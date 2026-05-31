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
$member_name_jnsa = $_SESSION['member_name_jnsa'] ?? 'Member';

require_once "Naval_FinalsActivity3_DB.php";

// prepare loan types for filter dropdown
$loan_types_map_jnsa = [];
$lt_q = $conn->query("SELECT loan_type_id_jnsa, loan_type_name_jnsa FROM loan_type_jnsa ORDER BY loan_type_name_jnsa ASC");
if ($lt_q && $lt_q->num_rows > 0) {
	while ($lt_row = $lt_q->fetch_assoc()) {
		$loan_types_map_jnsa[$lt_row['loan_type_id_jnsa']] = $lt_row['loan_type_name_jnsa'];
	}
}

// Search and category filters
$q = trim($_GET['q'] ?? '');
$type_filter = $_GET['type'] ?? 'all';
$status_filter = $_GET['status'] ?? 'all';

$loan_rows_jnsa = [];

// detect whether loan_jnsa has a loan_type_id_jnsa column (legacy schemas may not)
$has_loan_type_col = false;
$col_check = $conn->query("SHOW COLUMNS FROM loan_jnsa LIKE 'loan_type_id_jnsa'");
if ($col_check && $col_check->num_rows > 0) {
	$has_loan_type_col = true;
}

// Build WHERE clauses
$where = ["l.borrower_id_jnsa = " . (int)$member_id_jnsa];
if ($q !== '') {
	$esc = $conn->real_escape_string($q);
	if (is_numeric($q)) {
		$where[] = "(l.loan_id_jnsa = " . (int)$q . ")";
	} else {
		// only search loan type name if the schema supports it
		if ($has_loan_type_col) {
			$where[] = "lt.loan_type_name_jnsa LIKE '%$esc%'";
		}
	}
}
if ($type_filter !== 'all' && $type_filter !== '') {
	if ($has_loan_type_col) {
		$where[] = "l.loan_type_id_jnsa = " . (int)$type_filter;
	}
}
if ($status_filter === 'pending') {
	$where[] = "l.date_approved_jnsa IS NULL";
} elseif ($status_filter === 'paid') {
	$where[] = "l.outstanding_balance_jnsa = 0";
} elseif ($status_filter === 'approved') {
	$where[] = "l.date_approved_jnsa IS NOT NULL AND l.outstanding_balance_jnsa > 0";
}

$where_sql = '';
if (!empty($where)) {
	$where_sql = ' WHERE ' . implode(' AND ', $where);
}

if ($has_loan_type_col) {
	$sql = "SELECT l.*, lt.loan_type_name_jnsa FROM loan_jnsa l LEFT JOIN loan_type_jnsa lt ON lt.loan_type_id_jnsa = l.loan_type_id_jnsa" . $where_sql . " ORDER BY l.date_applied_jnsa DESC, l.loan_id_jnsa DESC";
} else {
	// legacy schema without loan_type_id_jnsa: select loans without joining types
	$sql = "SELECT l.*, NULL AS loan_type_name_jnsa FROM loan_jnsa l" . $where_sql . " ORDER BY l.date_applied_jnsa DESC, l.loan_id_jnsa DESC";
}

$loan_query_jnsa = $conn->query($sql);
if ($loan_query_jnsa && $loan_query_jnsa->num_rows > 0) {
	while ($loan_row_jnsa = $loan_query_jnsa->fetch_assoc()) {
		$loan_rows_jnsa[] = $loan_row_jnsa;
	}
}

$success_message_jnsa = '';
if (isset($_SESSION['loan_success_jnsa'])) {
	$success_message_jnsa = $_SESSION['loan_success_jnsa'];
	unset($_SESSION['loan_success_jnsa']);
} elseif (isset($_GET['success']) && $_GET['success'] == '1') {
	$success_message_jnsa = 'Your loan application was submitted successfully.';
}

$payment_success_message_jnsa = '';
if (isset($_SESSION['payment_success_jnsa'])) {
	$payment_success_message_jnsa = $_SESSION['payment_success_jnsa'];
	unset($_SESSION['payment_success_jnsa']);
} elseif (isset($_GET['payment_success']) && $_GET['payment_success'] == '1') {
	$payment_success_message_jnsa = 'Your payment was processed successfully.';
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
	<meta charset="UTF-8">
	<meta name="viewport" content="width=device-width, initial-scale=1.0">
	<title>Member Loan Status</title>
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
						<div style="font-size:16px; font-weight:700; color:#e2e8f0;">Member Portal</div>
						<div style="font-size:11px; color:#94a3b8; margin-top:2px;">Account Holder</div>
					</div>
				</div>

				<nav style="padding:18px 12px 0 12px;">
					<a href="memberdashboard_jnsa.php" style="display:flex; align-items:center; gap:14px; height:48px; border-radius:8px; padding:0 16px; margin-bottom:10px; color:#cbd5e1; text-decoration:none; font-size:14px; font-weight:500;">Dashboard Home</a>
					<a href="memberloanform_jnsa.php" style="display:flex; align-items:center; gap:14px; height:48px; border-radius:8px; padding:0 16px; margin-bottom:10px; color:#cbd5e1; text-decoration:none; font-size:14px; font-weight:500;">Apply for a Loan</a>
					<a href="memberloanstatus_jnsa.php" style="display:flex; align-items:center; gap:14px; height:48px; border-radius:8px; padding:0 16px; background:rgba(168,35,41,0.16); color:#ffffff; text-decoration:none; font-size:14px; font-weight:600; border:1px solid rgba(168,35,41,0.28); box-shadow:inset 0 1px 0 rgba(255,255,255,0.03);">View Loan Status</a>
					<a href="memberpayment_jnsa.php" style="display:flex; align-items:center; gap:14px; height:48px; border-radius:8px; padding:0 16px; color:#cbd5e1; text-decoration:none; font-size:14px; font-weight:500;">Make Payment</a>
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
						<div style="width:36px; height:36px; border-radius:50%; background:#a82329; display:flex; align-items:center; justify-content:center; color:#fff; flex:0 0 auto; box-shadow:0 0 0 1px rgba(255,255,255,0.06);">
							<span style="font-size:15px; line-height:1; font-weight:700;"><?php echo strtoupper(substr($member_name_jnsa, 0, 1)); ?></span>
						</div>
						<div style="line-height:1.05;">
							<div style="font-size:12px; font-weight:700; color:#ffffff;"><?php echo htmlspecialchars($member_name_jnsa); ?></div>
							<div style="font-size:11px; color:#cbd5e1; margin-top:4px;">Member</div>
						</div>
					</div>
				</div>
			</header>

			<section style="padding:28px 18px 18px 18px; background:#f4f6f9; flex:1;">
				<div style="margin-bottom:18px;">
					<div style="font-size:26px; font-weight:600; color:#212529; letter-spacing:-0.2px; margin-bottom:9px;">View Loan Status</div>
					<div style="font-size:14px; color:#495057;">Private loan records for <?php echo htmlspecialchars($member_name_jnsa); ?>.</div>
				</div>

				<!-- Search & Category filters -->
				<form method="get" style="display:flex; gap:8px; align-items:center; margin-bottom:16px;">
					<input type="text" name="q" value="<?php echo htmlspecialchars($q); ?>" placeholder="Search loan ID or loan type" style="padding:8px 10px; border:1px solid #e5e7eb; border-radius:6px; min-width:260px;">
					<select name="type" style="padding:8px 10px; border:1px solid #e5e7eb; border-radius:6px;">
						<option value="all">All Loan Types</option>
						<?php foreach ($loan_types_map_jnsa as $lt_id => $lt_name): ?>
							<option value="<?php echo intval($lt_id); ?>" <?php echo ($type_filter == (string)$lt_id) ? 'selected' : ''; ?>><?php echo htmlspecialchars($lt_name); ?></option>
						<?php endforeach; ?>
					</select>
					<select name="status" style="padding:8px 10px; border:1px solid #e5e7eb; border-radius:6px;">
						<option value="all" <?php echo $status_filter === 'all' ? 'selected' : ''; ?>>All Statuses</option>
						<option value="pending" <?php echo $status_filter === 'pending' ? 'selected' : ''; ?>>Pending</option>
						<option value="approved" <?php echo $status_filter === 'approved' ? 'selected' : ''; ?>>Approved</option>
						<option value="paid" <?php echo $status_filter === 'paid' ? 'selected' : ''; ?>>Paid</option>
					</select>
					<button type="submit" style="padding:8px 12px; background:#a82329; color:#fff; border:none; border-radius:6px;">Search</button>
					<a href="memberloanstatus_jnsa.php" style="padding:8px 12px; border-radius:6px; border:1px solid #e5e7eb; color:#374151; text-decoration:none;">Clear</a>
				</form>
				<?php if ($success_message_jnsa !== ''): ?>
					<div style="margin-bottom:18px; padding:14px 16px; border-radius:8px; background:rgba(16,185,129,0.08); border:1px solid rgba(16,185,129,0.18); color:#047857; font-weight:600;">
						<?php echo htmlspecialchars($success_message_jnsa); ?>
					</div>
				<?php endif; ?>

				<?php if ($payment_success_message_jnsa !== ''): ?>
					<div style="margin-bottom:18px; padding:14px 16px; border-radius:8px; background:rgba(16,185,129,0.08); border:1px solid rgba(16,185,129,0.18); color:#047857; font-weight:600;">
						<?php echo htmlspecialchars($payment_success_message_jnsa); ?>
					</div>
				<?php endif; ?>

				<div style="background:#ffffff; border:1px solid #e5e7eb; border-radius:12px; box-shadow:0 10px 30px rgba(15,23,42,0.08); padding:18px; overflow-x:auto;">
					<table style="width:100%; border-collapse:collapse; min-width:760px;">
						<thead>
							<tr style="text-align:left; color:#495057; font-size:12px; letter-spacing:0.2px; text-transform:uppercase;">
								<th style="padding:12px 10px; border-bottom:1px solid #e5e7eb;">Loan ID</th>
								<th style="padding:12px 10px; border-bottom:1px solid #e5e7eb;">Amount</th>
								<th style="padding:12px 10px; border-bottom:1px solid #e5e7eb;">Type</th>
								<th style="padding:12px 10px; border-bottom:1px solid #e5e7eb;">Term</th>
								<th style="padding:12px 10px; border-bottom:1px solid #e5e7eb;">Date Applied</th>
								<th style="padding:12px 10px; border-bottom:1px solid #e5e7eb;">Outstanding Balance</th>
								<th style="padding:12px 10px; border-bottom:1px solid #e5e7eb;">Status</th>
							</tr>
						</thead>
						<tbody>
							<?php if (!$loan_query_jnsa || $loan_query_jnsa->num_rows === 0): ?>
								<tr>
									<td colspan="5" style="padding:18px 10px; color:#495057;">No loan records found.</td>
								</tr>
							<?php else: ?>
								<?php foreach ($loan_rows_jnsa as $loan_row_jnsa): ?>
									<tr style="border-bottom:1px solid #f1f3f5; color:#212529;">
										<td style="padding:14px 10px;">#<?php echo intval($loan_row_jnsa['loan_id_jnsa']); ?></td>
										<td style="padding:14px 10px;">$<?php echo number_format((float) $loan_row_jnsa['loan_amount_jnsa'], 2); ?></td>
										<td style="padding:14px 10px;"><?php echo htmlspecialchars($loan_row_jnsa['loan_type_name_jnsa'] ?? 'Standard'); ?></td>
										<td style="padding:14px 10px;"><?php echo htmlspecialchars(number_format((float) $loan_row_jnsa['loan_term_jnsa'], 0)); ?> months</td>
										<td style="padding:14px 10px;"><?php echo htmlspecialchars($loan_row_jnsa['date_applied_jnsa']); ?></td>
										<td style="padding:14px 10px;">$<?php echo number_format((float) $loan_row_jnsa['outstanding_balance_jnsa'], 2); ?></td>
										<td style="padding:14px 10px;">
											<?php if (is_null($loan_row_jnsa['date_approved_jnsa'])): ?>
												<span style="display:inline-flex; align-items:center; padding:4px 10px; border-radius:999px; background:rgba(107,114,128,0.12); color:#374151; font-size:12px; font-weight:700;">Pending</span>
											<?php elseif ((float) $loan_row_jnsa['outstanding_balance_jnsa'] == 0): ?>
												<span style="display:inline-flex; align-items:center; padding:4px 10px; border-radius:999px; background:rgba(59,130,246,0.12); color:#1d4ed8; font-size:12px; font-weight:700;">Paid</span>
											<?php else: ?>
												<span style="display:inline-flex; align-items:center; padding:4px 10px; border-radius:999px; background:rgba(16,185,129,0.12); color:#047857; font-size:12px; font-weight:700;">Approved</span>
											<?php endif; ?>
										</td>
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
