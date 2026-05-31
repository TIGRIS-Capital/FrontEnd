<?php
session_start();
if (!isset($_SESSION['member_id_jnsa']) && isset($_SESSION['member_id'])) {
    $_SESSION['member_id_jnsa'] = (int) $_SESSION['member_id'];
}

if (!isset($_SESSION['member_id_jnsa']) || !isset($_SESSION['user_type']) || $_SESSION['user_type'] !== 'member') {
    header("Location: Loan_login_jn.php");
    exit();
}

$member_id_jnsa = (int) $_SESSION['member_id_jnsa'];
$username_jnsa = $_SESSION['username'] ?? null;
require_once "Naval_FinalsActivity3_DB.php";


function dashboard_scalar(mysqli $conn, string $sql, $default = 0) {
    $result = $conn->query($sql);
    if (!$result) return $default;
    $row = $result->fetch_row();
    return $row[0] ?? $default;
}

function dashboard_money($value) {
    return '$' . number_format((float)$value, 2);
}

$member_q_jnsa = $conn->query("SELECT member_id_jnsa, member_name_jnsa, member_img_jnsa, user_status_jnsa, username_jnsa FROM loan_member_jnsa WHERE member_id_jnsa = '$member_id_jnsa' LIMIT 1");
$member_jnsa = $member_q_jnsa ? $member_q_jnsa->fetch_assoc() : null;
if (!$member_jnsa) {
    header("Location: Loan_login_jn.php");
    exit();
}

$member_name_jnsa = $_SESSION['member_name_jnsa'] ?? $member_jnsa['member_name_jnsa'];

$my_total_loans_jnsa = (int) dashboard_scalar($conn, "SELECT COUNT(*) FROM loan_jnsa WHERE borrower_id_jnsa = '$member_id_jnsa'");
$my_active_loans_jnsa = (int) dashboard_scalar($conn, "SELECT COUNT(*) FROM loan_jnsa WHERE borrower_id_jnsa = '$member_id_jnsa' AND date_approved_jnsa IS NOT NULL AND COALESCE(outstanding_balance_jnsa,0) > 0");
$my_pending_loans_jnsa = (int) dashboard_scalar($conn, "SELECT COUNT(*) FROM loan_jnsa WHERE borrower_id_jnsa = '$member_id_jnsa' AND date_approved_jnsa IS NULL");
$outstanding_balance_jnsa = (float) dashboard_scalar($conn, "SELECT COALESCE(SUM(outstanding_balance_jnsa),0) FROM loan_jnsa WHERE borrower_id_jnsa = '$member_id_jnsa'");

$latest_payment_jnsa = null;
$latest_payment_query_jnsa = $conn->query("SELECT payment_amount_jnsa, payment_date_jnsa FROM payment_jnsa WHERE loan_id_jnsa IN (SELECT loan_id_jnsa FROM loan_jnsa WHERE borrower_id_jnsa = '$member_id_jnsa') ORDER BY payment_date_jnsa DESC LIMIT 1");
if ($latest_payment_query_jnsa && $latest_payment_query_jnsa->num_rows > 0) {
    $latest_payment_jnsa = $latest_payment_query_jnsa->fetch_assoc();
}

$next_due_date_jnsa = dashboard_scalar($conn, "SELECT MIN(DATE_ADD(date_disbursed_jnsa, INTERVAL 1 MONTH)) FROM loan_jnsa WHERE borrower_id_jnsa = '$member_id_jnsa' AND outstanding_balance_jnsa > 0 AND date_disbursed_jnsa IS NOT NULL");
$next_due_date_display_jnsa = $next_due_date_jnsa ? htmlspecialchars($next_due_date_jnsa) : 'No estimated due date yet';

$loan_rows_jnsa = [];
$loan_rows_query_jnsa = $conn->query("SELECT loan_id_jnsa, loan_amount_jnsa, loan_term_jnsa, date_applied_jnsa, date_approved_jnsa, date_disbursed_jnsa, outstanding_balance_jnsa FROM loan_jnsa WHERE borrower_id_jnsa = '$member_id_jnsa' ORDER BY date_applied_jnsa DESC");
if ($loan_rows_query_jnsa && $loan_rows_query_jnsa->num_rows > 0) {
    while ($loan_row_jnsa = $loan_rows_query_jnsa->fetch_assoc()) {
        $loan_rows_jnsa[] = $loan_row_jnsa;
    }
}

?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Member Dashboard</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body style="margin:0; background:#f4f6f9; color:#212529; font-family: Arial, Helvetica, sans-serif; overflow-x:hidden;">
    <div style="min-height:100vh; display:flex; background:#f4f6f9;">
        <aside style="width:240px; background:#121416; border-right:1px solid rgba(226,232,240,0.08); box-shadow: 0 0 0 1px rgba(0,0,0,0.08); display:flex; flex-direction:column; justify-content:space-between;">
            <div>
                <div style="height:69px; display:flex; align-items:center; gap:12px; padding:0 18px; border-bottom:1px solid rgba(226,232,240,0.08);">
                    <div style="width:36px; height:36px; border-radius:6px; background:#121416; display:flex; align-items:center; justify-content:center; color:#fff; flex:0 0 auto; box-shadow:0 0 0 1px rgba(255,255,255,0.04); overflow:hidden;">
                        <img src="Tigris_Logo_NoText.png" alt="Tigris Capital Logo" style="width:100%; height:100%; object-fit:cover;">
                    </div>
                    <div style="line-height:1.05;">
                        <div style="font-size:16px; font-weight:700; color:#e2e8f0;">Member Portal</div>
                        <div style="font-size:11px; color:#94a3b8; margin-top:2px;">Account Holder</div>
                    </div>
                </div>

                <nav style="padding:18px 12px 0 12px;">
                    <a href="memberdashboard_jnsa.php" style="display:flex; align-items:center; gap:14px; height:48px; border-radius:8px; padding:0 16px; margin-bottom:10px; background:rgba(168,35,41,0.16); color:#ffffff; text-decoration:none; font-size:14px; font-weight:600; border:1px solid rgba(168,35,41,0.28); box-shadow:inset 0 1px 0 rgba(255,255,255,0.03);">Dashboard Home</a>
                    <a href="memberloanform_jnsa.php" style="display:flex; align-items:center; gap:14px; height:48px; border-radius:8px; padding:0 16px; margin-bottom:10px; color:#cbd5e1; text-decoration:none; font-size:14px; font-weight:500;">Apply for a Loan</a>
                    <a href="memberloanstatus_jnsa.php" style="display:flex; align-items:center; gap:14px; height:48px; border-radius:8px; padding:0 16px; color:#cbd5e1; text-decoration:none; font-size:14px; font-weight:500;">View Loan Status</a>
                    <a href="memberpayment_jnsa.php" style="display:flex; align-items:center; gap:14px; height:48px; border-radius:8px; padding:0 16px; margin-top:10px; color:#cbd5e1; text-decoration:none; font-size:14px; font-weight:500;">Make Payment</a>
                </nav>
            </div>

            <div style="padding:20px 14px; border-top:1px solid rgba(226,232,240,0.08);">
                <a href="Loan_login_jn.php" style="display:flex; align-items:center; gap:12px; color:#e2e8f0; text-decoration:none; font-size:15px; font-weight:700; padding:12px 14px; border-radius:8px; background:rgba(255,255,255,0.02); border:1px solid rgba(226,232,240,0.08);">↪ Logout</a>
            </div>
        </aside>

        <main style="flex:1; min-width:0; display:flex; flex-direction:column;">
            <header style="height:69px; background:#121416; border-bottom:1px solid rgba(226,232,240,0.08); display:flex; align-items:center; justify-content:space-between; padding:0 18px 0 20px;">
                <div>
                    <div style="font-size:19px; font-weight:600; color:#ffffff; letter-spacing:-0.2px;">Loan Management System - Member</div>
                    <div style="font-size:12px; color:#cbd5e1; margin-top:6px;">Hello, <?php echo htmlspecialchars($member_name_jnsa); ?></div>
                </div>

                <div style="display:flex; align-items:center; gap:18px;">
                    <div style="display:flex; align-items:center; gap:10px;">
                        <div style="width:36px; height:36px; border-radius:50%; overflow:hidden; background:#fff; display:flex; align-items:center; justify-content:center;">
                            <?php if(!empty($member_jnsa['member_img_jnsa'])): ?>
                                <img src="<?php echo htmlspecialchars($member_jnsa['member_img_jnsa']); ?>" alt="avatar" style="width:100%; height:100%; object-fit:cover;">
                            <?php else: ?>
                                <span style="font-weight:700; color:#121416;"><?php echo strtoupper(substr($member_name_jnsa,0,1)); ?></span>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
            </header>

            <section style="padding:28px 18px 18px 18px; background:#f4f6f9; flex:1;">
                <div style="margin-bottom:18px;">
                    <div style="font-size:26px; font-weight:600; color:#212529; letter-spacing:-0.2px; margin-bottom:9px;">Member Dashboard</div>
                    <div style="font-size:14px; color:#495057;">Welcome back, <?php echo htmlspecialchars($member_name_jnsa); ?>.</div>
                </div>

                <div class="row gx-4 gy-4" style="margin:0 0 24px 0;">
                    <div class="col-12 col-md-6 col-xl-3 px-2">
                        <div style="height:110px; background:#ffffff; border:1px solid #e5e7eb; border-radius:10px; box-shadow:0 2px 10px rgba(15,23,42,0.06); padding:14px 16px; display:flex; justify-content:space-between; align-items:flex-start;">
                            <div>
                                <div style="font-size:13px; color:#212529; font-weight:500;">My Loans</div>
                                <div style="font-size:22px; font-weight:700; color:#212529; margin-top:6px;"><?php echo intval($my_total_loans_jnsa); ?></div>
                            </div>
                            <div style="width:42px; height:42px; border-radius:10px; background:rgba(168,35,41,0.08); display:flex; align-items:center; justify-content:center; color:#a82329; flex:0 0 auto;">💼</div>
                        </div>
                    </div>

                    <div class="col-12 col-md-6 col-xl-3 px-2">
                        <div style="height:110px; background:#ffffff; border:1px solid #e5e7eb; border-radius:10px; box-shadow:0 2px 10px rgba(15,23,42,0.06); padding:14px 16px; display:flex; justify-content:space-between; align-items:flex-start;">
                            <div>
                                <div style="font-size:13px; color:#212529; font-weight:500;">Active Loans</div>
                                <div style="font-size:22px; font-weight:700; color:#212529; margin-top:6px;"><?php echo intval($my_active_loans_jnsa); ?></div>
                            </div>
                            <div style="width:42px; height:42px; border-radius:10px; background:rgba(16,185,129,0.08); display:flex; align-items:center; justify-content:center; color:#10b981; flex:0 0 auto;">✓</div>
                        </div>
                    </div>

                    <div class="col-12 col-md-6 col-xl-3 px-2">
                        <div style="height:110px; background:#ffffff; border:1px solid #e5e7eb; border-radius:10px; box-shadow:0 2px 10px rgba(15,23,42,0.06); padding:14px 16px; display:flex; justify-content:space-between; align-items:flex-start;">
                            <div>
                                <div style="font-size:13px; color:#212529; font-weight:500;">Total Outstanding Balance</div>
                                <div style="font-size:22px; font-weight:700; color:#212529; margin-top:6px;"><?php echo htmlspecialchars(dashboard_money($outstanding_balance_jnsa)); ?></div>
                            </div>
                            <div style="width:42px; height:42px; border-radius:10px; background:rgba(168,35,41,0.08); display:flex; align-items:center; justify-content:center; color:#a82329; flex:0 0 auto;">$</div>
                        </div>
                    </div>

                    <div class="col-12 col-md-6 col-xl-3 px-2">
                        <div style="height:110px; background:#ffffff; border:1px solid #e5e7eb; border-radius:10px; box-shadow:0 2px 10px rgba(15,23,42,0.06); padding:14px 16px; display:flex; justify-content:space-between; align-items:flex-start;">
                            <div>
                                <div style="font-size:13px; color:#212529; font-weight:500;">Recent Payment Tracker</div>
                                <div style="font-size:16px; font-weight:700; color:#212529; margin-top:6px;">
                                    <?php if ($latest_payment_jnsa): ?>
                                        <?php echo htmlspecialchars(dashboard_money($latest_payment_jnsa['payment_amount_jnsa'])); ?>
                                    <?php else: ?>
                                        No payments yet
                                    <?php endif; ?>
                                </div>
                                <div style="font-size:12px; color:#495057; margin-top:4px;">
                                    <?php if ($latest_payment_jnsa): ?>
                                        Last payment on <?php echo htmlspecialchars($latest_payment_jnsa['payment_date_jnsa']); ?>
                                    <?php else: ?>
                                        Waiting for first payment
                                    <?php endif; ?>
                                </div>
                            </div>
                            <div style="width:42px; height:42px; border-radius:10px; background:rgba(249,115,22,0.08); display:flex; align-items:center; justify-content:center; color:#f97316; flex:0 0 auto;">⏳</div>
                        </div>
                    </div>
                </div>

                <div class="row g-3" style="margin:24px 0 0 0;">
                    <div class="col-md-6" style="padding-left:8px; padding-right:8px;">
                        <div style="background:#ffffff; border:1px solid #e5e7eb; border-radius:6px; box-shadow:0 1px 4px rgba(16,24,40,.04); min-height:220px; padding:16px;">
                            <div style="font-size:14px; font-weight:700; color:#212529; margin-bottom:14px;">Next Estimated Due Date</div>
                            <div style="font-size:24px; font-weight:700; color:#212529; margin-bottom:8px;"><?php echo $next_due_date_display_jnsa; ?></div>
                            <div style="color:#495057; font-size:13px;">Calculated as one month after the earliest disbursed active loan with an outstanding balance.</div>
                        </div>
                    </div>

                    <div class="col-md-6" style="padding-left:8px; padding-right:8px;">
                        <div style="background:#ffffff; border:1px solid #e5e7eb; border-radius:6px; box-shadow:0 1px 4px rgba(16,24,40,.04); min-height:220px; padding:16px;">
                            <div style="font-size:14px; font-weight:700; color:#212529; margin-bottom:14px;">My Loan Summary</div>
                            <?php if(empty($loan_rows_jnsa)): ?>
                                <div style="color:#495057;">You have no loans yet. <a href="loans_list_jn.php" style="color:#a82329; text-decoration:none;">Apply for a loan</a></div>
                            <?php else: ?>
                                <table style="width:100%; border-collapse:collapse; font-size:13px;">
                                    <thead>
                                        <tr style="text-align:left; color:#495057; font-size:12px;"><th>Loan</th><th>Amount</th><th>Term</th><th>Applied</th><th>Disbursed</th><th>Balance</th><th>Stage</th></tr>
                                    </thead>
                                    <tbody>
                                    <?php foreach($loan_rows_jnsa as $loan_row_jnsa): ?>
                                        <tr style="border-top:1px solid #f1f5f9; color:#212529;">
                                            <td style="padding:8px 6px;">#<?php echo intval($loan_row_jnsa['loan_id_jnsa']); ?></td>
                                            <td style="padding:8px 6px;"><?php echo htmlspecialchars(dashboard_money($loan_row_jnsa['loan_amount_jnsa'])); ?></td>
                                            <td style="padding:8px 6px;"><?php echo htmlspecialchars($loan_row_jnsa['loan_term_jnsa']); ?> months</td>
                                            <td style="padding:8px 6px;"><?php echo htmlspecialchars($loan_row_jnsa['date_applied_jnsa']); ?></td>
                                            <td style="padding:8px 6px;"><?php echo htmlspecialchars($loan_row_jnsa['date_disbursed_jnsa'] ?: 'N/A'); ?></td>
                                            <td style="padding:8px 6px;"><?php echo htmlspecialchars(dashboard_money($loan_row_jnsa['outstanding_balance_jnsa'])); ?></td>
                                            <td style="padding:8px 6px;">
                                                <?php if (is_null($loan_row_jnsa['date_approved_jnsa'])): ?>
                                                    <span style="display:inline-flex; align-items:center; padding:4px 10px; border-radius:999px; background:rgba(107,114,128,0.12); color:#374151; font-size:12px; font-weight:700;">Pending</span>
                                                <?php elseif (!is_null($loan_row_jnsa['date_disbursed_jnsa']) && (float)$loan_row_jnsa['outstanding_balance_jnsa'] <= 0): ?>
                                                    <span style="display:inline-flex; align-items:center; padding:4px 10px; border-radius:999px; background:rgba(59,130,246,0.12); color:#1d4ed8; font-size:12px; font-weight:700;">Paid</span>
                                                <?php elseif (!is_null($loan_row_jnsa['date_disbursed_jnsa'])): ?>
                                                    <span style="display:inline-flex; align-items:center; padding:4px 10px; border-radius:999px; background:rgba(16,185,129,0.12); color:#047857; font-size:12px; font-weight:700;">Approved</span>
                                                <?php else: ?>
                                                    <span style="display:inline-flex; align-items:center; padding:4px 10px; border-radius:999px; background:rgba(168,35,41,0.12); color:#a82329; font-size:12px; font-weight:700;">Approved</span>
                                                <?php endif; ?>
                                            </td>
                                        </tr>
                                    <?php endforeach; ?>
                                    </tbody>
                                </table>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>

            </section>
        </main>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
