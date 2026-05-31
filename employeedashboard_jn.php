<?php
session_start();

// Normalize the employee session before running any queries.
$username = $_SESSION['username'] ?? 'officer01';
$userRole = $_SESSION['user_type'] ?? 'Employee';
if (!isset($employee_name_jnsa)) {
    $employee_name_jnsa = $_SESSION['member_name_jnsa'] ?? ($_SESSION['username'] ?? 'Employee');
}
$current_page_jnsa = basename($_SERVER['PHP_SELF']);
$overview_nav_active_jnsa = ($current_page_jnsa === 'employeedashboard_jn.php');
$loans_nav_active_jnsa = ($current_page_jnsa === 'employeeloans_jnsa.php');
$payments_nav_active_jnsa = ($current_page_jnsa === 'employeepayments_jnsa.php');
$loan_types_nav_active_jnsa = ($current_page_jnsa === 'employeeloantypes_jnsa.php');
require_once "Naval_FinalsActivity3_DB.php";

// Shared scalar helper used by the employee metrics.
function dashboard_scalar(mysqli $conn, string $sql, $default = 0) {
    $result = $conn->query($sql);
    if (!$result) {
        return $default;
    }
    $row = $result->fetch_row();
    return $row[0] ?? $default;
}

// Format values as currency for the KPI cards.
function dashboard_money($value) {
    return '$' . number_format((float)$value, 2);
}

// Gather the approval and workload figures displayed in the cards.
$pendingApprovals = (int) dashboard_scalar($conn, "SELECT COUNT(*) FROM loan_jnsa WHERE date_approved_jnsa IS NULL");
$approvedToday = (int) dashboard_scalar($conn, "SELECT COUNT(*) FROM loan_jnsa WHERE date_approved_jnsa IS NOT NULL AND DATE(date_approved_jnsa) = CURDATE()");
$totalValuePending = (float) dashboard_scalar($conn, "SELECT COALESCE(SUM(loan_amount_jnsa), 0) FROM loan_jnsa WHERE date_approved_jnsa IS NULL");
$activeMembers = (int) dashboard_scalar($conn, "SELECT COUNT(*) FROM loan_member_jnsa WHERE user_status_jnsa = 'Active'");

// Build the weekly status distribution used by the progress bars.
$approvedLoans = (int) dashboard_scalar($conn, "SELECT COUNT(*) FROM loan_jnsa WHERE date_approved_jnsa IS NOT NULL");
$rejectedLoans = (int) dashboard_scalar($conn, "SELECT COUNT(*) FROM loan_jnsa WHERE date_approved_jnsa IS NULL AND date_disbursed_jnsa IS NULL AND DATE(date_applied_jnsa) < DATE_SUB(CURDATE(), INTERVAL 30 DAY)");
$underReviewLoans = (int) dashboard_scalar($conn, "SELECT COUNT(*) FROM loan_jnsa WHERE date_approved_jnsa IS NULL AND DATE(date_applied_jnsa) >= DATE_SUB(CURDATE(), INTERVAL 30 DAY)");
$totalLoans = max(1, (int) dashboard_scalar($conn, "SELECT COUNT(*) FROM loan_jnsa"));

$approvedPct = min(100, (int) round(($approvedLoans / $totalLoans) * 100));
$rejectedPct = min(100, (int) round(($rejectedLoans / $totalLoans) * 100));
$underReviewPct = min(100, (int) round(($underReviewLoans / $totalLoans) * 100));
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Loan Management System - Employee Panel</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body style="margin:0; background:#f4f6f9; color:#1f2937; font-family: Arial, Helvetica, sans-serif; overflow-x:hidden;">
    <div style="min-height:100vh; display:flex; background:#f4f5f7;">
        
    <!-- Sidebar -->
        <aside style="width:240px; background:#121416; border-right:1px solid rgba(226,232,240,0.08); box-shadow: 0 0 0 1px rgba(0,0,0,0.08); display:flex; flex-direction:column; justify-content:space-between;">
            <div>
                    <div style="height:69px; display:flex; align-items:center; gap:12px; padding:0 18px; border-bottom:1px solid rgba(226,232,240,0.08);">
                    <div style="width:36px; height:36px; border-radius:6px; background:#121416; display:flex; align-items:center; justify-content:center; color:#fff; flex:0 0 auto; box-shadow:0 0 0 1px rgba(255,255,255,0.04); overflow:hidden;">
                        <img src="Tigris_Logo_NoText.png" alt="Tigris Capital Logo" style="width:100%; height:100%; object-fit:cover;">
                    </div>
                    <div style="line-height:1.05;">
                        <div style="font-size:16px; font-weight:700; color:#e2e8f0;">Employee Portal</div>
                        <div style="font-size:11px; color:#94a3b8; margin-top:2px;">Loan Officer</div>
                    </div>
                </div>

                <!-- navbar to other page -->
                <nav style="padding:18px 12px 0 12px;">
                    <a href="employeedashboard_jn.php" style="display:flex; align-items:center; gap:14px; height:48px; border-radius:8px; padding:0 16px; margin-bottom:10px; <?php echo $overview_nav_active_jnsa ? 'background:rgba(168,35,41,0.16); color:#ffffff; font-weight:600; border:1px solid rgba(168,35,41,0.28); box-shadow:inset 0 1px 0 rgba(255,255,255,0.03);' : 'color:#cbd5e1; font-weight:500;'; ?> text-decoration:none; font-size:14px;">Overview</a>
                    <a href="employeeloans_jnsa.php" style="display:flex; align-items:center; gap:14px; height:48px; border-radius:8px; padding:0 16px; margin-bottom:10px; <?php echo $loans_nav_active_jnsa ? 'background:rgba(168,35,41,0.16); color:#ffffff; font-weight:600; border:1px solid rgba(168,35,41,0.28); box-shadow:inset 0 1px 0 rgba(255,255,255,0.03);' : 'color:#cbd5e1; font-weight:500;'; ?> text-decoration:none; font-size:14px;">Loans</a>
                    <a href="employeepayments_jnsa.php" style="display:flex; align-items:center; gap:14px; height:48px; border-radius:8px; padding:0 16px; margin-bottom:10px; <?php echo $payments_nav_active_jnsa ? 'background:rgba(168,35,41,0.16); color:#ffffff; font-weight:600; border:1px solid rgba(168,35,41,0.28); box-shadow:inset 0 1px 0 rgba(255,255,255,0.03);' : 'color:#cbd5e1; font-weight:500;'; ?> text-decoration:none; font-size:14px;">Payments</a>
                    <a href="employeeloantypes_jnsa.php" style="display:flex; align-items:center; gap:14px; height:48px; border-radius:8px; padding:0 16px; <?php echo $loan_types_nav_active_jnsa ? 'background:rgba(168,35,41,0.16); color:#ffffff; font-weight:600; border:1px solid rgba(168,35,41,0.28); box-shadow:inset 0 1px 0 rgba(255,255,255,0.03);' : 'color:#cbd5e1; font-weight:500;'; ?> text-decoration:none; font-size:14px;">Loan Types</a>
                </nav>
            </div>

            <div style="padding:20px 14px; border-top:1px solid rgba(226,232,240,0.08);">
                <a href="Loan_login_jn.php" style="display:flex; align-items:center; gap:12px; color:#e2e8f0; text-decoration:none; font-size:15px; font-weight:700; padding:12px 14px; border-radius:8px; background:rgba(255,255,255,0.02); border:1px solid rgba(226,232,240,0.08);">
                    <span style="font-size:18px; line-height:1; font-weight:700;">↪</span>
                    Logout
                </a>
            </div>
        </aside>

        <main style="flex:1; min-width:0; display:flex; flex-direction:column;">
            <!-- Header -->
            <header style="height:69px; background:#121416; border-bottom:1px solid rgba(226,232,240,0.08); display:flex; align-items:center; justify-content:space-between; padding:0 18px 0 20px;">
				<div>
					<div style="font-size:19px; font-weight:600; color:#ffffff; letter-spacing:-0.2px;">Loan Management System - Employee Panel</div>
					<div style="font-size:12px; color:#cbd5e1; margin-top:6px;">Welcome back, <?php echo htmlspecialchars($employee_name_jnsa); ?></div>
				</div>

				<div style="display:flex; align-items:center; gap:18px;">
					<div style="display:flex; align-items:center; gap:10px;">
						<div style="width:36px; height:36px; border-radius:50%; background:#a82329; display:flex; align-items:center; justify-content:center; color:#fff; flex:0 0 auto; box-shadow:0 0 0 1px rgba(255,255,255,0.06);">
							<span style="font-size:15px; line-height:1; font-weight:700;"><?php echo strtoupper(substr($employee_name_jnsa, 0, 1)); ?></span>
						</div>
						<div style="line-height:1.05;">
							<div style="font-size:12px; font-weight:700; color:#ffffff;"><?php echo htmlspecialchars($employee_name_jnsa); ?></div>
							<div style="font-size:11px; color:#cbd5e1; margin-top:4px;">Employee</div>
						</div>
					</div>
				</div>
			</header>

            <!-- Main SDashboard -->
            <section style="padding:28px 18px 18px 18px; background:#f4f6f9; flex:1;">
                <div style="margin-bottom:18px;">
                    <div style="font-size:26px; font-weight:600; color:#1f2937; letter-spacing:-0.2px; margin-bottom:9px;">Employee Dashboard</div>
                    <div style="font-size:14px; color:#6b7280;">Welcome back! Here&apos;s your loan approval queue.</div>
                </div>

                <div class="row gx-4 gy-4" style="margin:0 0 24px 0;">
                    <div class="col-12 col-md-6 col-xl-3 px-2">
                        <div style="height:128px; background:#fff; border:1px solid #dbe0e6; border-radius:10px; box-shadow:0 2px 10px rgba(15,23,42,0.08); padding:18px 20px; display:flex; justify-content:space-between; align-items:flex-start;">
                            <div style="display:flex; flex-direction:column; gap:8px;">
                                <div style="font-size:14px; color:#54708a; font-weight:500;">Pending Approvals</div>
                                <div style="font-size:28px; font-weight:700; color:#111827; line-height:1;"><?php echo intval($pendingApprovals); ?></div>
                            </div>
                            <div style="width:44px; height:44px; border-radius:10px; background:rgba(249,115,22,0.08); display:flex; align-items:center; justify-content:center; color:#f97316; flex:0 0 auto;">
                                <span style="font-size:20px; line-height:1; font-weight:700;">⏰</span>
                            </div>
                        </div>
                    </div>

                    <div class="col-12 col-md-6 col-xl-3 px-2">
                        <div style="height:128px; background:#fff; border:1px solid #dbe0e6; border-radius:10px; box-shadow:0 2px 10px rgba(15,23,42,0.08); padding:18px 20px; display:flex; justify-content:space-between; align-items:flex-start;">
                            <div style="display:flex; flex-direction:column; gap:8px;">
                                <div style="font-size:14px; color:#54708a; font-weight:500;">Approved Today</div>
                                <div style="font-size:28px; font-weight:700; color:#111827; line-height:1;"><?php echo intval($approvedToday); ?></div>
                            </div>
                            <div style="width:44px; height:44px; border-radius:10px; background:rgba(16,185,129,0.08); display:flex; align-items:center; justify-content:center; color:#10b981; flex:0 0 auto;">
                                <span style="font-size:20px; line-height:1; font-weight:700;">✓</span>
                            </div>
                        </div>
                    </div>

                    <div class="col-12 col-md-6 col-xl-3 px-2">
                        <div style="height:128px; background:#fff; border:1px solid #dbe0e6; border-radius:10px; box-shadow:0 2px 10px rgba(15,23,42,0.08); padding:18px 20px; display:flex; justify-content:space-between; align-items:flex-start;">
                            <div style="display:flex; flex-direction:column; gap:8px;">
                                <div style="font-size:14px; color:#54708a; font-weight:500;">Total Value Pending</div>
                                <div style="font-size:28px; font-weight:700; color:#111827; line-height:1;"><?php echo htmlspecialchars(dashboard_money($totalValuePending)); ?></div>
                            </div>
                            <div style="width:44px; height:44px; border-radius:10px; background:rgba(168,35,41,0.08); display:flex; align-items:center; justify-content:center; color:#a82329; flex:0 0 auto;">
                                <span style="font-size:20px; line-height:1; font-weight:700;">$</span>
                            </div>
                        </div>
                    </div>

                    <div class="col-12 col-md-6 col-xl-3 px-2">
                        <div style="height:128px; background:#fff; border:1px solid #dbe0e6; border-radius:10px; box-shadow:0 2px 10px rgba(15,23,42,0.08); padding:18px 20px; display:flex; justify-content:space-between; align-items:flex-start;">
                            <div style="display:flex; flex-direction:column; gap:8px;">
                                <div style="font-size:14px; color:#54708a; font-weight:500;">Active Members</div>
                                <div style="font-size:28px; font-weight:700; color:#111827; line-height:1;"><?php echo number_format($activeMembers); ?></div>
                            </div>
                            <div style="width:44px; height:44px; border-radius:10px; background:rgba(107,114,128,0.08); display:flex; align-items:center; justify-content:center; color:#6b7280; flex:0 0 auto;">
                                <span style="font-size:18px; line-height:1; font-weight:700;">👥</span>
                            </div>
                        </div>
                    </div>
                </div>


                <div class="row g-3" style="margin:24px 0 0 0;">
                    <div class="col-md-6" style="padding-left:8px; padding-right:8px;">
                        <div style="background:#fff; border:1px solid #e5e7eb; border-radius:6px; box-shadow:0 1px 4px rgba(16,24,40,.04); min-height:176px; padding:16px;">
                            <div style="font-size:14px; font-weight:700; color:#1f2937; margin-bottom:14px;">Quick Actions</div>
                            <div style="display:flex; flex-direction:column; gap:10px;">
                                <a href="loans_list_jn.php" style="display:flex; height:38px; align-items:center; gap:10px; padding:0 12px; color:#374151; font-size:13px; font-weight:600; background:#fff; border:1px solid #d1d5db; border-radius:6px; text-decoration:none;">
                                    <span style="width:16px; height:16px; border-radius:3px; border:1px solid #a82329; display:inline-flex; align-items:center; justify-content:center; font-size:9px; line-height:1; color:#a82329; font-weight:700;">L</span>
                                    View All Loans
                                </a>
                                <a href="reports_jn.php" style="display:flex; height:38px; align-items:center; gap:10px; padding:0 12px; color:#374151; font-size:13px; font-weight:600; background:#fff; border:1px solid #d1d5db; border-radius:6px; text-decoration:none;">
                                    <span style="width:16px; height:16px; border-radius:3px; border:1px solid #a82329; display:inline-flex; align-items:center; justify-content:center; font-size:9px; line-height:1; color:#a82329; font-weight:700;">R</span>
                                    View Reports
                                </a>
                            </div>
                        </div>
                    </div>

                    <div class="col-md-6" style="padding-left:8px; padding-right:8px;">
                        <div style="background:#fff; border:1px solid #e5e7eb; border-radius:6px; box-shadow:0 1px 4px rgba(16,24,40,.04); min-height:176px; padding:16px;">
                            <div style="font-size:14px; font-weight:700; color:#1f2937; margin-bottom:18px;">This Week&apos;s Activity</div>
                            <div style="margin-bottom:16px;">
                                <div style="display:flex; justify-content:space-between; font-size:13px; color:#6b7280; margin-bottom:8px;"><span>Approved</span><span><?php echo intval($approvedLoans); ?> loans</span></div>
                                <div style="height:6px; background:#e5e7eb; border-radius:999px; overflow:hidden;"><div style="width:<?php echo $approvedPct; ?>%; height:100%; background:#a82329; border-radius:999px;"></div></div>
                            </div>
                            <div style="margin-bottom:16px;">
                                <div style="display:flex; justify-content:space-between; font-size:13px; color:#6b7280; margin-bottom:8px;"><span>Rejected</span><span><?php echo intval($rejectedLoans); ?> loans</span></div>
                                <div style="height:6px; background:#e5e7eb; border-radius:999px; overflow:hidden;"><div style="width:<?php echo $rejectedPct; ?>%; height:100%; background:#4b5563; border-radius:999px;"></div></div>
                            </div>
                            <div>
                                <div style="display:flex; justify-content:space-between; font-size:13px; color:#6b7280; margin-bottom:8px;"><span>Under Review</span><span><?php echo intval($underReviewLoans); ?> loans</span></div>
                                <div style="height:6px; background:#e5e7eb; border-radius:999px; overflow:hidden;"><div style="width:<?php echo $underReviewPct; ?>%; height:100%; background:#a82329; opacity:0.32; border-radius:999px;"></div></div>
                            </div>
                        </div>
                    </div>

                </div>
            </section>
        </main>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>