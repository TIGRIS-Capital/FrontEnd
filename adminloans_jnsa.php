<?php
session_start();

$username_jnsa = $_SESSION['username'] ?? 'admin01';
$userRole_jnsa = $_SESSION['user_type'] ?? 'Admin';

// Ensure DB connection is available. Try a few likely paths and create a
// fallback connection with sane defaults. This prevents "undefined $conn"
// errors when the DB include was moved or renamed.
$db_included = false;
$db_candidates = [
	__DIR__ . '/Naval_FinalsActivity3_DB.php',
	__DIR__ . '/includes/Naval_FinalsActivity3_DB.php',
	__DIR__ . '/db/Naval_FinalsActivity3_DB.php',
	'Naval_FinalsActivity3_DB.php'
];
foreach ($db_candidates as $candidate) {
	if (file_exists($candidate)) {
		require_once $candidate;
		$db_included = true;
		break;
	}
}

if (!isset($conn) || !($conn instanceof mysqli)) {
	// Try to create a fallback connection. Adjust credentials if necessary.
	$fallback_server = 'localhost';
	$fallback_user = 'root';
	$fallback_pass = '';
	$fallback_db   = 'loan_management_jn';
	$conn = @new mysqli($fallback_server, $fallback_user, $fallback_pass, $fallback_db);
	if ($conn->connect_error) {
		http_response_code(500);
		echo "<h2>Database connection error</h2>\n";
		echo "<p>Please check database configuration. Error: " . htmlspecialchars($conn->connect_error) . "</p>";
		exit;
	}
}

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

function dashboard_rate_jnsa($value) {
	return number_format((float) $value, 2) . '%';
}

function loan_status_badge_jnsa(array $loan_row_jnsa): array {
	$outstanding_balance_jnsa = (float) ($loan_row_jnsa['outstanding_balance_jnsa'] ?? 0);
	$date_approved_jnsa = $loan_row_jnsa['date_approved_jnsa'] ?? null;

	if (is_null($date_approved_jnsa)) {
		return ['Pending', 'background:rgba(107,114,128,0.12); color:#374151;'];
	}

	if ($outstanding_balance_jnsa <= 0) {
		return ['Paid', 'background:rgba(16,185,129,0.12); color:#047857;'];
	}

	return ['Approved', 'background:rgba(168,35,41,0.12); color:#a82329;'];
}

$interest_rate_jnsa = 5.00;

$summary_pending_jnsa = (int) dashboard_scalar_jnsa($conn, "SELECT COUNT(*) FROM loan_jnsa WHERE date_approved_jnsa IS NULL");
$summary_approved_jnsa = (int) dashboard_scalar_jnsa($conn, "SELECT COUNT(*) FROM loan_jnsa WHERE date_approved_jnsa IS NOT NULL AND COALESCE(outstanding_balance_jnsa, 0) > 0");
$summary_paid_jnsa = (int) dashboard_scalar_jnsa($conn, "SELECT COUNT(*) FROM loan_jnsa WHERE COALESCE(outstanding_balance_jnsa, 0) = 0 AND date_approved_jnsa IS NOT NULL");

$loans_query_jnsa = $conn->query("SELECT l.loan_id_jnsa, l.loan_amount_jnsa, l.outstanding_balance_jnsa, l.date_applied_jnsa, l.date_approved_jnsa, m.member_name_jnsa, m.member_id_jnsa FROM loan_jnsa l INNER JOIN loan_member_jnsa m ON m.member_id_jnsa = l.borrower_id_jnsa ORDER BY l.date_applied_jnsa DESC, l.loan_id_jnsa DESC");
$loans_rows_jnsa = [];
if ($loans_query_jnsa && $loans_query_jnsa->num_rows > 0) {
	while ($loan_row_jnsa = $loans_query_jnsa->fetch_assoc()) {
		$loans_rows_jnsa[] = $loan_row_jnsa;
	}
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
	<meta charset="UTF-8">
	<meta name="viewport" content="width=device-width, initial-scale=1.0">
	<title>Admin Loans - TIGRIS Capital</title>
	<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body style="margin:0; background:#f4f5f7; color:#1f2937; font-family: Arial, Helvetica, sans-serif; overflow-x:hidden;">
	<div style="min-height:100vh; display:flex; background:#f4f5f7;">
		<!-- sideb... -->
		<aside style="width:240px; background:#121416; border-right:1px solid rgba(226,232,240,0.08); display:flex; flex-direction:column; justify-content:space-between;">
			<div>
				<!-- bran... -->
				<div style="height:69px; display:flex; align-items:center; gap:12px; padding:0 18px; border-bottom:1px solid rgba(226,232,240,0.08);">
					<div style="width:36px; height:36px; border-radius:6px; overflow:hidden;"><img src="Tigris_Logo_NoText.png" alt="logo" style="width:100%;height:100%;object-fit:cover;"></div>
					<div style="line-height:1.05;"><div style="font-size:16px; font-weight:700; color:#e2e8f0;">Admin Portal</div><div style="font-size:11px; color:#94a3b8; margin-top:2px;">System Admin</div></div>
				</div>
				<nav style="padding:18px 12px 0 12px;">
					<a href="admindashboard_jn.php" style="display:flex; align-items:center; gap:14px; height:48px; padding:0 16px; margin-bottom:10px; color:#cbd5e1; text-decoration:none; font-size:14px;">Overview</a>
					<a href="adminmembers_jnsa.php" style="display:flex; align-items:center; gap:14px; height:48px; padding:0 16px; margin-bottom:10px; color:#cbd5e1; text-decoration:none; font-size:14px;">Members</a>
					<a href="adminloans_jnsa.php" style="display:flex; align-items:center; gap:14px; height:48px; padding:0 16px; margin-bottom:10px; background:rgba(168,35,41,0.16); color:#ffffff; text-decoration:none; font-size:14px; font-weight:600; border-radius:8px;">Loans</a>
					<a href="adminreports_jnsa.php" style="display:flex; align-items:center; gap:14px; height:48px; padding:0 16px; color:#cbd5e1; text-decoration:none; font-size:14px;">Reports</a>
				</nav>
			</div>
			<div style="padding:20px 14px; border-top:1px solid rgba(226,232,240,0.08);">
				<a href="Loan_login_jn.php" style="display:flex; align-items:center; gap:12px; color:#e2e8f0; text-decoration:none; font-size:15px; font-weight:700;">↪ Logout</a>
			</div>
		</aside>

		<main style="flex:1; min-width:0; display:flex; flex-direction:column;">
			<!-- head... -->
			<header style="height:69px; background:#121416; border-bottom:1px solid rgba(226,232,240,0.08); display:flex; align-items:center; justify-content:space-between; padding:0 18px 0 20px; color:#fff;">
				<div>
					<div style="font-size:19px; font-weight:600;">Master Loan Tracking</div>
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

			<section style="padding:28px 18px 18px 18px; background:#f4f5f7; flex:1;">
				<div style="margin-bottom:18px;">
					<div style="font-size:26px; font-weight:600; color:#1f2937; letter-spacing:-0.2px; margin-bottom:9px;">Loan Administration</div>
					<div style="font-size:14px; color:#6b7280;">All loans joined to borrower records with live status tracking.</div>
				</div>

				<!-- sum... -->
				<div class="row g-3" style="margin:0 0 24px 0;">
					<div class="col-12 col-md-4 px-2">
						<div style="height:110px; background:#ffffff; border:1px solid #e5e7eb; border-radius:10px; box-shadow:0 2px 10px rgba(15,23,42,0.06); padding:14px 16px; display:flex; justify-content:space-between; align-items:flex-start;">
							<div>
								<div style="font-size:13px; color:#212529; font-weight:500;">Pending</div>
								<div style="font-size:22px; font-weight:700; color:#212529; margin-top:6px;"><?php echo intval($summary_pending_jnsa); ?></div>
							</div>
							<div style="width:42px; height:42px; border-radius:10px; background:rgba(107,114,128,0.08); display:flex; align-items:center; justify-content:center; color:#6b7280;">⌛</div>
						</div>
					</div>

					<div class="col-12 col-md-4 px-2">
						<div style="height:110px; background:#ffffff; border:1px solid #e5e7eb; border-radius:10px; box-shadow:0 2px 10px rgba(15,23,42,0.06); padding:14px 16px; display:flex; justify-content:space-between; align-items:flex-start;">
							<div>
								<div style="font-size:13px; color:#212529; font-weight:500;">Approved</div>
								<div style="font-size:22px; font-weight:700; color:#212529; margin-top:6px;"><?php echo intval($summary_approved_jnsa); ?></div>
							</div>
							<div style="width:42px; height:42px; border-radius:10px; background:rgba(168,35,41,0.08); display:flex; align-items:center; justify-content:center; color:#a82329;">✓</div>
						</div>
					</div>

					<div class="col-12 col-md-4 px-2">
						<div style="height:110px; background:#ffffff; border:1px solid #e5e7eb; border-radius:10px; box-shadow:0 2px 10px rgba(15,23,42,0.06); padding:14px 16px; display:flex; justify-content:space-between; align-items:flex-start;">
							<div>
								<div style="font-size:13px; color:#212529; font-weight:500;">Paid</div>
								<div style="font-size:22px; font-weight:700; color:#212529; margin-top:6px;"><?php echo intval($summary_paid_jnsa); ?></div>
							</div>
							<div style="width:42px; height:42px; border-radius:10px; background:rgba(16,185,129,0.08); display:flex; align-items:center; justify-content:center; color:#10b981;">$</div>
						</div>
					</div>
				</div>

				<!-- tabl... -->
				<div style="background:#ffffff; border:1px solid #e5e7eb; border-radius:12px; box-shadow:0 10px 30px rgba(15,23,42,0.08); padding:18px; overflow-x:auto;">
					<table style="width:100%; border-collapse:collapse; min-width:980px;">
						<thead>
							<tr style="text-align:left; color:#495057; font-size:12px; letter-spacing:0.2px; text-transform:uppercase;">
								<th style="padding:12px 10px; border-bottom:1px solid #e5e7eb;">Loan ID</th>
								<th style="padding:12px 10px; border-bottom:1px solid #e5e7eb;">Borrower Name</th>
								<th style="padding:12px 10px; border-bottom:1px solid #e5e7eb;">Principal Amount</th>
								<th style="padding:12px 10px; border-bottom:1px solid #e5e7eb;">Interest Rate</th>
								<th style="padding:12px 10px; border-bottom:1px solid #e5e7eb;">Remaining Outstanding Balance</th>
								<th style="padding:12px 10px; border-bottom:1px solid #e5e7eb;">Date Applied</th>
								<th style="padding:12px 10px; border-bottom:1px solid #e5e7eb;">Status</th>
							</tr>
						</thead>
						<tbody>
							<?php if (empty($loans_rows_jnsa)): ?>
								<tr>
									<td colspan="7" style="padding:18px 10px; color:#495057;">No loan records found.</td>
								</tr>
							<?php else: ?>
								<?php foreach ($loans_rows_jnsa as $loan_row_jnsa): ?>
									<?php [$status_label_jnsa, $status_style_jnsa] = loan_status_badge_jnsa($loan_row_jnsa); ?>
									<tr style="border-bottom:1px solid #f1f3f5; color:#212529;">
										<td style="padding:14px 10px;">#<?php echo intval($loan_row_jnsa['loan_id_jnsa']); ?></td>
										<td style="padding:14px 10px;"><?php echo htmlspecialchars($loan_row_jnsa['member_name_jnsa']); ?></td>
										<td style="padding:14px 10px;"><?php echo htmlspecialchars(dashboard_money_jnsa($loan_row_jnsa['loan_amount_jnsa'])); ?></td>
										<td style="padding:14px 10px;"><?php echo htmlspecialchars(dashboard_rate_jnsa($interest_rate_jnsa)); ?></td>
										<td style="padding:14px 10px;"><?php echo htmlspecialchars(dashboard_money_jnsa($loan_row_jnsa['outstanding_balance_jnsa'] ?? 0)); ?></td>
										<td style="padding:14px 10px;"><?php echo htmlspecialchars($loan_row_jnsa['date_applied_jnsa']); ?></td>
										<td style="padding:14px 10px;"><span style="display:inline-flex; align-items:center; padding:4px 10px; border-radius:999px; <?php echo $status_style_jnsa; ?> font-size:12px; font-weight:700;"><?php echo htmlspecialchars($status_label_jnsa); ?></span></td>
									</tr>
								<?php endforeach; ?>
							<?php endif; ?>
						</tbody>
					</table>
				</div>
			</section>
		</main>
	</div>

	<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>