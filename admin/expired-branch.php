<?php include 'layouts/session.php'; ?>
<!doctype html>
<html lang="en">

<head>
    <title><?= $_SESSION['site_name'] ?> - Admin</title>
    <?php include 'layouts/head.php'; ?>
    <?php include 'layouts/head-style.php'; ?>
</head>


<body data-layout="vertical" data-sidebar="dark">

    <!-- Begin page -->
    <div id="layout-wrapper">

        <?php include 'layouts/menu.php'; ?>

        <div class="main-content">
            <div class="page-content">
                <div class="container-fluid">

                    <!-- start page title -->
                    <?php
                    $maintitle =  $_SESSION['site_name'];
                    $title = 'Dashboard';
                    ?>
                    <?php include 'layouts/breadcrumb.php'; ?>
                    <!-- end page title -->

                    <div class="row">
                        <div class="col-lg-12">
                            <div class="card">
                                <div class="card-header">
                                    <h4 class="card-title mb-0">Manage Branch</h4>
                                </div><!-- end card header -->
                                <div class="card-body">
                                    <table class="table table-striped table-bordered" id="datatable">
                                        <thead>
                                            <tr>
                                                <th>ID</th>
                                                <th>Branch Name</th>
                                                <th>Branch Email</th>
                                                <th>Branch Code</th>
                                                <th>Branch Head</th>
                                                <th>Phone Number</th>
                                                <th>Created</th>
                                                <th>Valid</th>
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

    <?php include 'layouts/vendor-scripts.php'; ?>

    <!-- gridjs js -->
    <script src="../assets/js/app.js"></script>

    <script src="https://cdn.datatables.net/1.13.7/js/jquery.dataTables.min.js"></script>
    <script src="https://cdn.datatables.net/1.13.6/js/dataTables.bootstrap5.min.js"></script>
    <script src="https://cdn.datatables.net/buttons/2.4.2/js/dataTables.buttons.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/jszip/3.10.1/jszip.min.js"></script>
    <script src="https://cdn.datatables.net/buttons/2.4.2/js/buttons.html5.min.js"></script>
    <script src="https://cdn.datatables.net/buttons/2.4.2/js/buttons.print.min.js"></script>

    <script src="../ajax/js/fetch.js"></script>
    <script src="../ajax/js/extra.js"></script>
    <script src="../ajax/datatable-branch.js"></script>

    <script>
        const action = function(d) {
            d.action = 'fetchBranches';
            d.condition = 'expiredBranch';
        };
        var table = initializeDataTable('#datatable', action);
        deleteItem(table, "branch");
    </script>
</body>
</html>