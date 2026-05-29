<?php
session_start(); 
require_once "Naval_FinalsActivity3_DB.php";
require_once "verifyotpemail_jn.php";
?>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Account Entry</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body style="background: linear-gradient(135deg, #fce5e6 0%, #ffeff0 100%); min-height: 100vh; display: flex; align-items: center; justify-content: center; margin: 0; padding: 20px; font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;">
    <div class="container-fluid" style="max-width: 1200px;">
        <div class="row align-items-center justify-content-center">

            <div class="col-md-5 text-center mb-5 mb-md-0 d-flex flex-column align-items-center justify-content-center">
                <div style="width: 100px; height: 100px; background-color: #de3b4a; border-radius: 24px; display: flex; align-items: center; justify-content: center; box-shadow: 0 8px 16px rgba(222, 59, 74, 0.2); margin-bottom: 20px;">
                    <span style="color: white; font-size: 2.5rem; font-weight: bold;">TC</span>
                </div>
                <h1 class="fw-bold m-0" style="font-size: 3rem; color: #333333; letter-spacing: -1px;">
                    <span style="color: #de3b4a;">TIGRIS</span> Capital
                </h1>
                <p class="text-secondary mt-2" style="font-size: 1.1rem; font-weight: 500; opacity: 0.8;">
                    Loan Management System
                </p>
                <p class="text-secondary mt-3" style="max-width: 320px;">Create an account to access member features and manage loans securely.</p>
            </div>

            <div class="col-md-6 d-flex justify-content-center justify-content-md-start ps-md-5">
                <div class="bg-white border-0 rounded-4 p-5 w-100" style="max-width: 720px; box-shadow: 0 15px 35px rgba(0,0,0,0.05) !important;">
                    <form action="Registration_jn.php" method="post" enctype="multipart/form-data">
                        <div class="row mb-4">
                            <div class="col text-start">
                                <span class="fw-bold" style="font-size: 1.5rem; color: #333333;">Register New Account</span>
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-md-4 text-center mb-3">
                                <img src="" alt="" id="preview_img" width="180" height="180" class="img-thumbnail">
                                <input type="file" name="upload_img_jnsa" id="upload_img" class="form-control mt-3" onchange="previewImage(event)">
                            </div>

                            <div class="col-md-8">
                                <div class="row">
                                    <div class="col-md-6 mb-3">
                                        <label class="form-label">First Name</label>
                                        <input type="text" name="fname_jnsa" class="form-control" required>
                                    </div>
                                    <div class="col-md-6 mb-3">
                                        <label class="form-label">Middle Name</label>
                                        <input type="text" name="mname_jnsa" class="form-control">
                                    </div>
                                    <div class="col-md-6 mb-3">
                                        <label class="form-label">Last Name</label>
                                        <input type="text" name="lname_jnsa" class="form-control" required>
                                    </div>
                                    <div class="col-md-6 mb-3">
                                        <label class="form-label">Suffix</label>
                                        <input type="text" name="suffix_jnsa" class="form-control">
                                    </div>
                                    <div class="col-md-6 mb-3">
                                        <label class="form-label">Contact Number</label>
                                        <input type="text" name="contact_jnsa" class="form-control">
                                    </div>
                                    <div class="col-md-6 mb-3">
                                        <label class="form-label">Address</label>
                                        <input type="text" name="address_jnsa" class="form-control">
                                    </div>
                                    <div class="col-md-6 mb-3">
                                        <label class="form-label">Username (Email Address)</label>
                                        <input type="email" name="username_jnsa" class="form-control" placeholder="example@email.com" required>
                                    </div>
                                    <div class="col-md-6 mb-3">
                                        <label class="form-label">Password</label>
                                        <input type="password" name="password_jnsa" class="form-control" required>
                                    </div>
                                    <div class="col-md-12 mb-3">
                                        <label class="form-label">User Type</label>
                                        <select name="user_type_jnsa" class="form-control" required>
                                            <option value="">Select Type</option>
                                            <option value="admin">Admin</option>
                                            <option value="employee">Employee</option>
                                            <option value="member">Member</option>
                                        </select>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="d-flex justify-content-between align-items-center mt-4">
                            <div>
                                <a href="Loan_login_jn.php" class="text-decoration-none" style="color: #de3b4a;">Back to Login</a>
                            </div>
                            <div>
                                <input type="submit" name="btnsave_jnsa" value="Register" class="btn text-white py-2 px-4" style="background-color: #de3b4a; border-color: #de3b4a; border-radius: 8px;">
                            </div>
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
    $password_jnsa = md5($_POST['password_jnsa']); 
    $user_type_jnsa = $_POST['user_type_jnsa'];
    
    // OTP and account status
    $otp_jnsa = rand(100000, 999999);
    $user_status_jnsa = 'Pending';

    $imagepath = "Images_jn" . $_FILES['upload_img_jnsa']['name'];
    copy($_FILES['upload_img_jnsa']['tmp_name'], $imagepath);

   
    $insertsql = "INSERT INTO loan_member_jn (member_name_jnsa, contact_information_jnsa, address_jnsa, member_img_jnsa, username_jnsa, password_jnsa, user_type_jnsa, otp_jnsa, user_status_jnsa) 
                  VALUES ('$fullname_jnsa', '$contact_jnsa', '$address_jnsa', '$imagepath', '$username_jnsa', '$password_jnsa', '$user_type_jnsa', '$otp_jnsa', '$user_status_jnsa')";

    if(mysqli_query($conn, $insertsql)) {
        
        $_SESSION['verify_email'] = $username_jnsa;
        send_verification($fullname_jnsa, $username_jnsa, $otp_jnsa);
        
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