<?php

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

// Load Composer's autoloader
require 'vendor/autoload.php';

function send_verification($fullname, $email, $otp){

    $mail = new PHPMailer(true);

    try {

        $mail->isSMTP();
        $mail->Host       = 'smtp.gmail.com';
        $mail->SMTPAuth   = true;
        $mail->Username   = 'jertzavryll.naval.cics@ust.edu.ph';
        $mail->Password   = 'xjnk galc cimw olop';              
        $mail->SMTPSecure = 'tls';
        $mail->Port       = 587;

        $mail->setFrom('jertzavryll.naval.cics@ust.edu.ph', 'TIGRIS Capital');
        $mail->addAddress($email);

        // Content
        $mail->isHTML(true);
        $mail->Subject = "OTP - TIGRIS Capital Loan Management System";
        
        $mail->Body = '<h3 style="color: #d63346; margin-bottom: 20px;">Hello, '.$fullname.'</h3>
                       <p>Thank you for registering an account with <strong>TIGRIS Capital Loan Management System</strong>.</p>
                       <p style="margin-top: 20px;">To complete your registration and secure your profile, please proceed to the verification page and enter the validation code below.</p>
                       <p>Verification code:</p>
                       <div style="background-color: #f8f9fa; padding: 15px; border-radius: 5px; text-align: center; font-size: 24px; color: #d63346; font-weight: bold;">'.$otp.'</div>
                       <p style="margin-top:10px;font-size: 14px; color: #6c757d;">— TIGRIS Capital Team</p>';

        $mail->send();

        echo "
        <script src='https://cdn.jsdelivr.net/npm/sweetalert2@11'></script>
        <script>
            setTimeout(function() {
                Swal.fire({
                    icon: 'success',
                    title: 'Success!',
                    text: 'Email Successfully Sent!',
                    confirmButtonText: 'OK',
                    confirmButtonColor: '#de3b4a'
                }).then(() => {
                    window.location.href = 'otpverify_jn.php';
                });
            }, 100);
        </script>
        ";

    } catch (Exception $e) {

        echo "
        <script src='https://cdn.jsdelivr.net/npm/sweetalert2@11'></script>
        <script>
            setTimeout(function() {
                Swal.fire({
                    icon: 'error',
                    title: 'Email Failed!',
                    text: 'Message could not be sent.',
                    footer: '".addslashes($mail->ErrorInfo)."',
                    confirmButtonColor: '#de3b4a'
                });
            }, 100);
        </script>
        ";
    }
}
?>