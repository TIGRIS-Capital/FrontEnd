<?php
session_start();

if (!isset($_SESSION['member_id_jnsa']) && isset($_SESSION['member_id'])) {
	$_SESSION['member_id_jnsa'] = (int) $_SESSION['member_id'];
}

if (!isset($_SESSION['member_id_jnsa']) || !isset($_SESSION['user_type']) || strtolower((string) $_SESSION['user_type']) !== 'employee') {
	header("Location: Loan_login_jn.php");
	exit();
}

$employee_id_jnsa = (int) $_SESSION['member_id_jnsa'];
if (!isset($employee_name_jnsa)) {
	$employee_name_jnsa = $_SESSION['member_name_jnsa'] ?? ($_SESSION['username'] ?? 'Employee');
}
$current_page_jnsa = basename($_SERVER['PHP_SELF']);
$payments_nav_active_jnsa = ($current_page_jnsa === 'employeepayments_jnsa.php');
$loan_types_nav_active_jnsa = ($current_page_jnsa === 'employeeloantypes_jnsa.php');

require_once "Naval_FinalsActivity3_DB.php";

function dashboard_scalar(mysqli $conn, string $sql, $default = 0) {
	$result = $conn->query($sql);
	if (!$result) {
		return $default;
	}
	$row = $result->fetch_row();
	return $row[0] ?? $default;
}

function dashboard_money($value) {
	return '$' . number_format((float) $value, 2);
}

$payments_jnsa = [];

// Search inputs and sorting (GET)
$q = trim($_GET['q'] ?? '');
$sort = $_GET['sort'] ?? '';
$dir = strtolower($_GET['dir'] ?? 'desc');

// allowed sort columns -> SQL mapping
$allowed_sort_cols = [
	'payment_id' => 'p.payment_id_jnsa',
	'member' => 'm.member_name_jnsa',
	'loan' => 'l.loan_id_jnsa',
	'amount' => 'p.payment_amount_jnsa',
	'date' => 'p.payment_date_jnsa'
];
if (!in_array($dir, ['asc','desc'])) { $dir = 'desc'; }
$order_by = $allowed_sort_cols[$sort] ?? 'p.payment_date_jnsa';

// Build where clauses
$where = [];
if ($q !== '') {
	$esc = $conn->real_escape_string($q);
	if (is_numeric($q)) {
		$where[] = "(m.member_name_jnsa LIKE '%$esc%' OR p.payment_id_jnsa = " . (int)$q . " OR l.loan_id_jnsa = " . (int)$q . ")";
	} else {
		$where[] = "m.member_name_jnsa LIKE '%$esc%'";
	}
}

$where_sql = '';
if (!empty($where)) {
	$where_sql = ' WHERE ' . implode(' AND ', $where);
}

$sql_payments = "SELECT p.payment_id_jnsa, m.member_name_jnsa, l.loan_id_jnsa, p.payment_amount_jnsa, p.payment_date_jnsa
	FROM payment_jnsa p
	INNER JOIN loan_jnsa l ON l.loan_id_jnsa = p.loan_id_jnsa
	INNER JOIN loan_member_jnsa m ON m.member_id_jnsa = l.borrower_id_jnsa" . $where_sql . " ORDER BY $order_by $dir, p.payment_id_jnsa DESC";

$payments_query_jnsa = $conn->query($sql_payments);
if ($payments_query_jnsa && $payments_query_jnsa->num_rows > 0) {
	while ($payment_row_jnsa = $payments_query_jnsa->fetch_assoc()) {
		$payments_jnsa[] = $payment_row_jnsa;
	}
}

$total_payments_count_jnsa = (int) dashboard_scalar($conn, "SELECT COUNT(*) FROM payment_jnsa");
$total_payment_amount_jnsa = (float) dashboard_scalar($conn, "SELECT COALESCE(SUM(payment_amount_jnsa), 0) FROM payment_jnsa");
$latest_payment_date_jnsa = dashboard_scalar($conn, "SELECT MAX(payment_date_jnsa) FROM payment_jnsa", '');
?>
<!DOCTYPE html>
<html lang="en">
<head>
	<meta charset="UTF-8">
	<meta name="viewport" content="width=device-width, initial-scale=1.0">
	<title>Employee Payments</title>
	<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body style="margin:0; background:#f4f6f9; color:#212529; font-family: Arial, Helvetica, sans-serif; overflow-x:hidden;">
	<div style="min-height:100vh; display:flex; background:#f4f6f9;">
		<aside style="width:240px; background:#121416; border-right:1px solid rgba(226,232,240,0.08); box-shadow:0 0 0 1px rgba(0,0,0,0.08); display:flex; flex-direction:column; justify-content:space-between;">
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

				<nav style="padding:18px 12px 0 12px;">
					<a href="employeedashboard_jn.php" style="display:flex; align-items:center; gap:14px; height:48px; border-radius:8px; padding:0 16px; margin-bottom:10px; color:#cbd5e1; text-decoration:none; font-size:14px; font-weight:500;">Overview</a>
					<a href="employeeloans_jnsa.php" style="display:flex; align-items:center; gap:14px; height:48px; border-radius:8px; padding:0 16px; margin-bottom:10px; color:#cbd5e1; text-decoration:none; font-size:14px; font-weight:500;">Loans</a>
					<a href="employeepayments_jnsa.php" style="display:flex; align-items:center; gap:14px; height:48px; border-radius:8px; padding:0 16px; margin-bottom:10px; <?php echo $payments_nav_active_jnsa ? 'background:rgba(168,35,41,0.16); color:#ffffff; font-weight:600; border:1px solid rgba(168,35,41,0.28); box-shadow:inset 0 1px 0 rgba(255,255,255,0.03);' : 'color:#cbd5e1; font-weight:500;'; ?> text-decoration:none; font-size:14px;">Payments</a>
					<a href="employeeloantypes_jnsa.php" style="display:flex; align-items:center; gap:14px; height:48px; border-radius:8px; padding:0 16px; <?php echo $loan_types_nav_active_jnsa ? 'background:rgba(168,35,41,0.16); color:#ffffff; font-weight:600; border:1px solid rgba(168,35,41,0.28); box-shadow:inset 0 1px 0 rgba(255,255,255,0.03);' : 'color:#cbd5e1; font-weight:500;'; ?> text-decoration:none; font-size:14px;">Loan Types</a>
				</nav>
			</div>

			<div style="padding:20px 14px; border-top:1px solid rgba(226,232,240,0.08);">
				<a href="Loan_login_jn.php" style="display:flex; align-items:center; gap:12px; color:#e2e8f0; text-decoration:none; font-size:15px; font-weight:700; padding:12px 14px; border-radius:8px; background:rgba(255,255,255,0.02); border:1px solid rgba(226,232,240,0.08);">↪ Logout</a>
			</div>
		</aside>

		<main style="flex:1; min-width:0; display:flex; flex-direction:column;">
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

			<section style="padding:28px 18px 18px 18px; background:#f4f6f9; flex:1;">
				<div style="margin-bottom:18px;">
					<div style="font-size:26px; font-weight:600; color:#212529; letter-spacing:-0.2px; margin-bottom:9px;">Payment History</div>
					<div style="font-size:14px; color:#495057;">Track every posted payment across all member loans.</div>
				</div>

				<?php
				function sort_link_payments($key) {
					$params = $_GET;
					$currentSort = $_GET['sort'] ?? '';
					$currentDir = strtolower($_GET['dir'] ?? 'desc');
					$newDir = 'desc';
					if ($currentSort === $key && $currentDir === 'desc') { $newDir = 'asc'; }
					$params['sort'] = $key;
					$params['dir'] = $newDir;
					return htmlspecialchars('?' . http_build_query($params));
				}
				?>

				<div class="row gx-4 gy-4" style="margin:0 0 24px 0;">
					<div class="col-12 col-md-4 px-2">
						<div style="height:110px; background:#ffffff; border:1px solid #e5e7eb; border-radius:10px; box-shadow:0 2px 10px rgba(15,23,42,0.06); padding:14px 16px; display:flex; justify-content:space-between; align-items:flex-start;">
							<div>
								<div style="font-size:13px; color:#212529; font-weight:500;">Total Payments</div>
								<div style="font-size:22px; font-weight:700; color:#212529; margin-top:6px;"><?php echo intval($total_payments_count_jnsa); ?></div>
							</div>
							<div style="width:42px; height:42px; border-radius:10px; background:rgba(16,185,129,0.08); display:flex; align-items:center; justify-content:center; color:#10b981; flex:0 0 auto;">✓</div>
						</div>
					</div>

					<div class="col-12 col-md-4 px-2">
						<div style="height:110px; background:#ffffff; border:1px solid #e5e7eb; border-radius:10px; box-shadow:0 2px 10px rgba(15,23,42,0.06); padding:14px 16px; display:flex; justify-content:space-between; align-items:flex-start;">
							<div>
								<div style="font-size:13px; color:#212529; font-weight:500;">Total Amount Collected</div>
								<div style="font-size:22px; font-weight:700; color:#212529; margin-top:6px;"><?php echo htmlspecialchars(dashboard_money($total_payment_amount_jnsa)); ?></div>
							</div>
							<div style="width:42px; height:42px; border-radius:10px; background:rgba(168,35,41,0.08); display:flex; align-items:center; justify-content:center; color:#a82329; flex:0 0 auto;">$</div>
						</div>
					</div>

					<div class="col-12 col-md-4 px-2">
						<div style="height:110px; background:#ffffff; border:1px solid #e5e7eb; border-radius:10px; box-shadow:0 2px 10px rgba(15,23,42,0.06); padding:14px 16px; display:flex; justify-content:space-between; align-items:flex-start;">
							<div>
								<div style="font-size:13px; color:#212529; font-weight:500;">Latest Payment Date</div>
								<div style="font-size:16px; font-weight:700; color:#212529; margin-top:6px;"><?php echo $latest_payment_date_jnsa ? htmlspecialchars($latest_payment_date_jnsa) : 'No payments yet'; ?></div>
							</div>
							<div style="width:42px; height:42px; border-radius:10px; background:rgba(107,114,128,0.08); display:flex; align-items:center; justify-content:center; color:#6b7280; flex:0 0 auto;">⏳</div>
						</div>
					</div>
				</div>

				<!-- Search form placed below cards and above the table -->
				<form method="get" style="display:flex; gap:8px; align-items:center; margin:0 0 16px 0;">
					<input type="text" name="q" value="<?php echo htmlspecialchars($q); ?>" placeholder="Search member name, payment ID or loan ID" style="padding:8px 10px; border:1px solid #e5e7eb; border-radius:6px; min-width:280px;">
					<button type="submit" style="padding:8px 12px; background:#a82329; color:#fff; border:none; border-radius:6px;">Search</button>
					<a href="employeepayments_jnsa.php" style="padding:8px 12px; border-radius:6px; border:1px solid #e5e7eb; color:#374151; text-decoration:none;">Clear</a>
				</form>

				<div style="background:#ffffff; border:1px solid #e5e7eb; border-radius:12px; box-shadow:0 10px 30px rgba(15,23,42,0.08); padding:18px; overflow-x:auto;">
					<table style="width:100%; border-collapse:collapse; min-width:900px;">
						<thead>
							<tr style="text-align:left; color:#495057; font-size:12px; letter-spacing:0.2px; text-transform:uppercase;">
								<th style="padding:12px 10px; border-bottom:1px solid #e5e7eb;"><a href="<?php echo sort_link_payments('payment_id'); ?>" style="text-decoration:none; color:inherit;">Payment ID</a></th>
								<th style="padding:12px 10px; border-bottom:1px solid #e5e7eb;"><a href="<?php echo sort_link_payments('member'); ?>" style="text-decoration:none; color:inherit;">Member</a></th>
								<th style="padding:12px 10px; border-bottom:1px solid #e5e7eb;"><a href="<?php echo sort_link_payments('loan'); ?>" style="text-decoration:none; color:inherit;">Loan ID</a></th>
								<th style="padding:12px 10px; border-bottom:1px solid #e5e7eb;"><a href="<?php echo sort_link_payments('amount'); ?>" style="text-decoration:none; color:inherit;">Payment Amount</a></th>
								<th style="padding:12px 10px; border-bottom:1px solid #e5e7eb;"><a href="<?php echo sort_link_payments('date'); ?>" style="text-decoration:none; color:inherit;">Payment Date</a></th>
							</tr>
						</thead>
						<tbody>
							<?php if (empty($payments_jnsa)): ?>
								<tr>
									<td colspan="5" style="padding:18px 10px; color:#495057;">No payment records found.</td>
								</tr>
							<?php else: ?>
								<?php foreach ($payments_jnsa as $payment_row_jnsa): ?>
									<tr style="border-bottom:1px solid #f1f3f5; color:#212529;">
										<td style="padding:14px 10px;">#<?php echo intval($payment_row_jnsa['payment_id_jnsa']); ?></td>
										<td style="padding:14px 10px;"><?php echo htmlspecialchars($payment_row_jnsa['member_name_jnsa']); ?></td>
										<td style="padding:14px 10px;">#<?php echo intval($payment_row_jnsa['loan_id_jnsa']); ?></td>
										<td style="padding:14px 10px;"><?php echo htmlspecialchars(dashboard_money($payment_row_jnsa['payment_amount_jnsa'])); ?></td>
										<td style="padding:14px 10px;"><?php echo htmlspecialchars($payment_row_jnsa['payment_date_jnsa']); ?></td>
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
