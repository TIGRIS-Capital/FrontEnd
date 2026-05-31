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

$total_logs_jnsa = (int) dashboard_scalar_jnsa($conn, "SELECT COUNT(*) FROM loan_logs_jnsa");
$today_logs_jnsa = (int) dashboard_scalar_jnsa($conn, "SELECT COUNT(*) FROM loan_logs_jnsa WHERE DATE(datetime_jnsa) = CURDATE()");

$logs_query_jnsa = $conn->query("SELECT lg.log_id_jnsa, lg.member_id_jnsa, lg.action_jnsa, lg.datetime_jnsa, lm.member_name_jnsa FROM loan_logs_jnsa lg LEFT JOIN loan_member_jnsa lm ON lm.member_id_jnsa = lg.member_id_jnsa ORDER BY lg.datetime_jnsa DESC, lg.log_id_jnsa DESC");
$logs_rows_jnsa = [];
if ($logs_query_jnsa && $logs_query_jnsa->num_rows > 0) {
	while ($log_row_jnsa = $logs_query_jnsa->fetch_assoc()) {
		$logs_rows_jnsa[] = $log_row_jnsa;
	}
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
	<meta charset="UTF-8">
	<meta name="viewport" content="width=device-width, initial-scale=1.0">
	<title>Admin Reports - TIGRIS Capital</title>
	<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body style="margin:0; background:#f4f5f7; color:#1f2937; font-family: Arial, Helvetica, sans-serif; overflow-x:hidden;">
	<div style="min-height:100vh; display:flex; background:#f4f5f7;">
		<aside style="width:240px; background:#121416; border-right:1px solid rgba(226,232,240,0.08); display:flex; flex-direction:column; justify-content:space-between;">
			<div>
				<div style="height:69px; display:flex; align-items:center; gap:12px; padding:0 18px; border-bottom:1px solid rgba(226,232,240,0.08);">
					<div style="width:36px; height:36px; border-radius:6px; overflow:hidden;"><img src="Tigris_Logo_NoText.png" alt="logo" style="width:100%;height:100%;object-fit:cover;"></div>
					<div style="line-height:1.05;"><div style="font-size:16px; font-weight:700; color:#e2e8f0;">Admin Portal</div><div style="font-size:11px; color:#94a3b8; margin-top:2px;">System Admin</div></div>
				</div>
				<nav style="padding:18px 12px 0 12px;">
					<a href="admindashboard_jn.php" style="display:flex; align-items:center; gap:14px; height:48px; padding:0 16px; margin-bottom:10px; color:#cbd5e1; text-decoration:none; font-size:14px;">Overview</a>
					<a href="adminmembers_jnsa.php" style="display:flex; align-items:center; gap:14px; height:48px; padding:0 16px; margin-bottom:10px; color:#cbd5e1; text-decoration:none; font-size:14px;">Members</a>
					<a href="adminloans_jnsa.php" style="display:flex; align-items:center; gap:14px; height:48px; padding:0 16px; margin-bottom:10px; color:#cbd5e1; text-decoration:none; font-size:14px;">Loans</a>
					<a href="adminreports_jnsa.php" style="display:flex; align-items:center; gap:14px; height:48px; padding:0 16px; margin-bottom:10px; background:rgba(168,35,41,0.16); color:#ffffff; text-decoration:none; font-size:14px; font-weight:600; border-radius:8px;">Reports</a>
				</nav>
			</div>
			<div style="padding:20px 14px; border-top:1px solid rgba(226,232,240,0.08);">
				<a href="Loan_login_jn.php" style="display:flex; align-items:center; gap:12px; color:#e2e8f0; text-decoration:none; font-size:15px; font-weight:700;">↪ Logout</a>
			</div>
		</aside>

		<main style="flex:1; min-width:0; display:flex; flex-direction:column;">
			<header style="height:69px; background:#121416; border-bottom:1px solid rgba(226,232,240,0.08); display:flex; align-items:center; justify-content:space-between; padding:0 18px 0 20px; color:#fff;">
				<div>
					<div style="font-size:19px; font-weight:600;">Audit Log Reports</div>
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
					<div style="font-size:26px; font-weight:600; color:#1f2937; letter-spacing:-0.2px; margin-bottom:9px;">Audit Trail</div>
					<div style="font-size:14px; color:#6b7280;">Chronological action log for all tracked system events.</div>
				</div>

				<div class="row g-3" style="margin:0 0 24px 0;">
					<div class="col-12 col-md-6 px-2">
						<div style="height:110px; background:#ffffff; border:1px solid #e5e7eb; border-radius:10px; box-shadow:0 2px 10px rgba(15,23,42,0.06); padding:14px 16px; display:flex; justify-content:space-between; align-items:flex-start;">
							<div>
								<div style="font-size:13px; color:#212529; font-weight:500;">Total Logs</div>
								<div style="font-size:22px; font-weight:700; color:#212529; margin-top:6px;"><?php echo intval($total_logs_jnsa); ?></div>
							</div>
							<div style="width:42px; height:42px; border-radius:10px; background:rgba(168,35,41,0.08); display:flex; align-items:center; justify-content:center; color:#a82329;">#</div>
						</div>
					</div>

					<div class="col-12 col-md-6 px-2">
						<div style="height:110px; background:#ffffff; border:1px solid #e5e7eb; border-radius:10px; box-shadow:0 2px 10px rgba(15,23,42,0.06); padding:14px 16px; display:flex; justify-content:space-between; align-items:flex-start;">
							<div>
								<div style="font-size:13px; color:#212529; font-weight:500;">Today's Activity</div>
								<div style="font-size:22px; font-weight:700; color:#212529; margin-top:6px;"><?php echo intval($today_logs_jnsa); ?></div>
							</div>
							<div style="width:42px; height:42px; border-radius:10px; background:rgba(16,185,129,0.08); display:flex; align-items:center; justify-content:center; color:#10b981;">⏱</div>
						</div>
					</div>
				</div>

				<div style="background:#ffffff; border:1px solid #e5e7eb; border-radius:12px; box-shadow:0 10px 30px rgba(15,23,42,0.08); padding:18px; overflow-x:auto;">
					<table style="width:100%; border-collapse:collapse; min-width:860px;">
						<thead>
							<tr style="text-align:left; color:#495057; font-size:12px; letter-spacing:0.2px; text-transform:uppercase;">
								<th style="padding:12px 10px; border-bottom:1px solid #e5e7eb;">Log ID</th>
								<th style="padding:12px 10px; border-bottom:1px solid #e5e7eb;">Actor Name</th>
								<th style="padding:12px 10px; border-bottom:1px solid #e5e7eb;">Action Performed</th>
								<th style="padding:12px 10px; border-bottom:1px solid #e5e7eb;">System Timestamp</th>
							</tr>
						</thead>
						<tbody>
							<?php if (empty($logs_rows_jnsa)): ?>
								<tr>
									<td colspan="4" style="padding:18px 10px; color:#495057;">No audit log records found.</td>
								</tr>
							<?php else: ?>
								<?php foreach ($logs_rows_jnsa as $log_row_jnsa): ?>
									<tr style="border-bottom:1px solid #f1f3f5; color:#212529;">
										<td style="padding:14px 10px;">#<?php echo intval($log_row_jnsa['log_id_jnsa']); ?></td>
										<td style="padding:14px 10px;"><?php echo htmlspecialchars($log_row_jnsa['member_name_jnsa'] ?? 'Unknown Actor'); ?></td>
										<td style="padding:14px 10px;"><?php echo htmlspecialchars($log_row_jnsa['action_jnsa']); ?></td>
										<td style="padding:14px 10px;"><?php echo htmlspecialchars($log_row_jnsa['datetime_jnsa']); ?></td>
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