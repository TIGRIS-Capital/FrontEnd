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

$loans_query = "SELECT * FROM loans WHERE member_id = $member_id ORDER BY date_applied DESC";
$loans_result = $conn->query($loans_query);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>My Loans - Loan Management System</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.css">
    
    <style>
        :root {
            --sidebar-width: 240px;
            --primary-green: #00bfa5;
            --text-muted: #6c757d;
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
        .loan-card {
            background: #ffffff;
            border: 1px solid #eef2f5;
            border-radius: 12px;
            box-shadow: 0 4px 12px rgba(0,0,0,0.01);
            margin-bottom: 24px;
            overflow: hidden;
        }
        .loan-card-body {
            padding: 28px;
        }
        .loan-card-footer {
            background-color: #fff5f5;
            border-top: 1px solid #ffe3e3;
            padding: 14px 28px;
            font-size: 0.9rem;
            color: #c62828;
        }
        .badge-approved { background-color: #e6f9f6; color: #00bfa5; border: 1px solid #b3ede4; }
        .badge-pending { background-color: #fff8e1; color: #ffb300; border: 1px solid #ffe082; }
        .badge-paid { background-color: #f1f3f4; color: #5f6368; border: 1px solid #dadce0; }
        
        .amount-display {
            color: #dc3545;
            font-weight: 700;
            font-size: 1.6rem;
        }
        .field-label {
            font-size: 12px;
            color: var(--text-muted);
            text-transform: uppercase;
            font-weight: 500;
            margin-bottom: 4px;
        }
        .field-value {
            font-size: 14px;
            color: #333;
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
    <a href="#" class="nav-link-custom active"><i class="bi bi-cash-stack"></i> My Loans</a>
    <a href="applyforloan.php" class="nav-link-custom"><i class="bi bi-file-earmark-plus"></i> Apply for Loan</a>
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
            <h3 class="fw-bold mb-1">My Loan Applications</h3>
            <p class="text-muted mb-4">Track the status of your loan applications</p>

            <?php if ($loans_result->num_rows > 0): ?>
                <?php while ($loan = $loans_result->fetch_assoc()): ?>
                    <div class="loan-card">
                        <div class="loan-card-body">
                            <div class="d-flex justify-content-between align-items-start mb-4">
                                <div>
                                    <div class="d-flex align-items-center gap-3 flex-wrap">
                                        <h4 class="fw-bold mb-0" style="color: #2d3748;"><?= htmlspecialchars($loan['loan_type']) ?></h4>
                                        <?php 
                                            $badge_class = 'badge-pending';
                                            if ($loan['status'] == 'Approved') $badge_class = 'badge-approved';
                                            if ($loan['status'] == 'Fully Paid') $badge_class = 'badge-paid';
                                        ?>
                                        <span class="badge <?= $badge_class ?> px-2.5 py-1.5 rounded-pill fw-medium" style="font-size: 12px;">
                                            <?php if ($loan['status'] == 'Approved'): ?><i class="bi bi-check-circle-fill me-1"></i><?php endif; ?>
                                            <?php if ($loan['status'] == 'Pending'): ?><i class="bi bi-clock-fill me-1"></i><?php endif; ?>
                                            <?= $loan['status'] ?>
                                        </span>
                                    </div>
                                    <small class="text-muted text-uppercase d-block mt-1" style="font-size: 13px; font-weight: 500;">Loan ID: <?= htmlspecialchars($loan['loan_code']) ?></small>
                                </div>
                                <div class="text-end">
                                    <div class="field-label">Amount</div>
                                    <div class="amount-display">$<?= number_format($loan['amount_requested']) ?></div>
                                </div>
                            </div>

                            <div class="row g-3">
                                <div class="col-6 col-md-4">
                                    <div class="field-label">Date Applied</div>
                                    <div class="field-value fw-medium"><?= date('M d, Y', strtotime($loan['date_applied'])) ?></div>
                                </div>
                                
                                <?php if ($loan['status'] == 'Pending'): ?>
                                    <div class="col-6 col-md-8">
                                        <div class="field-label">Status Update</div>
                                        <div class="field-value text-secondary">Your application is under review. We'll notify you within 2-3 business days.</div>
                                    </div>
                                <?php elseif ($loan['status'] == 'Approved'): ?>
                                    <div class="col-6 col-md-4">
                                        <div class="field-label">Monthly Payment</div>
                                        <div class="field-value fw-medium">$450.00</div>
                                    </div>
                                    <div class="col-6 col-md-4">
                                        <div class="field-label">Next Payment Due</div>
                                        <div class="field-value fw-medium">June 1, 2024</div>
                                    </div>
                                <?php elseif ($loan['status'] == 'Fully Paid'): ?>
                                    <div class="col-6 col-md-8">
                                        <div class="field-label">Completion</div>
                                        <div class="field-value fw-semibold text-success">Congratulations! This loan has been fully paid off.</div>
                                    </div>
                                <?php endif; ?>
                            </div>
                        </div>
                        
                        <?php if ($loan['status'] == 'Approved'): ?>
                            <div class="loan-card-footer">
                                Your loan has been approved! Funds will be disbursed within 24 hours.
                            </div>
                        <?php endif; ?>
                    </div>
                <?php endwhile; ?>
            <?php else: ?>
                <div class="card p-5 text-center text-muted bg-white border-0 shadow-sm" style="border-radius: 12px;">
                    <i class="bi bi-folder2-open display-4 mb-3 text-black-50"></i>
                    <h5>No loan applications discovered.</h5>
                </div>
            <?php endif; ?>
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