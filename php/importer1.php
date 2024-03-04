<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
require '../../vendor/autoload.php';
include('../model/connection.php');

use PhpOffice\PhpSpreadsheet\IOFactory;

class ExcelImporter
{
    private $targetDir;
    private $dbConnection;

    public function __construct()
    {
        $this->targetDir = "../../assets/excel/";
        $this->dbConnection = new Connection();
    }

    public function importExcel()
    {
        $response = array(); // Associative array to store the response

        $targetFile = $this->targetDir . basename($_FILES["import_file"]["name"]);

        if (!$this->isValidFileType($targetFile)) {
            $response['success'] = false;
            $_SESSION['error_message'] = "Sorry, only Excel files are allowed.";
            return $response;
        }

        $this->createTargetDirectory();

        if (move_uploaded_file($_FILES["import_file"]["tmp_name"], $targetFile)) {
            $excelData = $this->readExcelDataWithHeadingRow($targetFile);
            // $response['success'] = true;
            // $response['data'] = $excelData;
            return $response;
        }

        $response['success'] = false;
        $_SESSION['error_message'] = "Sorry, there was an error uploading your file.";
        return $response;
    }

    public function importExcel1()
    {
        $response = array(); // Associative array to store the response

        $targetFile = $this->targetDir . basename($_FILES["import_file"]["name"]);

        if (!$this->isValidFileType($targetFile)) {
            $response['success'] = false;
            $_SESSION['error_message'] = "Sorry, only Excel files are allowed.";
            return $response;
        }

        $this->createTargetDirectory();

        if (move_uploaded_file($_FILES["import_file"]["tmp_name"], $targetFile)) {
            $excelData = $this->readExcelDataWithHeadingRow($targetFile);
            $response['success'] = true;
            $response['data'] = $excelData;
            return $response;
        }

        $response['success'] = false;
        $_SESSION['error_message'] = "Sorry, there was an error uploading your file.";
        return $response;
    }

    private function isValidFileType($targetFile)
    {
        $fileType = strtolower(pathinfo($targetFile, PATHINFO_EXTENSION));
        return $fileType == "xls" || $fileType == "xlsx";
    }
    private function createTargetDirectory()
    {
        if (!file_exists($this->targetDir)) {
            mkdir($this->targetDir, 0777, true);
        }
    }

    // Helper function to get existing columns in a table
    private function getExistingColumns($tableName)
    {
        $conn = $this->dbConnection->getConnection();
        $stmt = $conn->query("DESCRIBE $tableName");
        $columns = $stmt->fetchAll(PDO::FETCH_COLUMN);
        return $columns;
    }

    public function insertData($tableName, $excelData)
    {
        $conn = $this->dbConnection->getConnection();
        $columns = array_shift($excelData);
        $response = array(); // Associative array to store the response

        try {
            $existingColumns = $this->getExistingColumns($tableName);
            $nonExistingColumns = array_diff($columns, $existingColumns);
            $jsonData = json_encode(array_values($nonExistingColumns));

            if (!empty($nonExistingColumns)) {
                return ['success' => false, 'message' => "Columns do not exist in table: " . $jsonData];
            }

            $columnNames = implode(', ', $columns);
            $namedPlaceholders = implode(', ', array_map(function ($column) {
                return ":$column";
            }, $columns));

            $stmt = $conn->prepare("INSERT INTO $tableName ($columnNames) VALUES ($namedPlaceholders)");

            $isFirstRow = true;
            $columnNames = array();
            $excelDataAssoc = array();

            foreach ($excelData as $index => $rowData) {
                if ($isFirstRow) {
                    // If it's the first row, store the column names
                    $columnNames = $rowData;
                    $isFirstRow = false;
                } else {
                    // If it's not the first row, create an associative array
                    $rowAssoc = array_combine($columns, $rowData);
                    $excelDataAssoc[$index] = $rowAssoc;
                }
            }

            // Now, execute the insert statement for each row
            foreach ($excelDataAssoc as $row) {
                $row = array_map(function ($value) {
                    return $value !== '' ? $value : null;
                }, $row);

                try {
                    // Binding parameters before executing the statement
                    foreach ($columns as $column) {
                        $stmt->bindParam(":$column", $row[$column]);
                    }

                    $stmt->execute();
                } catch (PDOException $e) {
                    if ($e->getCode() == '23000' && strpos($e->getMessage(), 'Duplicate entry') !== false) {
                        continue;
                    } else {
                        throw $e;
                    }
                }

                if ($stmt->rowCount() == 0) {
                    return ['success' => false, 'message' => "Some entries failed to insert."];
                }
            }

            if (empty($response)) {
                $response['success'] = true;
                $response['message'] = "All entries inserted successfully.";
            } else {
                $response['success'] = false;
                $response['message'] = "Some entries failed to insert.";
            }
        } catch (PDOException $e) {
            $response['success'] = false;
            $response['message'] = "Error: " . $e->getMessage();
        }

        return $response;
    }

    public function updateData($tableName, $excelData)
    {
        $conn = $this->dbConnection->getConnection();
        $response = array();
        try {
            $existingColumns = $this->getExistingColumns($tableName);
            $nonExistingColumns = array_diff($excelData[0], $existingColumns);
            if (!empty($nonExistingColumns)) {
                return ['success' => false, 'message' => "Columns do not exist in table: " . json_encode(array_values($nonExistingColumns))];
            }
            $stmt = $conn->prepare("UPDATE $tableName SET student_status = 'complete' WHERE enrollment = :enrollment");
            for ($i = 1; $i < count($excelData); $i++) {
                $stmt->bindParam(':enrollment', $excelData[$i]['enrollment']);
                if (!$stmt->execute() || !$stmt->rowCount()) {
                    continue;
                    // return ['success' => false, 'message' => "Failed to update enrollment: {$excelData[$i][0]}"];
                }
            }
            $response['success'] = true;
            $response['message'] = "All entries updated successfully.";
        } catch (PDOException $e) {
            $response['success'] = false;
            $response['message'] = "Error: " . $e->getMessage();
        }
        return $response;
    }

    private function readExcelData($file)
    {
        $spreadsheet = IOFactory::load($file);
        $sheet = $spreadsheet->getActiveSheet();
        $highestRow = $sheet->getHighestRow();
        $highestColumn = $sheet->getHighestColumn();

        $excelData = array();

        for ($row = 1; $row <= $highestRow; $row++) {
            $rowData = $sheet->rangeToArray('A' . $row . ':' . $highestColumn . $row, NULL, TRUE, FALSE);
            $excelData[] = $rowData[0];
        }
        return $excelData;
    }

    // private function readExcelDataWithHeadingRow($file)
    // {
    //     $spreadsheet = IOFactory::load($file);
    //     $sheet = $spreadsheet->getActiveSheet();
    //     $highestRow = $sheet->getHighestRow();
    //     $highestColumn = $sheet->getHighestColumn();

    //     $excelData = array();

    //     // Get column headings
    //     $columnHeadings = $sheet->rangeToArray('A1:' . $highestColumn . '1', NULL, TRUE, FALSE)[0];

    //     // Start from the second row assuming it's data and not headings
    //     for ($row = 2; $row <= $highestRow; $row++) {
    //         // Read row data
    //         $rowData = $sheet->rangeToArray('A' . $row . ':' . $highestColumn . $row, NULL, TRUE, FALSE)[0];

    //         // Create associative array using column headings
    //         $rowAssoc = array();
    //         foreach ($columnHeadings as $index => $heading) {
    //             $rowAssoc[$heading] = isset($rowData[$index]) ? $rowData[$index] : null;
    //         }

    //         // Add the row data to the result
    //         $excelData[] = $rowAssoc;
    //     }
    //     return $excelData;
    // }

    private function readExcelDataWithHeadingRow($file)
    {
        $spreadsheet = IOFactory::load($file);
        $sheet = $spreadsheet->getActiveSheet();
        $highestRow = $sheet->getHighestRow();
        $highestColumn = $sheet->getHighestColumn();

        $excelData = array();
        $updateQueries = array();

        // Get column headings
        $columnHeadings = $sheet->rangeToArray('A1:' . $highestColumn . '1', NULL, TRUE, FALSE)[0];

        // Start from the second row assuming it's data and not headings
        for ($row = 2; $row <= $highestRow; $row++) {
            // Read row data
            $rowData = $sheet->rangeToArray('A' . $row . ':' . $highestColumn . $row, NULL, TRUE, FALSE)[0];

            // Create associative array using column headings
            $rowAssoc = array();
            foreach ($columnHeadings as $index => $heading) {
                $rowAssoc[$heading] = isset($rowData[$index]) ? $rowData[$index] : null;
            }

            // Add the row data to the result
            $excelData[] = $rowAssoc;

            // Generate SQL UPDATE query
            $updateQuery = "UPDATE students SET ";
            $updateValues = array();
            foreach ($rowAssoc as $key => $value) {
                // Assuming 'enrollment' is the unique identifier
                if ($key !== 'enrollment') {
                    $updateValues[] = "$key = :$key";
                }
            }
            $updateQuery .= implode(", ", $updateValues);
            $updateQuery .= " WHERE enrollment = :enrollment";

            // Add the query to the list of update queries
            $updateQueries[] = $updateQuery;
        }

        // return array($excelData, $updateQueries);
        return  $this->executeUpdateQueries($excelData, $updateQueries);
    }

    private function executeUpdateQueries($excelData, $updateQueries)
    {

        $conn = $this->dbConnection->getConnection();
        // Prepare and execute each query for each row
        foreach ($excelData as $index => $rowData) {
            $updateQuery = $updateQueries[$index];

            // Prepare the query
            $stmt = $conn->prepare($updateQuery);

            // Bind values
            foreach ($rowData as $key => $value) {
                if ($key !== 'enrollment') { // Skip the unique identifier
                    $stmt->bindValue(":$key", $value);
                }
            }
            // Bind the enrollment separately (assuming it's a unique identifier)
            $stmt->bindValue(":enrollment", $rowData['enrollment']);

            // Execute the query
            $stmt->execute();
        }
    }



}