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

        <!-- ============================================================== -->
        <!-- Start right Content here -->
        <!-- ============================================================== -->
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
                        <div class="col">
                            <div class="card">
                                <div class="card-body">
                                    <form id="filterData">
                                        <div class="row">
                                            <div class="col">
                                                <div class="fw-bold mb-3">Enrollment</div>
                                                <input type="text" name="enrollment" class="form-control mb-3" value="CPR20231030160654">
                                            </div>
                                            <div class="col">
                                                <div class="fw-bold mb-3">File</div>
                                                <div class="my-2">
                                                    <label class="form-check form-check-inline"><input type="radio" name="card" value="card" checked> ID Card</label>
                                                    <label class="form-check form-check-inline"><input type="radio" name="card" value="cartificate"> Cartificate</label>
                                                </div>
                                            </div>
                                        </div>
                                        <button type="submit" class="btn btn-primary mr-2">Submit</button>
                                    </form>
                                </div>
                            </div>

                            <div class="card">
                                <div class="card-body">
                                    <div class="row">
                                        <div class="col">
                                            <div id="imageContainer"></div>
                                        </div>
                                    </div>
                                </div>
                            </div>

                        </div>
                    </div>

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

    <script src="../assets/js/app.js"></script>
    <script src="../ajax/js/fetch.js"></script>
    <script>
        $("#filterData").on("submit", function(e) {
            e.preventDefault();
            const formData = new FormData(this);
            formData.append("action", "cardStudent");
            ajax("../php/controller/studentController.php", formData, function(response) {
                console.log(response);
                if (response.status) {
                    $('#imageContainer').html('<img src="data:image/jpeg;base64,' + response.image + '" />');
                    const width = $('input[name="card"]:checked').val() === 'card' ? '300px' : '70%';
                    $('#imageContainer img').css('width', width);

                    var downloadIcon = $('<i>').addClass('mdi mdi-download d-block font-size-16');
                    var downloadLink = $('<a>', {
                        href: 'data:image/jpeg;base64,' + response.image,
                        download: 'student_image.jpg',
                    }).addClass('btn btn-sm btn-success ms-2 position-absolute');
                    downloadLink.append(downloadIcon);
                    $('#imageContainer').append(downloadLink);
                } else {
                    $('#imageContainer').html('<div class="text-danger text-center">' + response.message + '</div>');
                }
            });
        });
    </script>

    <style>
        #imageContainer {
            align-items: center;
        }

        #imageContainer img {
            /* width: 300px; */
            align-items: center;
            border: 2px solid;
        }
    </style>

</body>

</html>