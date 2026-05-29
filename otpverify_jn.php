<?php

session_start();
require_once "Naval_FinalsActivity3_DB.php";

 $verification_success = false;
 $verification_error = '';

if (!isset($_SESSION['verify_email'])) {
    header("Location: Registration_jn.php");
    exit();
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['ver_jnsa'], $_POST['otp_jnsa']) && $_POST['otp_jnsa'] !== '') {
    $user_otp = mysqli_real_escape_string($conn, $_POST['otp_jnsa']);
    $email = $_SESSION['verify_email'];

    $check_query = "SELECT * FROM loan_member_jn WHERE username_jnsa = '$email' AND otp_jnsa = '$user_otp'";
    $result = mysqli_query($conn, $check_query);

    if ($result && mysqli_num_rows($result) > 0) {
        $update_query = "UPDATE loan_member_jn SET user_status_jnsa = 'Active', otp_jnsa = NULL WHERE username_jnsa = '$email'";

        if (mysqli_query($conn, $update_query)) {
            unset($_SESSION['verify_email']);
            $verification_success = true;
        } else {
            $verification_error = 'Unable to verify your account right now. Please try again.';
        }
    } else {
        $verification_error = 'Invalid OTP. Please check the code and try again.';
    }
}
?>

<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>OTP Verification</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body>
    <div class="container mt-5 w-25 border border-primary rounded p-5">
        <div class="row mb-5">
            <div class="col text-center fw-bold">
                <span class="display-4 text-primary">OTP Verification</span>
            </div>
        </div>
        <div class="row my-3">
            <div class="col text-center fw-bold">
                <span class="text-primary h6">One time password (OTP) was sent to your email</span>
            </div>
        </div>

        <form action="otpverify_jn.php" method="post">
            <div class="form-outline mb-4">
                <label class="form-label" for="form2Example1">Enter the OTP Number to verify</label>
                <input type="text" name="otp_jnsa" id="form2Example1" class="form-control" required />
            </div>
            <input type="submit" name="ver_jnsa" value="Verify" class="btn btn-primary btn-block w-100 mb-4">
        </form>
    </div>    

    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <?php if ($verification_success): ?>
    <script>
        Swal.fire({
            icon: 'success',
            title: 'Verified',
            text: 'Your OTP was verified successfully.',
            confirmButtonColor: '#0d6efd'
        }).then(() => {
            window.location.href = 'Loan_login_jn.php';
        });
    </script>
    <?php elseif ($verification_error): ?>
    <script>
        Swal.fire({
            icon: 'error',
            title: 'Verification Failed',
            text: '<?php echo addslashes($verification_error); ?>',
            confirmButtonColor: '#0d6efd'
        });
    </script>
    <?php endif; ?>
</body>
</html>