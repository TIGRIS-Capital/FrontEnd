<?php
session_start();

$username_jnsa = $_SESSION['username'] ?? 'admin01';
$userRole_jnsa = $_SESSION['user_type'] ?? 'Admin';

require_once 'Naval_FinalsActivity3_DB.php';

function dashboard_scalar_jnsa(mysqli $conn, string $sql, $default = 0) {
    $result = $conn->query($sql);
    if (!$result) {
        return $default;
    }
    $row = $result->fetch_row();
    return $row[0] ?? $default;
}

function dashboard_money_jnsa($value) {
    return '$' . number_format((float) $value, 2);
}

$active_users_jnsa = (int) dashboard_scalar_jnsa($conn, "SELECT COUNT(*) FROM loan_member_jnsa WHERE user_status_jnsa = 'Active'");
$total_funds_disbursed_jnsa = (float) dashboard_scalar_jnsa($conn, "SELECT COALESCE(SUM(loan_amount_jnsa), 0) FROM loan_jnsa WHERE date_approved_jnsa IS NOT NULL");
$total_outstanding_balances_jnsa = (float) dashboard_scalar_jnsa($conn, "SELECT COALESCE(SUM(outstanding_balance_jnsa), 0) FROM loan_jnsa");
$system_alerts_logs_jnsa = (int) dashboard_scalar_jnsa($conn, "SELECT COUNT(*) FROM loan_logs_jnsa");

$recent_logs_query_jnsa = $conn->query("SELECT lg.log_id_jnsa, lg.action_jnsa, lg.datetime_jnsa, lm.member_name_jnsa FROM loan_logs_jnsa lg LEFT JOIN loan_member_jnsa lm ON lm.member_id_jnsa = lg.member_id_jnsa ORDER BY lg.datetime_jnsa DESC, lg.log_id_jnsa DESC LIMIT 5");
$recent_logs_rows_jnsa = [];
if ($recent_logs_query_jnsa && $recent_logs_query_jnsa->num_rows > 0) {
    while ($recent_log_row_jnsa = $recent_logs_query_jnsa->fetch_assoc()) {
        $recent_logs_rows_jnsa[] = $recent_log_row_jnsa;
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Dashboard - TIGRIS Capital</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body style="margin:0; background:#f4f6f9; color:#212529; font-family: Arial, Helvetica, sans-serif; overflow-x:hidden;">
    <div style="min-height:100vh; display:flex; background:#f4f6f9;">
        <aside style="width:240px; background:#121416; border-right:1px solid rgba(226,232,240,0.08); display:flex; flex-direction:column; justify-content:space-between;">
            <div>
                <div style="height:69px; display:flex; align-items:center; gap:12px; padding:0 18px; border-bottom:1px solid rgba(226,232,240,0.08);">
                    <div style="width:36px; height:36px; border-radius:6px; overflow:hidden;"><img src="Tigris_Logo_NoText.png" alt="logo" style="width:100%;height:100%;object-fit:cover;"></div>
                    <div style="line-height:1.05;"><div style="font-size:16px; font-weight:700; color:#e2e8f0;">Admin Portal</div><div style="font-size:11px; color:#94a3b8; margin-top:2px;">System Admin</div></div>
                </div>
                <nav style="padding:18px 12px 0 12px;">
                    <a href="admindashboard_jn.php" style="display:flex; align-items:center; gap:14px; height:48px; padding:0 16px; margin-bottom:10px; background:rgba(168,35,41,0.16); color:#ffffff; text-decoration:none; font-size:14px; font-weight:600; border-radius:8px;">Overview</a>
                    <a href="adminmembers_jnsa.php" style="display:flex; align-items:center; gap:14px; height:48px; padding:0 16px; margin-bottom:10px; color:#cbd5e1; text-decoration:none; font-size:14px;">Members</a>
                    <a href="adminloans_jnsa.php" style="display:flex; align-items:center; gap:14px; height:48px; padding:0 16px; margin-bottom:10px; color:#cbd5e1; text-decoration:none; font-size:14px;">Loans</a>
                    <a href="adminreports_jnsa.php" style="display:flex; align-items:center; gap:14px; height:48px; padding:0 16px; color:#cbd5e1; text-decoration:none; font-size:14px;">Reports</a>
                </nav>
            </div>
            <div style="padding:20px 14px; border-top:1px solid rgba(226,232,240,0.08);">
                <a href="Loan_login_jn.php" style="display:flex; align-items:center; gap:12px; color:#e2e8f0; text-decoration:none; font-size:15px; font-weight:700;">↪ Logout</a>
            </div>
        </aside>

        <main style="flex:1; min-width:0; display:flex; flex-direction:column;">
            <header style="height:69px; background:#121416; border-bottom:1px solid rgba(226,232,240,0.08); display:flex; align-items:center; justify-content:space-between; padding:0 18px 0 20px; color:#fff;">
                <div>
                    <div style="font-size:19px; font-weight:600;">Admin Dashboard</div>
                    <div style="font-size:12px; color:#cbd5e1; margin-top:6px;">Welcome back, <?php echo htmlspecialchars($username_jnsa); ?></div>
                </div>
                <div style="display:flex; align-items:center; gap:18px;">
                    <div style="width:26px; height:26px; border-radius:50%; background:#a82329; display:flex; align-items:center; justify-content:center; color:#fff;">A</div>
                    <div style="line-height:1.05; text-align:right;">
                        <div style="font-size:12px; font-weight:700; color:#ffffff;"><?php echo htmlspecialchars($username_jnsa); ?></div>
                        <div style="font-size:11px; color:#cbd5e1; margin-top:4px;"><?php echo htmlspecialchars($userRole_jnsa); ?></div>
                    </div>
                </div>
            </header>

            <section style="padding:28px 18px 18px 18px; background:#f4f6f9; flex:1;">
                <div style="margin-bottom:18px;">
                    <div style="font-size:26px; font-weight:600; color:#212529; letter-spacing:-0.2px; margin-bottom:9px;">Admin Overview</div>
                    <div style="font-size:14px; color:#6b7280;">Live system metrics, shortcuts, and the latest audit trail activity.</div>
                </div>

                <div class="row g-3" style="margin:0 0 24px 0;">
                    <div class="col-12 col-md-3 px-2">
                        <div style="background:#ffffff; border:1px solid #e5e7eb; border-radius:10px; box-shadow:0 2px 10px rgba(15,23,42,0.06); padding:16px; min-height:140px; display:flex; flex-direction:column; justify-content:space-between;">
                            <div>
                                <div style="font-size:13px; color:#495057; font-weight:600;">Active System Users</div>
                                <div style="font-size:26px; font-weight:700; color:#212529; margin-top:8px;"><?php echo intval($active_users_jnsa); ?></div>
                            </div>
                            <div style="font-size:12px; color:#6b7280;">Members marked active in the live schema.</div>
                        </div>
                    </div>
                    <div class="col-12 col-md-3 px-2">
                        <div style="background:#ffffff; border:1px solid #e5e7eb; border-radius:10px; box-shadow:0 2px 10px rgba(15,23,42,0.06); padding:16px; min-height:140px; display:flex; flex-direction:column; justify-content:space-between;">
                            <div>
                                <div style="font-size:13px; color:#495057; font-weight:600;">Total Funds Disbursed</div>
                                <div style="font-size:26px; font-weight:700; color:#212529; margin-top:8px;"><?php echo htmlspecialchars(dashboard_money_jnsa($total_funds_disbursed_jnsa)); ?></div>
                            </div>
                            <div style="font-size:12px; color:#6b7280;">Approved loan principal total.</div>
                        </div>
                    </div>
                    <div class="col-12 col-md-3 px-2">
                        <div style="background:#ffffff; border:1px solid #e5e7eb; border-radius:10px; box-shadow:0 2px 10px rgba(15,23,42,0.06); padding:16px; min-height:140px; display:flex; flex-direction:column; justify-content:space-between;">
                            <div>
                                <div style="font-size:13px; color:#495057; font-weight:600;">Total Active Outstanding Balances</div>
                                <div style="font-size:26px; font-weight:700; color:#212529; margin-top:8px;"><?php echo htmlspecialchars(dashboard_money_jnsa($total_outstanding_balances_jnsa)); ?></div>
                            </div>
                            <div style="font-size:12px; color:#6b7280;">Remaining balances across all loans.</div>
                        </div>
                    </div>
                    <div class="col-12 col-md-3 px-2">
                        <div style="background:#ffffff; border:1px solid #e5e7eb; border-radius:10px; box-shadow:0 2px 10px rgba(15,23,42,0.06); padding:16px; min-height:140px; display:flex; flex-direction:column; justify-content:space-between;">
                            <div>
                                <div style="font-size:13px; color:#495057; font-weight:600;">System Alerts / Logs Count</div>
                                <div style="font-size:26px; font-weight:700; color:#212529; margin-top:8px;"><?php echo intval($system_alerts_logs_jnsa); ?></div>
                            </div>
                            <div style="font-size:12px; color:#6b7280;">Audit activity captured in loan logs.</div>
                        </div>
                    </div>
                </div>

                <div class="row g-3">
                    <div class="col-12 col-lg-5">
                        <div style="background:#ffffff; border:1px solid #e5e7eb; border-radius:12px; box-shadow:0 10px 30px rgba(15,23,42,0.08); padding:18px; height:100%;">
                            <div style="font-size:15px; font-weight:700; color:#212529; margin-bottom:14px;">Quick Actions</div>
                            <div style="display:flex; flex-direction:column; gap:10px;">
                                <a href="adminmembers_jnsa.php" style="display:flex; align-items:center; justify-content:center; height:46px; border-radius:8px; background:#121416; color:#ffffff; text-decoration:none; font-weight:700;">Manage Users</a>
                                <a href="adminloans_jnsa.php" style="display:flex; align-items:center; justify-content:center; height:46px; border-radius:8px; background:#a82329; color:#ffffff; text-decoration:none; font-weight:700;">Review Loans</a>
                                <a href="adminreports_jnsa.php" style="display:flex; align-items:center; justify-content:center; height:46px; border-radius:8px; background:#f8f9fa; color:#212529; text-decoration:none; font-weight:700; border:1px solid #e5e7eb;">Open Reports</a>
                            </div>
                        </div>
                    </div>

                    <div class="col-12 col-lg-7">
                        <div style="background:#ffffff; border:1px solid #e5e7eb; border-radius:12px; box-shadow:0 10px 30px rgba(15,23,42,0.08); padding:18px; height:100%; overflow-x:auto;">
                            <div style="display:flex; align-items:center; justify-content:space-between; margin-bottom:14px; gap:12px;">
                                <div style="font-size:15px; font-weight:700; color:#212529;">Recent Critical Logs</div>
                                <div style="font-size:12px; color:#6b7280;">Latest 5 actions</div>
                            </div>
                            <table style="width:100%; border-collapse:collapse; min-width:620px;">
                                <thead>
                                    <tr style="text-align:left; color:#495057; font-size:12px; letter-spacing:0.2px; text-transform:uppercase;">
                                        <th style="padding:10px 8px; border-bottom:1px solid #e5e7eb;">Log ID</th>
                                        <th style="padding:10px 8px; border-bottom:1px solid #e5e7eb;">Actor</th>
                                        <th style="padding:10px 8px; border-bottom:1px solid #e5e7eb;">Action</th>
                                        <th style="padding:10px 8px; border-bottom:1px solid #e5e7eb;">Timestamp</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php if (empty($recent_logs_rows_jnsa)): ?>
                                        <tr>
                                            <td colspan="4" style="padding:16px 8px; color:#6b7280;">No recent logs available.</td>
                                        </tr>
                                    <?php else: ?>
                                        <?php foreach ($recent_logs_rows_jnsa as $recent_log_row_jnsa): ?>
                                            <tr style="border-bottom:1px solid #f1f3f5; color:#212529;">
                                                <td style="padding:12px 8px;">#<?php echo intval($recent_log_row_jnsa['log_id_jnsa']); ?></td>
                                                <td style="padding:12px 8px;"><?php echo htmlspecialchars($recent_log_row_jnsa['member_name_jnsa'] ?? 'Unknown Actor'); ?></td>
                                                <td style="padding:12px 8px;"><?php echo htmlspecialchars($recent_log_row_jnsa['action_jnsa']); ?></td>
                                                <td style="padding:12px 8px;"><?php echo htmlspecialchars($recent_log_row_jnsa['datetime_jnsa']); ?></td>
                                            </tr>
                                        <?php endforeach; ?>
                                    <?php endif; ?>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </section>
        </main>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
