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

    private function isValidFileType($targetFile)
    {
        $fileType = strtolower(pathinfo($targetFile, PATHINFO_EXTENSION));
        return $fileType == "xls" || $fileType == "xlsx" || $fileType == "csv";
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

        if (!file_exists($this->targetDir)) {
            mkdir($this->targetDir, 0777, true);
        }

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

    private function getExistingColumns($tableName)
    {
        $conn = $this->dbConnection->getConnection();
        $stmt = $conn->query("DESCRIBE $tableName");
        $columns = $stmt->fetchAll(PDO::FETCH_COLUMN);
        return $columns;
    }

    // public function updateData($tableName, $excelData)
    // {
    //     $conn = $this->dbConnection->getConnection();
    //     $response = array();

    //     $columns = array_shift($excelData);
    //     $existingColumns = $this->getExistingColumns($tableName);
    //     $nonExistingColumns = array_diff($columns, $existingColumns);
    //     $jsonData = json_encode(array_values($nonExistingColumns));

    //     if (!empty($nonExistingColumns)) {
    //         return ['success' => false, 'message' => "Columns do not exist in table: " . $jsonData];
    //     }
    //     try {
    //         $updateQuery = "UPDATE students SET ";
    //         $updateValues = array();



    //         // Add the query to the list of update queries

    //         // $stmt = $conn->prepare("UPDATE $tableName SET student_status = :student_status WHERE enrollment = :enrollment");
    //         foreach ($excelData as $key => $row) {
    //             if ($key !== 'enrollment') {
    //                 $updateValues[] = "$key = :$key";
    //             }
    //         }
    //         $updateQuery .= implode(", ", $updateValues);
    //         $updateQuery .= " WHERE enrollment = :enrollment";

    //         $updateQueries[] = $updateQuery;

    //         $response['success'] = true;
    //         $response['message'] = "All entries updated successfully.";
    //     } catch (PDOException $e) {
    //         $response['success'] = false;
    //         $response['message'] = "Error: " . $e->getMessage();
    //     }
    //     return $response;
    // }


    public function updateData($tableName, $excelData)
    {
        $conn = $this->dbConnection->getConnection();
        $response = array();

        $columns = array_keys($excelData[0]); // Get column names from the first row
        $existingColumns = $this->getExistingColumns($tableName);
        $nonExistingColumns = array_diff($columns, $existingColumns);
        $jsonData = json_encode(array_values($nonExistingColumns));

        if (!empty($nonExistingColumns)) {
            return ['success' => false, 'message' => "Columns do not exist in table: " . $jsonData];
        }

        try {
            $updateQueries = array(); // Initialize array to store update queries

            foreach ($excelData as $row) {
                $updateQuery = "UPDATE $tableName SET ";
                $updateValues = array();

                foreach ($row as $key => $value) {
                    if ($key !== 'enrollment') {
                        $updateValues[] = "$key = :$key";
                    }
                }
                $updateQuery .= implode(", ", $updateValues);
                $updateQuery .= " WHERE enrollment = :enrollment";

                $stmt = $conn->prepare($updateQuery);
                $stmt->execute(array_merge($row, ['enrollment' => $row['enrollment']]));
            }

            $response['success'] = true;
            $response['message'] = "All entries updated successfully. Affected Row: - " . $stmt->rowCount();
        } catch (PDOException $e) {
            $response['success'] = false;
            $response['message'] = "Error: " . $e->getMessage();
        }
        return $response;
    }



    private function readExcelDataWithHeadingRow($file)
    {
        $spreadsheet = IOFactory::load($file);
        $sheet = $spreadsheet->getActiveSheet();
        $highestRow = $sheet->getHighestRow();
        $highestColumn = $sheet->getHighestColumn();

        $excelData = array();

        // Get column headings
        $columnHeadings = $sheet->rangeToArray('A1:' . $highestColumn . '1', NULL, TRUE, FALSE)[0];

        // Start from the second row assuming it's data and not headings
        for ($row = 1; $row <= $highestRow; $row++) {
            // Read row data
            $rowData = $sheet->rangeToArray('A' . $row . ':' . $highestColumn . $row, NULL, TRUE, FALSE)[0];

            // Create associative array using column headings
            $rowAssoc = array();
            foreach ($columnHeadings as $index => $heading) {
                $rowAssoc[$heading] = isset($rowData[$index]) ? $rowData[$index] : null;
            }

            // Add the row data to the result
            $excelData[] = $rowAssoc;
        }
        return $excelData;
    }

}