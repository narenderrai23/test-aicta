<?php
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}
require_once('../model/students.php');

if (isset($_POST['action'])) {
    $Student = new Student();

    if ($_POST['action'] === 'addStudent') {
        $data = $Student->insertStudent();
        echo json_encode($data);
    }

    if ($_POST['action'] === 'updateStudent') {
        $data = $Student->updateStudent();
        echo json_encode($data);
    }

     if ($_POST['action'] === 'detailStudent') {
        $enrollment = $_POST['enrollment'];
        $data = $Student->fetchStudentByEnrollment($enrollment);
        echo json_encode($data);
    }

    if ($_POST['action'] === 'statusUpdate') {
        $id = $_POST['itemId'];
        $data = $Student->statusUpdate($id, 'students');
        echo json_encode($data);
    }

    if ($_POST['action'] === 'approveStatusUpdate') {
        $id = $_POST['itemId'];
        $data = $Student->approveStatusUpdate($id, 'students', true);
        echo json_encode($data);
    }

    if ($_POST['action'] === 'updateStudentStatus') {
        $id = $_POST['id'];
        $status =  $_POST['status'];
        $data = $Student->updateStudentStatus($id, $status, 'students');
        echo json_encode($data);
    }

   if ($_POST['action'] === 'cardStudent') {
        $enrollment = $_POST['enrollment'];
        if (isset($_POST['card']) && $_POST['card'] == 'card') {
            $response = $Student->writeTextToImage($enrollment);
        } else {
            $response = $Student->writeTextToImageCertificate($enrollment);
        }
        echo json_encode($response);
    }


   
}
