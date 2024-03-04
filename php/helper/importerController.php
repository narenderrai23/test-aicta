<?php

require_once('importer.php');
$excelImporter = new ExcelImporter();

if (isset($_POST['table'])) {
    $tableName = $_POST['table'];
    $data = $excelImporter->importExcel();
    if (isset($data['success'])) {
        $response = $excelImporter->insertData($tableName, $data['data']);
        if ($response['success']) {
            $_SESSION['success_message'] = $response['message'];
        } else {
            $_SESSION['error_message'] = $response['message'];
        }
    } else {
        $_SESSION['error_message'] = $response['message'];
    }
    header('Location: ../../admin/importer.php');
    exit(); // Make sure to exit after the redirect
}

if (isset($_POST['action']) && $_POST['action'] === 'completeStudent') {
    $tableName = 'students';
    $data = $excelImporter->importExcel();
    if (isset($data['success'])) {
        $response = $excelImporter->updateData($tableName, $data['data']);
        if ($response['success']) {
            $_SESSION['success_message'] = $response['message'];
        } else {
            $_SESSION['error_message'] = $response['message'];
        }
    } else {
        $_SESSION['error_message'] = $response['message'];
    }
    header('Location: ../../admin/students-completed.php');
    exit(); // Make sure to exit after the redirect
}