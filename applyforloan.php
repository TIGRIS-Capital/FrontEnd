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

$member_query = "SELECT * FROM members WHERE id = $member_id LIMIT 1";
$member_res = $conn->query($member_query);
$member_data = $member_res->fetch_assoc();

$success_message = "";
$error_message = "";

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $loan_type = trim($_POST['loan_type'] ?? '');
    $amount_requested = floatval($_POST['amount_requested'] ?? 0);
    $loan_term = trim($_POST['loan_term'] ?? '');

    if (empty($loan_type) || $amount_requested <= 0 || empty($loan_term)) {
        $error_message = "All fields marked with * are required.";
    } elseif ($amount_requested < 1000 || $amount_requested > 500000) {
        $error_message = "Loan amount must be between $1,000 and $500000.";
    } else {
        $year_suffix = date('Y');
        $rand_num = str_pad(rand(1, 999), 3, '0', STR_PAD_LEFT);
        $loan_code = "LN-" . $year_suffix . "-" . $rand_num;
        $date_applied = date('Y-m-d');
        $status = "Pending";

        $stmt = $conn->prepare("INSERT INTO loans (member_id, loan_code, loan_type, amount_requested, date_applied, status) VALUES (?, ?, ?, ?, ?, ?)");
        $stmt->bind_param("issdss", $member_id, $loan_code, $loan_type, $amount_requested, $date_applied, $status);

        if ($stmt->execute()) {
            $success_message = "Your application has been submitted successfully! Redirecting...";
            echo "<script>
                    setTimeout(function() {
                        window.location.href = 'myloans.php';
                    }, 2000);
                  </script>";
        } else {
            $error_message = "Something went wrong while processing your transaction. Please try again.";
        }
        $stmt->close();
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Apply for Loan - Loan Management System</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.css">
    
    <style>
        :root {
            --sidebar-width: 240px;
            --primary-green: #00bfa5;
            --text-muted: #6c757d;
            --figma-red: #dc3545;
        }
        body {
            font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, Helvetica, Arial, sans-serif;
            background-color: #f8f9fa;
            color: #333;
        }
        .wrapper {
            display: flex;
            min-height: 100vh;
        }
        .sidebar {
            width: var(--sidebar-width);
            background-color: #ffffff;
            border-right: 1px solid #eef2f5;
            display: flex;
            flex-direction: column;
            justify-content: space-between;
            position: fixed;
            height: 100vh;
            z-index: 100;
        }
        .main-content {
            margin-left: var(--sidebar-width);
            flex-grow: 1;
            min-width: 0;
        }
        .portal-brand {
            padding: 24px;
            display: flex;
            align-items: center;
            gap: 12px;
        }
        .brand-icon {
            background-color: var(--primary-green);
            color: #fff;
            padding: 8px;
            border-radius: 8px;
            font-size: 1.25rem;
        }
        .nav-menu {
            padding: 0 12px;
        }
        .nav-link-custom {
            display: flex;
            align-items: center;
            gap: 12px;
            padding: 12px 16px;
            color: #555;
            text-decoration: none;
            border-radius: 8px;
            margin-bottom: 4px;
            font-weight: 500;
            transition: all 0.2s ease;
        }
        .nav-link-custom.active, .nav-link-custom:hover {
            background-color: #e6f9f6;
            color: var(--primary-green);
        }
        .nav-link-custom.logout-btn {
            color: #555 !important;
        }
        .nav-link-custom.logout-btn:hover {
            background-color: #f8d7da;
            color: #721c24 !important;
        }
        .header-bar {
            background-color: #ffffff;
            border-bottom: 1px solid #eef2f5;
            padding: 16px 40px;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }
        .user-menu-zone {
            display: flex;
            align-items: center;
            gap: 20px;
            position: relative;
        }
        .avatar-circle {
            width: 36px;
            height: 36px;
            background-color: var(--primary-green);
            color: #fff;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 1.1rem;
            cursor: pointer;
        }
        .content-container {
            padding: 40px;
        }
        .form-card {
            background: #ffffff;
            border: 1px solid #eef2f5;
            border-radius: 12px;
            box-shadow: 0 4px 12px rgba(0,0,0,0.01);
            padding: 40px;
            margin-bottom: 24px;
        }
        
        .step-indicator-wrapper {
            display: flex;
            justify-content: center;
            align-items: center;
            gap: 16px;
            margin-bottom: 48px;
        }
        .step-node {
            display: flex;
            align-items: center;
            gap: 10px;
            font-size: 14px;
            color: var(--text-muted);
            font-weight: 500;
        }
        .step-circle {
            width: 32px;
            height: 32px;
            border-radius: 50%;
            background-color: #f1f3f4;
            color: #5f6368;
            display: flex;
            align-items: center;
            justify-content: center;
            font-weight: 600;
        }
        .step-node.active .step-circle {
            background-color: #e53935;
            color: #ffffff;
        }
        .step-node.active {
            color: #2d3748;
        }
        .step-line {
            height: 1px;
            background-color: #e2e8f0;
            width: 80px;
        }

        .form-label-custom {
            font-size: 14px;
            font-weight: 600;
            color: #2d3748;
            margin-bottom: 8px;
        }
        .form-control-custom {
            background-color: #f8f9fa;
            border: 1px solid #e2e8f0;
            border-radius: 8px;
            padding: 12px 16px;
            font-size: 14px;
            color: #4a5568;
        }
        .form-control-custom:focus {
            background-color: #ffffff;
            border-color: var(--primary-green);
            box-shadow: 0 0 0 3px rgba(0, 191, 165, 0.15);
        }
        .form-control-custom::placeholder {
            color: #a0aec0;
        }
        .form-control-custom[readonly] {
            background-color: #f8f9fa;
            color: #718096;
            cursor: not-allowed;
        }

        .guidelines-card {
            background: #ffffff;
            border: 1px solid #eef2f5;
            border-radius: 12px;
            padding: 28px;
        }
        .guidelines-title {
            font-size: 15px;
            font-weight: 600;
            color: #2d3748;
            margin-bottom: 16px;
        }
        .guidelines-list {
            padding-left: 16px;
            margin: 0;
            font-size: 13.5px;
            color: #4a5568;
            line-height: 1.8;
        }
        .guidelines-list li::marker {
            color: #e53935;
        }

        .btn-cancel {
            background-color: #ffffff;
            border: 1px solid #e2e8f0;
            border-radius: 8px;
            padding: 10px 24px;
            font-weight: 500;
            color: #4a5568;
        }
        .btn-cancel:hover {
            background-color: #f8f9fa;
        }
        .btn-next {
            background-color: #e53935;
            border: none;
            border-radius: 8px;
            padding: 10px 28px;
            font-weight: 500;
            color: #ffffff;
        }
        .btn-next:hover {
            background-color: #d32f2f;
        }

        .figma-dropdown {
            position: absolute;
            right: 0;
            top: 45px;
            background: #ffffff;
            border: 1px solid #eef2f5;
            border-radius: 8px;
            box-shadow: 0 4px 12px rgba(0,0,0,0.1);
            width: 160px;
            display: none;
            z-index: 1050;
        }
        .figma-dropdown.show { display: block; }
        .figma-dropdown-item {
            padding: 10px 16px;
            color: #333;
            text-decoration: none;
            display: block;
            font-size: 0.95rem;
        }
        .figma-dropdown-item:hover { background-color: #f8f9fa; }
        .figma-dropdown-item.text-danger:hover { background-color: #f8d7da; }
    </style>
</head>
<body>

<div class="wrapper">
    <nav class="sidebar">
        <div>
            <div class="portal-brand">
                <div class="brand-icon"><i class="bi bi-wallet2"></i></div>
                <div>
                    <h6 class="m-0 fw-bold">Member Portal</h6>
                    <small class="text-muted" style="font-size: 11px;">My Account</small>
                </div>
            </div>
            
            <div class="nav-menu">
                <a href="dashboard.php" class="nav-link-custom"><i class="bi bi-grid"></i> Dashboard</a>
                <a href="myloans.php" class="nav-link-custom"><i class="bi bi-cash-stack"></i> My Loans</a>
                <a href="#" class="nav-link-custom active"><i class="bi bi-file-earmark-plus"></i> Apply for Loan</a>
                <a href="loancalculator.php" class="nav-link-custom"><i class="bi bi-calculator"></i> Loan Calculator</a>
                <a href="#" class="nav-link-custom"><i class="bi bi-credit-card"></i> Payments</a>
            </div>
        </div>
        
        <div class="p-3">
            <a href="#" class="nav-link-custom logout-btn text-danger"><i class="bi bi-box-arrow-left"></i> Logout</a>
        </div>
    </nav>

    <main class="main-content">
        <header class="header-bar">
            <div>
                <h4 class="m-0 fw-bold">Member Portal</h4>
                <small class="text-muted">Welcome back, <?= htmlspecialchars($member_data['username']) ?></small>
            </div>
            
            <div class="user-menu-zone">
                <div class="position-relative cursor-pointer">
                    <i class="bi bi-bell fs-5 text-muted"></i>
                    <span class="position-absolute top-0 start-100 translate-middle p-1 bg-danger border border-light rounded-circle"></span>
                </div>
                
                <div class="d-flex align-items-center gap-2" id="profileDropdownTrigger" style="cursor: pointer;">
                    <div class="avatar-circle">
                        <i class="bi bi-person"></i>
                    </div>
                    <div class="d-none d-md-block text-start">
                        <div class="m-0 fw-bold lh-1" style="font-size: 14px;"><?= htmlspecialchars($member_data['username']) ?></div>
                        <small class="text-muted" style="font-size: 12px;"><?= htmlspecialchars($member_data['role']) ?></small>
                    </div>
                    <i class="bi bi-chevron-down text-muted small"></i>
                </div>

                <div class="figma-dropdown" id="profileDropdownMenu">
    <a href="profile.php" class="figma-dropdown-item">Profile</a>
    <a href="#" class="figma-dropdown-item">Settings</a>
    <hr class="m-0 text-muted opacity-25">
    <a href="#" class="figma-dropdown-item text-danger fw-bold">Logout</a>
</div>
            </div>
        </header>

        <div class="content-container">
            <h3 class="fw-bold mb-1">Loan Application</h3>
            <p class="text-muted mb-4">Complete all steps to submit your loan application</p>

            <?php if (!empty($error_message)): ?>
                <div class="alert alert-danger mb-4" role="alert"><?= $error_message ?></div>
            <?php endif; ?>

            <?php if (!empty($success_message)): ?>
                <div class="alert alert-success mb-4" role="alert"><?= $success_message ?></div>
            <?php endif; ?>

            <div class="form-card">
                <div class="step-indicator-wrapper">
                    <div class="step-node active">
                        <div class="step-circle">1</div>
                        <div>Loan Details</div>
                    </div>
                    <div class="step-line"></div>
                    <div class="step-node">
                        <div class="step-circle">2</div>
                        <div>Contact Information</div>
                    </div>
                    <div class="step-line"></div>
                    <div class="step-node">
                        <div class="step-circle">3</div>
                        <div>Address Verification</div>
                    </div>
                </div>

                <h5 class="fw-bold mb-4" style="color: #2d3748;">Step 1: Loan Details</h5>

                <form action="applyforloan.php" method="POST">
                    <div class="mb-4">
                        <label class="form-label form-label-custom">Full Name <span class="text-danger">*</span></label>
                        <input type="text" class="form-control form-control-custom w-100" value="John Michael Smith" readonly>
                        <small class="text-muted d-block mt-1" style="font-size: 12px;">This field is pre-filled from your member profile</small>
                    </div>

                    <div class="row g-4 mb-4">
                        <div class="col-12 col-md-6">
                            <label for="loan_type" class="form-label form-label-custom">Loan Type <span class="text-danger">*</span></label>
                            <select name="loan_type" id="loan_type" class="form-select form-control-custom w-100" required>
                                <option value="" selected hidden>Select Loan Type</option>
                                <option value="Personal Loan">Personal Loan</option>
                                <option value="Auto Loan">Auto Loan</option>
                                <option value="Home Improvement Loan">Home Improvement Loan</option>
                                <option value="Business Loan">Business Loan</option>
                                <option value="Education Loan">Education Loan</option>
                            </select>
                        </div>
                        <div class="col-12 col-md-6">
                            <label for="amount_requested" class="form-label form-label-custom">Desired Loan Amount <span class="text-danger">*</span></label>
                            <div class="position-relative">
                                <span class="position-absolute top-50 start-0 translate-middle-y ps-3 text-muted" style="font-size: 14px;">$</span>
                                <input type="number" name="amount_requested" id="amount_requested" class="form-control form-control-custom w-100 ps-4" step="0.01" placeholder="0.00" min="1000" max="500000" required>
                            </div>
                            <small class="text-muted d-block mt-1" style="font-size: 12px;">Minimum: $1,000 | Maximum: $500,000</small>
                        </div>
                    </div>

                    <div class="mb-5">
                        <label for="loan_term" class="form-label form-label-custom">Loan Term <span class="text-danger">*</span></label>
                        <select name="loan_term" id="loan_term" class="form-select form-control-custom w-100" required>
                            <option value="" selected hidden>Select Loan Term</option>
                            <option value="12 Months">12 Months</option>
                            <option value="24 Months">24 Months</option>
                            <option value="36 Months">36 Months</option>
                            <option value="48 Months">48 Months</option>
                            <option value="60 Months">60 Months</option>
                        </select>
                    </div>

                    <div class="d-flex justify-content-between align-items-center pt-2">
                        <button type="button" class="btn btn-cancel" onclick="window.location.href='dashboard.php'">Cancel</button>
                        <button type="submit" class="btn btn-next">Next <i class="bi bi-chevron-right ms-1" style="font-size: 12px;"></i></button>
                    </div>
                </form>
            </div>

            <div class="guidelines-card">
                <div class="guidelines-title">Application Guidelines</div>
                <ul class="guidelines-list">
                    <li>All fields marked with <span class="text-danger">*</span> are required</li>
                    <li>Processing time is typically 2-3 business days</li>
                    <li>You will receive email and SMS notifications about your application status</li>
                    <li>Contact our support team if you need assistance: (555) 000-0000</li>
                </ul>
            </div>
        </div>
    </main>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
<script>
    const dropdownTrigger = document.getElementById('profileDropdownTrigger');
    const dropdownMenu = document.getElementById('profileDropdownMenu');

    dropdownTrigger.addEventListener('click', function(event) {
        event.stopPropagation();
        dropdownMenu.classList.toggle('show');
    });

    document.addEventListener('click', function() {
        if (dropdownMenu.classList.contains('show')) {
            dropdownMenu.classList.remove('show');
        }
    });
</script>
</body>
</html>