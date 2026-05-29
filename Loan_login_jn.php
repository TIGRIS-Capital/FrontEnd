<?php
session_start();
//session_abort();
require_once "Naval_FinalsActivity3_DB.php";

$login_error = "";

if (isset($_POST['sub_jnsa'])) {
    // user input
    $username_jnsa = $_POST['username_jnsa'];
    $password_jnsa = md5($_POST['password_jnsa']); //encrypt password

    $loginsql = "SELECT * FROM loan_member_jn WHERE username_jnsa='$username_jnsa' AND password_jnsa='$password_jnsa'";
    $result = $conn->query($loginsql);

    //for logs

    if ($result && $result->num_rows == 1) {
    $fieldnames = $result->fetch_assoc();

    // Account verify
    if ($fieldnames['user_status_jnsa'] !== 'Active') {
        $_SESSION['verify_email'] = $username_jnsa; // Save session context for otpverify.php
    } else {
        // Account status
        $member_id_jnsa = $fieldnames['member_id_jnsa'];
        $logsql = "INSERT INTO loan_logs_jn (member_id_jnsa, action_jnsa, datetime_jnsa) VALUES ('$member_id_jnsa', 'Logged In', NOW())";
        $conn->query($logsql);
        $usertype_jnsa = $fieldnames['user_type_jnsa'];
        
        $_SESSION['user_type'] = $usertype_jnsa;
        $_SESSION['username'] = $username_jnsa;

        if ($usertype == "admin") {
            header("Location: admindashboard_jn.php");
            exit();
        } else if ($usertype == "employee") {
            header("Location: employeedashboard_jn.php");
            exit();
        }
    }
    
    } else {
    
    $login_error = "Invalid username or password";
    }
    
}
?>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Document</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-T3c6CoIi6uLrA9TneNEoa7RxnatzjcDSCmG1MXxSR1GAsXEV/Dwwykc2MPK8M2HN" crossorigin="anonymous">
    <style>
        .page-bg { background: linear-gradient(135deg, #fce5e6 0%, #ffeff0 100%); font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif; }
        .container-max { max-width: 1200px; }
        .logo-box { width: 140px; height: 140px; background-color: #de3b4a; border-radius: 28px; display: flex; align-items: center; justify-content: center; box-shadow: 0 10px 22px rgba(222, 59, 74, 0.18); margin-bottom: 24px; }
        .brand-title { font-size: 3.8rem; color: #333333; letter-spacing: -1px; }
        .lead-custom { font-size: 1.3rem; font-weight: 500; opacity: 0.85; }
        .login-card { max-width: 520px; padding: 3.5rem !important; box-shadow: 0 18px 45px rgba(0,0,0,0.06) !important; }
        .input-custom { border-color: #dee2e6; border-radius: 10px; font-size: 1.05rem; padding: 0.75rem 0.9rem; }
        .btn-de { background-color: #de3b4a; border-color: #de3b4a; border-radius: 10px; font-size: 1.05rem; padding: 0.9rem 1rem; }
        .text-de { color: #de3b4a; font-weight: 600; }
    </style>
</head>
<body class="page-bg min-vh-100 d-flex align-items-center justify-content-center p-3">
    
    <div class="container-fluid container-max">
        <div class="row align-items-center justify-content-center">
            
        <!-- Logo and Title Column -->
            <div class="col-md-5 text-center mb-5 mb-md-0 d-flex flex-column align-items-center justify-content-center">
                <div class="logo-box">
                    <span class="text-white" style="font-size: 3.5rem; font-weight: bold;">TC</span>
                </div>
                <h1 class="fw-bold m-0 brand-title">
                    <span class="text-de">TIGRIS</span> Capital
                </h1>
                <p class="text-secondary mt-2 lead-custom">
                    Loan Management System
                </p>
            </div>

        <!-- Form/Login Column     -->
            <div class="col-md-5 d-flex justify-content-center justify-content-md-start ps-md-5">
                <div class="bg-white border-0 rounded-4 p-5 w-100 login-card">
                    <form action="" method="post">
                    <div class="row mb-4">
                        <div class="col text-start">
                            <span class="fw-bold fs-2" style="color: #333333;">Log In</span>
                        </div>
                    </div>
                    
                    <div class="form-outline mb-3">
                        <label class="form-label fw-semibold text-secondary small" for="form2Example1">Username</label>
                        <input type="text" name="username_jnsa" id="form2Example1" class="form-control py-2 input-custom" />
                    </div>

                    <div class="form-outline mb-4">
                        <label class="form-label fw-semibold text-secondary small" for="form2Example2">Password</label>
                        <input type="password" name="password_jnsa" id="form2Example2" class="form-control py-2 input-custom" />
                    </div>
                    
                    <div class = "row mb-4 text-center">
                        <label class="col text-secondary small fs-5">Don't have an account? <a href="Registration_jn.php" class="text-decoration-none text-de">Register</a></label>
                        <!-- <a href="#" class="text-decoration-none text-secondary small">Forgot password?</a> -->
                    </div>

                    <input type="submit" name="sub_jnsa" value="Log In" class="btn text-white w-100 py-2 fw-semibold btn-de">
                    </form>
                </div>
            </div>

        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></scrip   t>
    <?php if($login_error): ?>
    <script>
        Swal.fire({
            icon: 'error',
            title: 'Login Failed',
            text: '<?php echo $login_error; ?>'
        });
    </script>
    <?php endif; ?>
</body>
</html>