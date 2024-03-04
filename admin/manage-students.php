<?php include 'layouts/session.php'; ?>
<!doctype html>
<html lang="en">

<head>

    <title><?= $_SESSION['site_name'] ?> - Admin</title>
    
    <?php include 'layouts/head.php'; ?>
    <?php include 'layouts/head-style.php'; ?>
</head>

<body data-layout="vertical" data-sidebar="dark" data-sidebar-size="lg">

    <!-- Begin page -->
    <div id="layout-wrapper">

        <?php include 'layouts/menu.php'; ?>

        <!-- ============================================================== -->
        <!-- Start right Content here -->
        <!-- ============================================================== -->
        <div class="main-content">
            <div class="page-content">
                <div class="container-fluid">

                    <!-- start page title -->
                    <?php
                    $maintitle = $_SESSION['site_name'];
                    $title = 'Dashboard';
                    ?>
                    <!-- end page title -->

                    <div class="row">
                        <div class="col-lg-12">
                            <div class="card">
                                <div class="card-header d-flex justify-content-between align-items-center">
                                    <h4 class="card-title mb-0">Manage Student</h4>
                                    <form id="excelForm" action="../php/helper/excel-export.php" method="post">
                                        <input type="number" name="limit" class="btn btn-sm border-info"
                                            placeholder="Select All Entries">
                                        <button type="submit" name="action" value="excelExport"
                                            class="btn btn-sm btn-info">Excel</button>
                                    </form>
                                </div>
                                <div class="card-body">
                                    <table class="table table-striped table-bordered" id="datatable">
                                        <thead>
                                            <tr>
                                                <th>S.No</th>
                                                <th>Enrollment No</th>
                                                <th>Student Name</th>
                                                <th>Father's Name</th>
                                                <th>Course</th>
                                                <th>Branch Name</th>
                                                <th>Admission Date</th>
                                                <th>Approve</th>
                                                <th>Action</th>
                                                <th>Status</th>
                                            </tr>
                                        </thead>
                                    </table>
                                </div>
                                <!-- end card body -->
                            </div>
                            <!-- end card -->
                        </div>
                        <!-- end col -->
                    </div>
                    <!-- end row -->

                </div> <!-- container-fluid -->
            </div>
            <!-- End Page-content -->

            <?php include 'layouts/footer.php'; ?>
        </div>
        <!-- end main content-->

    </div>
    <!-- END layout-wrapper -->


    <!-- Right Sidebar -->
    <a id="right-bar-toggle"></a>

    <div class="modal fade" id="view" aria-hidden="true" aria-labelledby="exampleModalToggleLabel" tabindex="-1">
        <div class="modal-dialog modal-lg modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-body" id="imageContainer">
                </div>
            </div>
        </div>
    </div>

    <?php include 'layouts/vendor-scripts.php'; ?>

    <!-- gridjs js -->
    <script src="../assets/js/app.js"></script>

    <script src="https://cdn.datatables.net/1.13.7/js/jquery.dataTables.min.js"></script>
    <script src="https://cdn.datatables.net/1.13.6/js/dataTables.bootstrap5.min.js"></script>
    <script src="https://cdn.datatables.net/buttons/2.4.2/js/dataTables.buttons.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/jszip/3.10.1/jszip.min.js"></script>
    <script src="https://cdn.datatables.net/buttons/2.4.2/js/buttons.html5.min.js"></script>
    <script src="https://cdn.datatables.net/buttons/2.4.2/js/buttons.print.min.js"></script>
    <script src="https://cdn.datatables.net/buttons/2.4.2/js/buttons.colVis.min.js"></script>

    <!-- DataTables Buttons -->
    <script src="https://cdnjs.cloudflare.com/ajax/libs/pdfmake/0.1.53/pdfmake.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/pdfmake/0.1.53/vfs_fonts.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>
    <script src="../ajax/js/fetch.js"></script>
    <script src="../ajax/js/extra.js"></script>
    <script src="../ajax/datatable-student.js"></script>
    <script>
        const action = function (d) {
            d.action = 'fetchStudents';
        };
        // Call the function to initialize DataTable
        var table = initializeDataTable('#datatable', action);
        const deleteText = "You are about to delete the student."
        const successText = "Student Deleted! The student has been successfully deleted."
        deleteItem(table, "deleteStudent", deleteText, successText);
        function edit(enrollment) {
            $('#view').modal('show');
            const data = {
                enrollment: enrollment,
                card: 'cartificate',
                action: 'cardStudent',
                image: 'manage',
            };
            const url = "../php/controller/studentController.php";
            const success = function (response) {
                // console.log(response)
                if (response.status) {
                    $('#imageContainer').html('<img src="data:image/jpeg;base64,' + response.image + '" />');
                    const width = $('input[name="card"]:checked').val() === 'card' ? '300px' : '100%';
                    $('#imageContainer img').css('width', width);
                    var downloadIcon = $('<i>').addClass('mdi mdi-download d-block font-size-16');
                    var downloadLink = $('<a>', {
                        href: 'data:image/jpeg;base64,' + response.image,
                        download: 'student_image.jpg',
                    }).addClass('btn btn-sm btn-success float-end mt-2');
                    downloadLink.append(downloadIcon);
                    $('#imageContainer').append(downloadLink);
                } else {
                    $('#imageContainer').html('<div class="text-danger text-center">' + response.message + '</div>');
                }
            };
            performAjaxRequest(url, data, success);
        }
    </script>
</body>

</html>