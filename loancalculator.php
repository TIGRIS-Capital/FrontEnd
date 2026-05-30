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
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Loan Eligibility Calculator - Loan Management System</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.css">
    
    <style>
        :root {
            --sidebar-width: 240px;
            --primary-green: #00bfa5;
            --text-muted: #6c757d;
            --figma-red: #dc3545;
            --soft-red: #fff5f5;
            --dark-green: #004d40;
            --light-green: #e0f2f1;
            --emerald: #00796b;
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
        
        .calculator-card {
            background: #ffffff;
            border: 1px solid #eef2f5;
            border-radius: 12px;
            padding: 32px;
            height: 100%;
        }
        .breakdown-card {
            background: #ffffff;
            border: 1px solid #eef2f5;
            border-radius: 12px;
            padding: 32px;
            margin-bottom: 24px;
        }
        .summary-card {
            background: #ffffff;
            border: 1px solid #eef2f5;
            border-radius: 12px;
            padding: 32px;
            margin-bottom: 24px;
        }
        .eligibility-banner {
            background-color: #e8f5e9;
            border: 1px solid #a5d6a7;
            border-radius: 12px;
            padding: 24px;
            margin-bottom: 24px;
        }

        .section-title {
            font-size: 18px;
            font-weight: 600;
            color: #1e293b;
            margin-bottom: 24px;
        }
        .value-box {
            background-color: #fff5f5;
            color: #e53935;
            font-weight: 600;
            padding: 6px 16px;
            border-radius: 6px;
            font-size: 16px;
        }
        
        .range-slider {
            -webkit-appearance: none;
            width: 100%;
            height: 6px;
            border-radius: 3px;
            background: #e2e8f0;
            outline: none;
        }
        .range-slider::-webkit-slider-thumb {
            -webkit-appearance: none;
            appearance: none;
            width: 20px;
            height: 20px;
            border-radius: 50%;
            background: #e53935;
            cursor: pointer;
            border: none;
        }

        .breakdown-box {
            background-color: #f8f9fa;
            border-radius: 8px;
            padding: 20px;
            margin-bottom: 16px;
            position: relative;
        }
        .breakdown-box .box-icon {
            position: absolute;
            right: 20px;
            top: 50%;
            transform: translateY(-50%);
            font-size: 1.25rem;
            color: #e53935;
        }
        .total-payment-box {
            background-color: #fff5f5;
            border: 1px solid #fecaca;
            border-radius: 8px;
            padding: 20px;
        }

        .btn-proceed {
            background-color: #004d40;
            color: #ffffff;
            font-weight: 600;
            border: none;
            border-radius: 8px;
            padding: 14px;
            width: 100%;
            transition: background-color 0.2s;
        }
        .btn-proceed:hover {
            background-color: #00332c;
            color: #ffffff;
        }

        .info-card {
            background: #ffffff;
            border: 1px solid #eef2f5;
            border-radius: 12px;
            padding: 32px;
            margin-top: 24px;
        }
        .info-dot {
            width: 6px;
            height: 6px;
            background-color: #e53935;
            border-radius: 50%;
            display: inline-block;
            margin-right: 8px;
            vertical-align: middle;
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
                <a href="applyforloan.php" class="nav-link-custom"><i class="bi bi-file-earmark-plus"></i> Apply for Loan</a>
                <a href="#" class="nav-link-custom active"><i class="bi bi-calculator"></i> Loan Calculator</a>
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
            <h3 class="fw-bold mb-1">Loan Eligibility Calculator</h3>
            <p class="text-muted mb-5">Calculate your potential loan payments and check eligibility</p>

            <div class="row g-4">
                <div class="col-12 col-xl-7">
                    <div class="calculator-card">
                        <div class="section-title">Loan Parameters</div>
                        
                        <div class="mb-5">
                            <div class="d-flex justify-content-between align-items-center mb-3">
                                <span class="fw-medium text-secondary" style="font-size: 15px;">Desired Loan Amount</span>
                                <span class="value-box" id="amountLabel">$25,000.00</span>
                            </div>
                            <input type="range" class="range-slider" id="amountSlider" min="5000" max="150000" step="1000" value="25000">
                            <div class="d-flex justify-content-between text-muted mt-2" style="font-size: 12px;">
                                <span>$5,000</span>
                                <span>$150,000</span>
                            </div>
                        </div>

                        <div class="mb-5">
                            <div class="d-flex justify-content-between align-items-center mb-3">
                                <span class="fw-medium text-secondary" style="font-size: 15px;">Repayment Term</span>
                                <span class="value-box" id="termLabel">36 months</span>
                            </div>
                            <input type="range" class="range-slider" id="termSlider" min="6" max="84" step="6" value="36">
                            <div class="d-flex justify-content-between text-muted mt-2" style="font-size: 12px;">
                                <span>6 months</span>
                                <span>84 months (7 years)</span>
                            </div>
                        </div>

                        <div class="pt-3 border-top">
                            <div class="d-flex justify-content-between mb-2" style="font-size: 14px;">
                                <span class="text-muted">Interest Rate (Fixed)</span>
                                <span class="fw-semibold text-dark">8.5% APR</span>
                            </div>
                            <div class="d-flex justify-content-between" style="font-size: 14px;">
                                <span class="text-muted">Number of Payments</span>
                                <span class="fw-semibold text-dark" id="paymentCountLabel">36 monthly payments</span>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="col-12 col-xl-5">
                    <div class="breakdown-card">
                        <div class="d-flex justify-content-between align-items-center mb-4">
                            <div class="fw-bold text-dark" style="font-size: 16px;">Payment Breakdown</div>
                            <i class="bi bi-currency-dollar text-danger fs-5"></i>
                        </div>

                        <div class="breakdown-box">
                            <div class="text-muted small uppercase fw-medium mb-1">Monthly Principal</div>
                            <h5 class="fw-bold m-0 text-dark" id="monthlyPrincipal">$694.44</h5>
                            <div class="box-icon"><i class="bi bi-graph-up-arrow text-danger"></i></div>
                        </div>

                        <div class="breakdown-box">
                            <div class="text-muted small uppercase fw-medium mb-1">Monthly Interest</div>
                            <h5 class="fw-bold m-0 text-dark" id="monthlyInterest">$94.74</h5>
                            <div class="box-icon"><i class="bi bi-calendar-event text-danger"></i></div>
                        </div>

                        <div class="total-payment-box">
                            <div class="text-muted small uppercase fw-medium mb-1">Total Monthly Payment</div>
                            <h4 class="fw-bold m-0 text-danger" id="totalMonthlyPayment">$789.19</h4>
                        </div>
                    </div>

                    <div class="summary-card">
                        <div class="fw-bold text-dark mb-4" style="font-size: 16px;">Total Repayment Summary</div>
                        
                        <div class="d-flex justify-content-between mb-3" style="font-size: 14px;">
                            <span class="text-muted">Principal Amount</span>
                            <span class="fw-semibold text-dark" id="summaryPrincipal">$25,000.00</span>
                        </div>
                        <div class="d-flex justify-content-between mb-3" style="font-size: 14px;">
                            <span class="text-muted">Total Interest</span>
                            <span class="fw-semibold text-dark" id="summaryInterest">$3,410.78</span>
                        </div>
                        <div class="d-flex justify-content-between pt-3 border-top">
                            <span class="fw-medium text-dark">Total Repayment Amount</span>
                            <span class="fw-bold text-danger fs-5" id="summaryTotal">$28,410.78</span>
                        </div>
                    </div>

                    <div class="eligibility-banner">
                        <div class="d-flex gap-3 align-items-start">
                            <div class="text-success fs-4"><i class="bi bi-check-circle-fill"></i></div>
                            <div>
                                <h6 class="fw-bold text-success mb-1">Likely Eligible</h6>
                                <p class="text-success small mb-3">Eligibility Score: 100/100</p>
                            </div>
                        </div>
                        <button class="btn btn-proceed" onclick="window.location.href='applyforloan.php'">Proceed with Application</button>
                    </div>
                </div>
            </div>

            <div class="info-card">
                <div class="fw-bold text-dark mb-4" style="font-size: 16px;">Important Information</div>
                <div class="row g-4">
                    <div class="col-12 col-md-4">
                        <div class="fw-semibold text-dark mb-2" style="font-size: 14px;"><span class="info-dot"></span>Estimated Calculations</div>
                        <p class="text-muted m-0" style="font-size: 13px; line-height: 1.6;">These are estimates based on the provided interest rate. Actual terms may vary.</p>
                    </div>
                    <div class="col-12 col-md-4">
                        <div class="fw-semibold text-dark mb-2" style="font-size: 14px;"><span class="info-dot"></span>Eligibility Check</div>
                        <p class="text-muted m-0" style="font-size: 13px; line-height: 1.6;">This is a preliminary check. Final approval depends on credit history and documentation.</p>
                    </div>
                    <div class="col-12 col-md-4">
                        <div class="fw-semibold text-dark mb-2" style="font-size: 14px;"><span class="info-dot"></span>Fixed Rate</div>
                        <p class="text-muted m-0" style="font-size: 13px; line-height: 1.6;">The 8.5% APR used here is illustrative. Rates vary by loan type and credit profile.</p>
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

    const amountSlider = document.getElementById('amountSlider');
    const termSlider = document.getElementById('termSlider');
    
    const amountLabel = document.getElementById('amountLabel');
    const termLabel = document.getElementById('termLabel');
    const paymentCountLabel = document.getElementById('paymentCountLabel');
    
    const monthlyPrincipalElement = document.getElementById('monthlyPrincipal');
    const monthlyInterestElement = document.getElementById('monthlyInterest');
    const totalMonthlyPaymentElement = document.getElementById('totalMonthlyPayment');
    
    const summaryPrincipalElement = document.getElementById('summaryPrincipal');
    const summaryInterestElement = document.getElementById('summaryInterest');
    const summaryTotalElement = document.getElementById('summaryTotal');

    const apr = 0.085;

    function formatCurrency(val) {
        return '$' + parseFloat(val).toLocaleString('en-US', { minimumFractionDigits: 2, maximumFractionDigits: 2 });
    }

    function calculateLoan() {
        const principal = parseFloat(amountSlider.value);
        const months = parseInt(termSlider.value);
        
        amountLabel.textContent = parseFloat(amountSlider.value).toLocaleString('en-US', { style: 'currency', currency: 'USD' });
        termLabel.textContent = months + ' months';
        paymentCountLabel.textContent = months + ' monthly payments';
        
        const monthlyRate = apr / 12;
        
        const totalMonthlyPayment = (principal * monthlyRate * Math.pow(1 + monthlyRate, months)) / (Math.pow(1 + monthlyRate, months) - 1);
        const totalRepayment = totalMonthlyPayment * months;
        const totalInterest = totalRepayment - principal;
        
        const monthlyPrincipal = principal / months;
        const monthlyInterest = totalMonthlyPayment - monthlyPrincipal;

        monthlyPrincipalElement.textContent = formatCurrency(monthlyPrincipal);
        monthlyInterestElement.textContent = formatCurrency(monthlyInterest);
        totalMonthlyPaymentElement.textContent = formatCurrency(totalMonthlyPayment);

        summaryPrincipalElement.textContent = formatCurrency(principal);
        summaryInterestElement.textContent = formatCurrency(totalInterest);
        summaryTotalElement.textContent = formatCurrency(totalRepayment);
    }

    amountSlider.addEventListener('input', calculateLoan);
    termSlider.addEventListener('input', calculateLoan);

    calculateLoan();
</script>
</body>
</html>