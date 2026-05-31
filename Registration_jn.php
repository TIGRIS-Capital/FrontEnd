<?php
session_start(); 
require_once "Naval_FinalsActivity3_DB.php";
require_once "verifyotpemail_jn.php";

function ensure_email_column_jnsa(mysqli $conn) {
    static $email_column_checked_jnsa = false;

    if ($email_column_checked_jnsa) {
        return;
    }

    $email_column_result_jnsa = $conn->query("SHOW COLUMNS FROM loan_member_jnsa LIKE 'email_jnsa'");
    if ($email_column_result_jnsa && $email_column_result_jnsa->num_rows === 0) {
        $conn->query("ALTER TABLE loan_member_jnsa ADD COLUMN email_jnsa VARCHAR(255) NULL AFTER member_img_jnsa");
    }

    $user_type_column_result_jnsa = $conn->query("SHOW COLUMNS FROM loan_member_jnsa LIKE 'user_type_jnsa'");
    if ($user_type_column_result_jnsa && $user_type_column_result_jnsa->num_rows === 0) {
        $conn->query("ALTER TABLE loan_member_jnsa ADD COLUMN user_type_jnsa VARCHAR(50) NULL AFTER username_jnsa");
    }

    $email_column_checked_jnsa = true;
}

ensure_email_column_jnsa($conn);
?>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>TIGRIS Capital</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body style="background: linear-gradient(135deg, #f7f8fa 0%, #eef1f4 100%); min-height: 100vh; display: flex; align-items: center; justify-content: center; margin: 0; padding: 20px; font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif; color:#1f2937;">
    <div class="container-fluid" style="max-width: 1200px;">
        <div class="row align-items-center justify-content-center">

                <div class="col-md-5 text-center mb-5 mb-md-0 d-flex flex-column align-items-center justify-content-center">
                <div style="width:200px; height:200px; background-color: #121416; border-radius: 24px; display: flex; align-items: center; justify-content: center; box-shadow: 0 8px 16px rgba(18, 20, 22, 0.18); margin-bottom: 20px; overflow:hidden;">
                    <img src="Tigris_Logo_NoText.png" alt="Tigris Capital Logo" style="width: 100%; height: 100%; object-fit: cover; border-radius: 24px;">
                </div>
                <h1 class="fw-bold m-0" style="font-size: 3rem; color: #1f2937; letter-spacing: -1px;">
                    <span style="color: #a82329;">TIGRIS</span> Capital
                </h1>
                <p class="mt-2" style="font-size: 1.1rem; font-weight: 500; color:#6b7280; opacity: 0.95;">
                    Loan Management System
                </p>
                <p class="mt-3" style="max-width: 320px; color:#6b7280;">Create an account to access member features and manage loans securely.</p>
            </div>

            <div class="col-md-6 d-flex justify-content-center justify-content-md-start ps-md-5">
                <div class="bg-white rounded-4 p-5 w-100" style="max-width: 720px; box-shadow: 0 15px 35px rgba(18,20,22,0.08) !important; border:1px solid #e5e7eb;">
                    <form action="Registration_jn.php" method="post" enctype="multipart/form-data">
                        <div class="row mb-4">
                            <div class="col text-start">
                                <span class="fw-bold" style="font-size: 1.5rem; color: #1f2937;">Register New Account</span>
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <div style="display:flex; flex-direction:column; gap:10px;">
                                    <label class="form-label" style="color:#4b5563; margin:0;">First Name</label>
                                    <input type="text" name="fname_jnsa" class="form-control" required style="border-color:#d1d5db; border-radius:10px; height:40px;">
                                </div>
                            </div>

                            <div class="col-md-6 mb-3">
                                <div style="display:flex; flex-direction:column; gap:10px;">
                                    <label class="form-label" style="color:#4b5563; margin:0;">Last Name</label>
                                    <input type="text" name="lname_jnsa" class="form-control" required style="border-color:#d1d5db; border-radius:10px; height:40px;">
                                </div>
                            </div>

                            <div class="col-md-6 mb-3">
                                <div style="display:flex; flex-direction:column; gap:10px;">
                                    <label class="form-label" style="color:#4b5563; margin:0;">Middle Name</label>
                                    <input type="text" name="mname_jnsa" class="form-control" style="border-color:#d1d5db; border-radius:10px; height:40px;">
                                </div>
                            </div>

                            <div class="col-md-4 mb-3">
                                <div style="display:flex; flex-direction:column; gap:10px;">
                                    <label class="form-label" style="color:#4b5563; margin:0;">Suffix</label>
                                    <input type="text" name="suffix_jnsa" class="form-control" style="border-color:#d1d5db; border-radius:10px; height:40px;">
                                </div>
                            </div>

                            <div class="col-12 mb-3">
                                <div style="display:flex; flex-direction:column; gap:10px;">
                                    <label class="form-label" style="color:#4b5563; margin:0;">Contact Number</label>
                                    <input type="text" name="contact_jnsa" class="form-control" style="border-color:#d1d5db; border-radius:10px; height:40px;">
                                </div>
                            </div>

                            <div class="col-12 mb-3">
                                <div style="display:flex; flex-direction:column; gap:10px;">
                                    <label class="form-label" style="color:#4b5563; margin:0;">Address</label>
                                    <input type="text" name="address_jnsa" class="form-control" style="border-color:#d1d5db; border-radius:10px; height:40px;">
                                </div>
                            </div>

                            <div class="col-12 mb-3">
                                <div style="display:flex; flex-direction:column; gap:10px;">
                                    <label class="form-label" style="color:#4b5563; margin:0;">Email</label>
                                    <input type="email" name="email_jnsa" class="form-control" placeholder="example@email.com" required style="border-color:#d1d5db; border-radius:10px; height:40px;">
                                </div>
                            </div>

                            <div class="col-md-6 mb-3">
                                <div style="display:flex; flex-direction:column; gap:10px;">
                                    <label class="form-label" style="color:#4b5563; margin:0;">Username</label>
                                    <input type="text" name="username_jnsa" class="form-control" placeholder="Choose a username" required style="border-color:#d1d5db; border-radius:10px; height:40px;">
                                </div>
                            </div>

                            <div class="col-md-6 mb-3">
                                <div style="display:flex; flex-direction:column; gap:10px;">
                                    <label class="form-label" style="color:#4b5563; margin:0;">Password</label>
                                    <input type="password" name="password_jnsa" class="form-control" required style="border-color:#d1d5db; border-radius:10px; height:40px;">
                                </div>
                            </div>

                            <div class="col-12 mb-4">
                                <div style="display:flex; flex-direction:column; gap:10px;">
                                    <label class="form-label" style="color:#4b5563; margin:0;">Upload your profile picture</label>
                                    <div style="display:flex; align-items:flex-end; gap:12px; flex-wrap:wrap;">
                                        <div style="width:90px; height:90px; border:1px solid #d1d5db; border-radius:10px; background:#fff; display:flex; align-items:center; justify-content:center; overflow:hidden;">
                                            <img src="" alt="" id="preview_img" style="width:100%; height:100%; object-fit:cover;">
                                        </div>
                                        <input type="file" name="upload_img_jnsa" id="upload_img" class="form-control" onchange="previewImage(event)" style="max-width:240px; border-color:#d1d5db; border-radius:10px; height:40px; padding-top:7px;">
                                    </div>
                                </div>
                            </div>

                            <input type="hidden" name="user_type_jnsa" value="member">
                        </div>

                        <div class="d-flex justify-content-center align-items-center mt-2">
                            <input type="submit" name="btnsave_jnsa" value="Register" class="btn text-white py-2 px-4" style="background-color: #a82329; border-color: #a82329; border-radius: 8px; min-width:110px;">
                        </div>
                    </form>
                </div>
            </div>

        </div>
    </div>

<?php
if(isset($_POST['btnsave_jnsa'])) {
    $fullname_jnsa = $_POST['lname_jnsa'] . ", " . $_POST['fname_jnsa'] . " " . $_POST['mname_jnsa'] . " " . $_POST['suffix_jnsa'];
    $contact_jnsa = $_POST['contact_jnsa'];
    $address_jnsa = $_POST['address_jnsa'];
    $username_jnsa = $_POST['username_jnsa'];
    $email_jnsa = $_POST['email_jnsa']; 
    $password_jnsa = md5($_POST['password_jnsa']); 
    $user_type_jnsa = $_POST['user_type_jnsa'];
    
    // OTP and account status
    $otp_jnsa = rand(100000, 999999);
    $user_status_jnsa = 'Pending';

    $imagepath = '';
    if (isset($_FILES['upload_img_jnsa']) && $_FILES['upload_img_jnsa']['error'] === UPLOAD_ERR_OK && !empty($_FILES['upload_img_jnsa']['name'])) {
        $imagepath = "Images_jn" . $_FILES['upload_img_jnsa']['name'];
        copy($_FILES['upload_img_jnsa']['tmp_name'], $imagepath);
    }

   
    $insertsql = "INSERT INTO loan_member_jnsa (member_name_jnsa, contact_information_jnsa, address_jnsa, member_img_jnsa, email_jnsa, username_jnsa, password_jnsa, user_type_jnsa, otp_jnsa, user_status_jnsa) 
                  VALUES ('$fullname_jnsa', '$contact_jnsa', '$address_jnsa', '$imagepath', '$email_jnsa', '$username_jnsa', '$password_jnsa', '$user_type_jnsa', '$otp_jnsa', '$user_status_jnsa')";

    if(mysqli_query($conn, $insertsql)) {
        
        $_SESSION['verify_email'] = $email_jnsa;
        send_verification($fullname_jnsa, $email_jnsa, $otp_jnsa);
        
    } else {
        echo "Error encountered: " . mysqli_error($conn);
    }
}
?>

<script>
    function previewImage(event) {
        var displaying = document.getElementById('preview_img');
        displaying.src = URL.createObjectURL(event.target.files[0]);
    }
</script>
</body>
</html>