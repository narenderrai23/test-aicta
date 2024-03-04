<?php
session_start();
// error_reporting(0);
if (isset($_SESSION['role']) && $_SESSION['role'] === 'branch') {
    header("location: index.php");
    exit;
}

if (!isset($_SESSION['otp_alert'])) {
    header('location:auth-recoverpw.php');
    exit;
}

?>

<!DOCTYPE html>
<html lang="en">

<head>

    <title><?=$_SESSION['site_name']?> - Admin</title>

    <?php include 'layouts/head.php'; ?>

    <?php include 'layouts/head-style.php'; ?>

</head>


<body data-layout="vertical" data-sidebar="dark">

    <div class="authentication-bg min-vh-100">
        <div class="bg-overlay"></div>
        <div class="container">
            <div class="d-flex flex-column min-vh-100 px-3 pt-4">
                <div class="row justify-content-center my-auto">
                    <div class="col-md-8 col-lg-6 col-xl-5">

                        <div class="text-center mb-4">
                            <a href="index.php">
                                <img src="assets/images/logo-sm.svg" alt="" height="22"> <span class="logo-txt">Symox</span>
                            </a>
                        </div>

                        <div class="card">
                            <div class="card-body p-4">
                                <div class="avatar-lg mx-auto">
                                    <div class="avatar-title rounded-circle bg-light">
                                        <i class="bx bxs-envelope h2 mb-0 text-primary"></i>
                                    </div>
                                </div>
                                <div class="p-2 mt-4">
                                    <div class="text-center">
                                        <h4>Verify your email</h4>
                                        <p class="mb-5">Please enter the 4 digit code sent to <span class="fw-bold text-success">
                                                <?php
                                                echo ($_SESSION['email']) ?? '';
                                                ?>
                                            </span></p>
                                    </div>

                                    <form action="../php/controller/resetPasswordController.php" method="POST">
                                        <div class="row">
                                            <div class="col-3">
                                                <div class="mb-3">
                                                    <label for="digit1-input" class="visually-hidden">Dight 1</label>
                                                    <input type="text" class="form-control form-control-lg text-center two-step" maxLength="1" data-value="1" name="digit1" id="digit1-input">
                                                </div>
                                            </div>

                                            <div class="col-3">
                                                <div class="mb-3">
                                                    <label for="digit2-input" class="visually-hidden">Dight 2</label>
                                                    <input type="text" class="form-control form-control-lg text-center two-step" maxLength="1" data-value="2" name="digit2" id="digit2-input">
                                                </div>
                                            </div>

                                            <div class="col-3">
                                                <div class="mb-3">
                                                    <label for="digit3-input" class="visually-hidden">Dight 3</label>
                                                    <input type="text" class="form-control form-control-lg text-center two-step" maxLength="1" data-value="3" name="digit3" id="digit3-input">
                                                </div>
                                            </div>

                                            <div class="col-3">
                                                <div class="mb-3">
                                                    <label for="digit4-input" class="visually-hidden">Dight 4</label>
                                                    <input type="text" class="form-control form-control-lg text-center two-step" maxLength="1" data-value="4" name="digit4" id="digit4-input">
                                                </div>
                                            </div>
                                        </div>
                                        <span class="text-danger">
                                            <?php
                                            if (isset($_SESSION['otp_alert'])) {
                                                echo $_SESSION['otp_alert'];
                                                unset($_SESSION['otp_alert']); // Clear the error message
                                            }
                                            ?>
                                        </span>
                                        <div class="mt-3">
                                            <button class="btn btn-primary w-100 waves-effect waves-light" name="otp_password" type="submit">Confirm</button>
                                        </div>
                                    </form>

                                    <div class="mt-5 text-center">
                                        <p class="text-muted mb-0">Didn't receive an email ? <a href="#" class="text-primary fw-semibold"> Resend </a> </p>
                                    </div>

                                </div>
                            </div>

                        </div><!-- end col -->
                    </div><!-- end row -->

                    <div class="row">
                        <div class="col-lg-12">
                            <div class="text-center text-muted p-4">
                                <p class="text-white-50">© <script>
                                        document.write(new Date().getFullYear())
                                    </script> Symox. Crafted with <i class="mdi mdi-heart text-danger"></i> by Themesbrand</p>
                            </div>
                        </div>
                    </div>

                </div>
            </div><!-- end container -->
        </div>
        <!-- end authentication section -->

        <?php include 'layouts/vendor-scripts.php'; ?>

        <!-- two-step-verification js -->
        <script src="../assets/js/pages/two-step-verification.init.js"></script>

</body>

</html>