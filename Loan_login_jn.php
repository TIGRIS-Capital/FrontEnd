<?php
session_start();
//session_abort();
require_once "Naval_FinalsActivity3_DB.php";

$login_error = "";

if (isset($_POST['sub_jnsa'])) {
    // user input
    $username_jnsa = trim($_POST['username_jnsa']);
    $password_jnsa = md5($_POST['password_jnsa']); //encrypt password

    $loginsql = "SELECT * FROM loan_member_jnsa WHERE username_jnsa = ? LIMIT 1";
    $stmt_jnsa = $conn->prepare($loginsql);
    if ($stmt_jnsa) {
        $stmt_jnsa->bind_param("s", $username_jnsa);
        $stmt_jnsa->execute();
        $result = $stmt_jnsa->get_result();
    } else {
        $result = false;
    }

    //for logs

    if ($result && $result->num_rows == 1) {
    $fieldnames = $result->fetch_assoc();

    if ($fieldnames['password_jnsa'] !== $password_jnsa) {
        $login_error = "Invalid username or password";
    } else {

    // Account verify
    if ($fieldnames['user_status_jnsa'] !== 'Active') {
        $_SESSION['verify_email'] = $fieldnames['email_jnsa'] ?? $username_jnsa; // Save session context for otpverify.php
        $login_error = "Your account is pending verification. Please verify your email before logging in.";
    } else {
        // Account status
        $member_id_jnsa = $fieldnames['member_id_jnsa'] ?? $fieldnames['id'] ?? $fieldnames['member_id'] ?? 0;

        $log_stmt = $conn->prepare("INSERT INTO loan_logs_jnsa (member_id_jnsa, action_jnsa, datetime_jnsa) VALUES (?, ?, NOW())");
        if ($log_stmt) {
            $action_jnsa = 'Logged In';

            $mid = (int)$member_id_jnsa;
            $log_stmt->bind_param('is', $mid, $action_jnsa);
            $log_stmt->execute();
            $log_stmt->close();
        }
        $usertype_jnsa = $fieldnames['user_type_jnsa'];
        $normalized_user_type = strtolower(trim((string)$usertype_jnsa));
        
        $_SESSION['member_id_jnsa'] = $member_id_jnsa;
        $_SESSION['user_type'] = $usertype_jnsa;
        $_SESSION['username'] = $username_jnsa;
        $_SESSION['member_name_jnsa'] = $fieldnames['member_name_jnsa'] ?? $username_jnsa;

        if ($normalized_user_type == "admin") {
            header("Location: admindashboard_jn.php");
            exit();
        } else if ($normalized_user_type == "employee") {
            header("Location: employeedashboard_jn.php");
            exit();
        } else if ($normalized_user_type == "member") {
            header("Location: memberdashboard_jnsa.php");
            exit();
        } else {
            $login_error = "Account role is not configured for dashboard access.";
        }
    }
    }
    
    if (isset($stmt_jnsa)) {
        $stmt_jnsa->close();
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
    <title>TIGRIS Capital</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-T3c6CoIi6uLrA9TneNEoa7RxnatzjcDSCmG1MXxSR1GAsXEV/Dwwykc2MPK8M2HN" crossorigin="anonymous">
</head>
<body style="margin:0; min-height:100vh; display:flex; align-items:center; justify-content:center; padding:20px; background:linear-gradient(135deg, #f7f8fa 0%, #eef1f4 100%); font-family:'Segoe UI', Tahoma, Geneva, Verdana, sans-serif; color:#1f2937;">
    
    <div class="container-fluid" style="max-width:1200px;">
        <div class="row align-items-center justify-content-center">
            
        <!-- Logo and Title -->
            <div class="col-md-5 text-center mb-5 mb-md-0 d-flex flex-column align-items-center justify-content-center">
                <div style="width:200px; height:200px; background-color:#121416; border-radius:28px; display:flex; align-items:center; justify-content:center; box-shadow:0 10px 22px rgba(18,20,22,0.18); margin-bottom:24px; overflow:hidden;">
                    <img src="Tigris_Logo_NoText.png" alt="Tigris Capital Logo" style="width:100%; height:100%; object-fit:cover; border-radius:inherit;">
                </div>
                <h1 class="fw-bold m-0" style="font-size:3.8rem; color:#1f2937; letter-spacing:-1px;">
                    <span style="color:#a82329; font-weight:600;">TIGRIS</span> Capital
                </h1>
                <p class="mt-2" style="font-size:1.3rem; font-weight:500; color:#6b7280; opacity:0.95;">
                    Loan Management System
                </p>
            </div>

        <!-- Login form card -->
            <div class="col-md-5 d-flex justify-content-center justify-content-md-start ps-md-5">
                <div class="bg-white rounded-4 p-5 w-100" style="max-width:520px; box-shadow:0 18px 45px rgba(18,20,22,0.08); border:1px solid #e5e7eb;">
                    <form action="" method="post">
                    <div class="row mb-4">
                        <div class="col text-start">
                            <span class="fw-bold fs-2" style="color:#1f2937;">Log In</span>
                        </div>
                    </div>
                    
                    <div class="form-outline mb-3">
                        <label class="form-label fw-semibold small" style="color:#4b5563;" for="form2Example1">Username</label>
                        <input type="text" name="username_jnsa" id="form2Example1" class="form-control py-2" style="border-color:#d1d5db; border-radius:10px; font-size:1.05rem; padding:0.75rem 0.9rem;" />
                    </div>

                    <div class="form-outline mb-4">
                        <label class="form-label fw-semibold small" style="color:#4b5563;" for="form2Example2">Password</label>
                        <input type="password" name="password_jnsa" id="form2Example2" class="form-control py-2" style="border-color:#d1d5db; border-radius:10px; font-size:1.05rem; padding:0.75rem 0.9rem;" />
                    </div>
                    
                    <div class = "row mb-4 text-center">
                        <label class="col small fs-5" style="color:#6b7280;">Don't have an account? <a href="Registration_jn.php" class="text-decoration-none" style="color:#a82329; font-weight:600;">Register</a></label>
                        <!-- <a href="#" class="text-decoration-none text-secondary small">Forgot password?</a> -->
                    </div>

                    <input type="submit" name="sub_jnsa" value="Log In" class="btn text-white w-100 py-2 fw-semibold" style="background-color:#a82329; border-color:#a82329; border-radius:10px; font-size:1.05rem; padding:0.9rem 1rem;">
                    </form>
                </div>
            </div>

        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

    
    <?php if($login_error): ?>
    <script>
        Swal.fire({
            icon: 'error',
            title: 'Login Failed',
            text: <?php echo json_encode($login_error); ?>
        });
    </script>
    <?php endif; ?>
</body>
</html>