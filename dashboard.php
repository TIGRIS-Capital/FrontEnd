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

$balance_query = "SELECT SUM(amount_requested) AS total_balance, COUNT(id) AS active_count 
                  FROM loans WHERE member_id = $member_id AND status = 'Approved'";
$balance_res = $conn->query($balance_query);
$balance_data = $balance_res->fetch_assoc();

$outstanding_balance = $balance_data['total_balance'] ?? 0.00;
$active_loans_count = $balance_data['active_count'] ?? 0;

$loans_query = "SELECT * FROM loans WHERE member_id = $member_id ORDER BY date_applied DESC";
$loans_result = $conn->query($loans_query);
$total_rows = $loans_result->num_rows;
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Loan Management System Dashboard</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.css">
    
    <style>
        :root {
            --sidebar-width: 240px;
            --primary-green: #00bfa5;
            --dark-bg: #1c1c1e;
            --text-muted: #6c757d;
            --card-red: #dc3545;
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
        .metric-container {
            padding: 40px;
        }
        .figma-card {
            border-radius: 12px;
            border: none;
            overflow: hidden;
            color: #ffffff;
            box-shadow: 0 4px 12px rgba(0,0,0,0.03);
        }
        .figma-card.red-variant .card-main-body { background-color: #e53935; }
        .figma-card.red-variant .card-sub-footer { background-color: #ffebee; color: #c62828; }
        .figma-card.green-variant .card-main-body { background-color: #00bfa5; }
        .figma-card.green-variant .card-sub-footer { background-color: #e0f2f1; color: #004d40; }
        
        .card-main-body {
            padding: 24px;
            position: relative;
        }
        .card-sub-footer {
            padding: 12px 24px;
            font-size: 0.875rem;
            font-weight: 500;
        }
        .decorative-circle {
            position: absolute;
            right: 24px;
            top: 50%;
            transform: translateY(-50%);
            width: 48px;
            height: 48px;
            background: rgba(255,255,255,0.25);
            border-radius: 50%;
        }
        .badge-approved { background-color: #e6f9f6; color: #00bfa5; border: 1px solid #b3ede4; }
        .badge-pending { background-color: #fff8e1; color: #ffb300; border: 1px solid #ffe082; }
        .badge-paid { background-color: #e8f0fe; color: #1a73e8; border: 1px solid #c2e7ff; }
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
    <a href="#" class="nav-link-custom active"><i class="bi bi-grid"></i> Dashboard</a>
    <a href="myloans.php" class="nav-link-custom"><i class="bi bi-cash-stack"></i> My Loans</a>
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

        <div class="metric-container">
            <h3 class="fw-bold mb-1">My Loan Dashboard</h3>
            <p class="text-muted mb-4">Track your loans and upcoming payments</p>

            <div class="row g-4 mb-5">
                <div class="col-12 col-xl-6">
                    <div class="card figma-card red-variant">
                        <div class="card-main-body">
                            <small class="text-white-50 uppercase fw-semibold">Current Outstanding Balance</small>
                            <h2 class="fw-bold mt-2 mb-1">$<?= number_format($outstanding_balance, 2) ?></h2>
                            <small class="text-white-50">Across <?= $active_loans_count ?> active loans</small>
                            <div class="decorative-circle"></div>
                        </div>
                        <div class="card-sub-footer">
                            This includes principal and accrued interest
                        </div>
                    </div>
                </div>

                <div class="col-12 col-xl-6">
                    <div class="card figma-card green-variant">
                        <div class="card-main-body">
                            <small class="text-white-50 uppercase fw-semibold">Next Payment Due Date</small>
                            <h2 class="fw-bold mt-2 mb-1">June 1, 2024</h2>
                            <small class="text-white-50">Payment amount: $2,850.00</small>
                            <div class="decorative-circle"></div>
                        </div>
                        <div class="card-sub-footer">
                            Set up auto-pay to never miss a payment
                        </div>
                    </div>
                </div>
            </div>

            <div class="card bg-white p-4 mb-4 border-0 shadow-sm" style="border-radius: 12px;">
                <div class="d-flex justify-content-between align-items-center mb-3">
                    <div>
                        <h5 class="fw-bold m-0">My Loans</h5>
                        <small class="text-muted">View all your active and past loan applications</small>
                    </div>
                </div>

                <div class="table-responsive">
                    <table class="table align-middle table-hover mx-0">
                        <thead class="table-light text-muted" style="font-size: 13px;">
                            <tr>
                                <th scope="col" class="py-3">Loan Type</th>
                                <th scope="col" class="py-3">Amount Requested</th>
                                <th scope="col" class="py-3">Date Applied</th>
                                <th scope="col" class="py-3">Status</th>
                                <th scope="col" class="py-3 text-end">Actions</th>
                            </tr>
                        </thead>
                        <tbody style="font-size: 14px;">
                            <?php if($total_rows > 0): ?>
                                <?php while($loan = $loans_result->fetch_assoc()): ?>
                                    <tr>
                                        <td class="py-3">
                                            <div class="fw-bold text-secondary mb-0"><?= htmlspecialchars($loan['loan_type']) ?></div>
                                            <small class="text-muted text-uppercase" style="font-size: 11px;"><?= $loan['loan_code'] ?></small>
                                        </td>
                                        <td class="py-3 fw-semibold text-dark">$<?= number_format($loan['amount_requested']) ?></td>
                                        <td class="py-3 text-muted"><?= date('M d, Y', strtotime($loan['date_applied'])) ?></td>
                                        <td class="py-3">
                                            <?php 
                                                $badge_class = 'badge-pending';
                                                if($loan['status'] == 'Approved') $badge_class = 'badge-approved';
                                                if($loan['status'] == 'Fully Paid') $badge_class = 'badge-paid';
                                            ?>
                                            <span class="badge <?= $badge_class ?> px-2 py-1.5 rounded-pill fw-medium">
                                                <?php if($loan['status'] == 'Approved'): ?><i class="bi bi-graph-up-arrow me-1"></i><?php endif; ?>
                                                <?php if($loan['status'] == 'Pending'): ?><i class="bi bi-clock me-1"></i><?php endif; ?>
                                                <?php if($loan['status'] == 'Fully Paid'): ?><i class="bi bi-currency-dollar me-1"></i><?php endif; ?>
                                                <?= $loan['status'] ?>
                                            </span>
                                        </td>
                                        <td class="py-3 text-end">
                                            <a href="#" class="text-danger text-decoration-none fw-semibold" style="font-size:13px;">View Details</a>
                                        </td>
                                    </tr>
                                <?php endwhile; ?>
                            <?php else: ?>
                                <tr><td colspan="5" class="text-center py-4 text-muted">No system loan records found.</td></tr>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>

                <div class="d-flex justify-content-between align-items-center mt-3 pt-2 border-top">
                    <span class="text-muted" style="font-size:14px;">Showing <?= $total_rows ?> loans</span>
                    <button class="btn btn-danger btn-sm px-3 py-2 fw-semibold" style="border-radius: 6px; background-color:#e53935;">Apply for New Loan</button>
                </div>
            </div>

            <div class="row g-4 mt-2">
                <div class="col-12 col-md-4">
                    <div class="card border-0 p-4 shadow-sm bg-white h-100" style="border-radius:12px;">
                        <div class="text-danger mb-3"><i class="bi bi-graph-up-arrow fs-4"></i></div>
                        <h6 class="fw-bold mb-1">Payment History</h6>
                        <p class="text-muted small mb-3">24 payments made on time</p>
                        <a href="#" class="text-danger text-decoration-none fw-semibold mt-auto small">View Full History &rarr;</a>
                    </div>
                </div>
                <div class="col-12 col-md-4">
                    <div class="card border-0 p-4 shadow-sm bg-white h-100" style="border-radius:12px;">
                        <div class="text-danger mb-3"><i class="bi bi-calendar-event fs-4"></i></div>
                        <h6 class="fw-bold mb-1">Payment Schedule</h6>
                        <p class="text-muted small mb-3">View your upcoming payments</p>
                        <a href="#" class="text-danger text-decoration-none fw-semibold mt-auto small">View Schedule &rarr;</a>
                    </div>
                </div>
                <div class="col-12 col-md-4">
                    <div class="card border-0 p-4 shadow-sm bg-white h-100" style="border-radius:12px;">
                        <div class="text-danger mb-3"><i class="bi bi-currency-dollar fs-4"></i></div>
                        <h6 class="fw-bold mb-1">Make a Payment</h6>
                        <p class="text-muted small mb-3">Pay ahead or make extra payment</p>
                        <a href="#" class="text-danger text-decoration-none fw-semibold mt-auto small">Make Payment &rarr;</a>
                    </div>
                </div>
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