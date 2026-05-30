<?php
$host = "localhost";
$user = "root";
$pass = "";
$dbname = "loan_system";

$conn = new mysqli($host, $user, $pass, $dbname);
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

$member_id = 1;

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $email = trim($_POST['email'] ?? '');
    $contact = trim($_POST['contact'] ?? '');
    $address = trim($_POST['address'] ?? '');

    if (!empty($email) && !empty($contact) && !empty($address)) {
        $stmt = $conn->prepare("UPDATE members SET email = ?, contact_number = ?, mailing_address = ? WHERE id = ?");
        $stmt->bind_param("sssi", $email, $contact, $address, $member_id);
        $stmt->execute();
        $stmt->close();
    }
}

$member_query = "SELECT * FROM members WHERE id = $member_id LIMIT 1";
$member_res = $conn->query($member_query);
$member_data = $member_res->fetch_assoc();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Personal Profile Info - TIGRIS</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.css">
    
    <style>
        body {
            background-color: #f3f4f6;
            font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, Arial, sans-serif;
            color: #1f2937;
        }
        .navbar-tigris {
            background-color: #111827;
            color: #ffffff;
            padding: 14px 24px;
        }
        .brand-title {
            font-weight: 800;
            letter-spacing: 1.5px;
            font-size: 20px;
        }
        .profile-card {
            background-color: #ffffff;
            border-radius: 12px;
            border: 1px solid #e5e7eb;
            box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.05);
            padding: 40px;
            height: 100%;
        }
        .section-heading {
            font-size: 22px;
            font-weight: 700;
            color: #111827;
            margin-bottom: 32px;
        }
        .sub-heading {
            font-size: 16px;
            font-weight: 700;
            color: #111827;
            margin-top: 16px;
            margin-bottom: 24px;
        }
        .field-group {
            margin-bottom: 24px;
        }
        .meta-label {
            font-size: 12px;
            font-weight: 700;
            color: #9ca3af;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            margin-bottom: 8px;
        }
        .form-control-static {
            background-color: #f9fafb;
            border: 1px solid #e5e7eb;
            border-radius: 8px;
            padding: 14px 18px;
            font-size: 15px;
            color: #111827;
            width: 100%;
        }
        .form-control-editable {
            background-color: #ffffff;
            border: 1px solid #e5e7eb;
            border-radius: 8px;
            padding: 14px 18px;
            font-size: 15px;
            color: #111827;
            width: 100%;
        }
        .form-control-editable:focus {
            border-color: #2563eb;
            outline: none;
            box-shadow: 0 0 0 3px rgba(37, 99, 115, 0.15);
        }
        .status-dot {
            width: 10px;
            height: 10px;
            background-color: #10b981;
            border-radius: 50%;
            display: inline-block;
            margin-right: 8px;
        }
        .info-pill-box {
            background-color: #f9fafb;
            border-radius: 6px;
            padding: 12px 20px;
            display: flex;
            justify-content: space-between;
            align-items: center;
            font-size: 14px;
            margin-bottom: 16px;
        }
        .info-pill-label {
            color: #6b7280;
        }
        .info-pill-value {
            font-weight: 600;
            color: #111827;
        }
        .info-pill-verified {
            font-weight: 600;
            color: #10b981;
        }
        .btn-update {
            background-color: #dc2626;
            color: #ffffff;
            font-weight: 700;
            border: none;
            border-radius: 8px;
            padding: 16px;
            width: 100%;
            font-size: 15px;
            transition: background-color 0.2s;
        }
        .btn-update:hover {
            background-color: #b91c1c;
        }
    </style>
</head>
<body>

<nav class="navbar-tigris d-flex justify-content-between align-items-center">
    <a href="dashboard.php" class="text-white text-decoration-none">
        <i class="bi bi-list fs-4 cursor-pointer"></i>
    </a>
    <div class="brand-title">TIGRIS</div>
    <div><i class="bi bi-gear fs-5 cursor-pointer"></i></div>
</nav>

<form action="profile.php" method="POST">
    <div class="container-fluid my-5 px-5">
        <div class="row g-4">
            
            <div class="col-12 col-lg-7">
                <div class="profile-card">
                    <div class="section-heading">Personal Profile Info</div>
                    
                    <div class="row g-3">
                        <div class="col-12 col-md-6 field-group">
                            <div class="meta-label">Full Name</div>
                            <div class="form-control-static">app dev</div>
                        </div>
                        <div class="col-12 col-md-6 field-group">
                            <div class="meta-label">Account/Member ID</div>
                            <div class="form-control-static">TIG-2026-8941</div>
                        </div>
                    </div>

                    <div class="row g-3">
                        <div class="col-12 col-md-6 field-group">
                            <div class="meta-label">Membership Date</div>
                            <div class="form-control-static">January 15, 2024</div>
                        </div>
                        <div class="col-12 col-md-6 field-group">
                            <div class="meta-label">Account Status</div>
                            <div class="form-control-static d-flex align-items-center">
                                <span class="status-dot"></span> Active
                            </div>
                        </div>
                    </div>

                    <div class="border-top pt-4 mt-2">
                        <div class="sub-heading">Additional Information</div>
                        <div class="row g-3">
                            <div class="col-12 col-md-6">
                                <div class="info-pill-box">
                                    <span class="info-pill-label">Total Loans</span>
                                    <span class="info-pill-value">3</span>
                                </div>
                            </div>
                            <div class="col-12 col-md-6">
                                <div class="info-pill-box">
                                    <span class="info-pill-label">Active Loans</span>
                                    <span class="info-pill-value">1</span>
                                </div>
                            </div>
                        </div>
                        <div class="row g-3">
                            <div class="col-12 col-md-6">
                                <div class="info-pill-box">
                                    <span class="info-pill-label">Credit Score</span>
                                    <span class="info-pill-value">750</span>
                                </div>
                            </div>
                            <div class="col-12 col-md-6">
                                <div class="info-pill-box">
                                    <span class="info-pill-label">Verified Status</span>
                                    <span class="info-pill-verified"><i class="bi bi-check-lg me-1"></i>Verified</span>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="col-12 col-lg-5">
                <div class="profile-card">
                    <div class="section-heading mb-4">Contact Details</div>
                    
                    <div class="field-group mb-4">
                        <div class="meta-label">Email Address</div>
                        <input type="email" name="email" class="form-control-editable" value="<?= htmlspecialchars($member_data['email'] ?? 'appdevust@gmail.com') ?>" required>
                    </div>

                    <div class="field-group mb-4">
                        <div class="meta-label">Contact Number</div>
                        <input type="text" name="contact" class="form-control-editable" value="<?= htmlspecialchars($member_data['contact_number'] ?? '+63 917 123 4567') ?>" required>
                    </div>

                    <div class="field-group mb-5">
                        <div class="meta-label">Mailing Address</div>
                        <textarea name="address" class="form-control-editable" rows="3" style="resize: none;" required><?= htmlspecialchars($member_data['mailing_address'] ?? "1008 España Blvd, Sampaloc, Manila, 1015 Metro Manila, Philippines") ?></textarea>
                    </div>

                    <div class="mt-auto">
                        <button type="submit" class="btn btn-update">Update Profile Information</button>
                    </div>
                </div>
            </div>

        </div>
    </div>
</form>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>