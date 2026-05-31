<?php
session_start();

// Normalize the employee session and redirect non-employees away.
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
$loans_nav_active_jnsa = ($current_page_jnsa === 'employeeloans_jnsa.php');
$loan_types_nav_active_jnsa = ($current_page_jnsa === 'employeeloantypes_jnsa.php');

require_once "Naval_FinalsActivity3_DB.php";

// Small scalar helper for the summary cards.
function dashboard_scalar(mysqli $conn, string $sql, $default = 0) {
	$result = $conn->query($sql);
	if (!$result) {
		return $default;
	}
	$row = $result->fetch_row();
	return $row[0] ?? $default;
}

// Format values as currency for table and card output.
function dashboard_money($value) {
	return '$' . number_format((float) $value, 2);
}

$status_message_jnsa = '';
$error_message_jnsa = '';

// Search inputs and sort settings read from the query string.
$q = trim($_GET['q'] ?? '');
$sort = $_GET['sort'] ?? '';
$dir = strtolower($_GET['dir'] ?? 'desc');

// Restrict sorting to a known column map.
$allowed_sort_cols = [
	'loan_id' => 'l.loan_id_jnsa',
	'member' => 'm.member_name_jnsa',
	'amount' => 'l.loan_amount_jnsa',
	'term' => 'l.loan_term_jnsa',
	'applied' => 'l.date_applied_jnsa',
	'status' => 'l.date_approved_jnsa'
];
if (!in_array($dir, ['asc','desc'])) { $dir = 'desc'; }
$order_by = $allowed_sort_cols[$sort] ?? 'l.date_applied_jnsa';

// Handle the approval action inside a transaction.
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['approve_loan_jnsa'], $_POST['loan_id_jnsa'])) {
	$approve_loan_id_jnsa = (int) $_POST['loan_id_jnsa'];

	$loan_lookup_stmt_jnsa = $conn->prepare("SELECT loan_id_jnsa, borrower_id_jnsa, date_approved_jnsa FROM loan_jnsa WHERE loan_id_jnsa = ? LIMIT 1");
	if (!$loan_lookup_stmt_jnsa) {
		$error_message_jnsa = 'Unable to prepare the loan lookup.';
	} else {
		$loan_lookup_stmt_jnsa->bind_param('i', $approve_loan_id_jnsa);
		$loan_lookup_stmt_jnsa->execute();
		$loan_lookup_result_jnsa = $loan_lookup_stmt_jnsa->get_result();
		$loan_row_jnsa = $loan_lookup_result_jnsa ? $loan_lookup_result_jnsa->fetch_assoc() : null;
		$loan_lookup_stmt_jnsa->close();

		if (!$loan_row_jnsa) {
			$error_message_jnsa = 'The selected loan could not be found.';
		} elseif (!is_null($loan_row_jnsa['date_approved_jnsa'])) {
			$error_message_jnsa = 'This loan has already been approved.';
		} else {
			try {
				$conn->begin_transaction();

				$approve_stmt_jnsa = $conn->prepare("UPDATE loan_jnsa SET date_approved_jnsa = CURDATE(), date_disbursed_jnsa = CURDATE() WHERE loan_id_jnsa = ?");
				if (!$approve_stmt_jnsa) {
					throw new Exception('Unable to prepare the approval update.');
				}
				$approve_stmt_jnsa->bind_param('i', $approve_loan_id_jnsa);
				if (!$approve_stmt_jnsa->execute()) {
					throw new Exception('Unable to approve the loan.');
				}
				$approve_stmt_jnsa->close();

				$log_action_jnsa = 'Approved Loan ID #' . $approve_loan_id_jnsa;
				$log_stmt_jnsa = $conn->prepare("INSERT INTO loan_logs_jnsa (member_id_jnsa, action_jnsa, datetime_jnsa) VALUES (?, CONCAT('Approved Loan ID #', ?), NOW())");
				if (!$log_stmt_jnsa) {
					throw new Exception('Unable to prepare the approval log.');
				}
				$log_stmt_jnsa->bind_param('ii', $employee_id_jnsa, $approve_loan_id_jnsa);
				if (!$log_stmt_jnsa->execute()) {
					throw new Exception('Unable to save the approval log.');
				}
				$log_stmt_jnsa->close();

				$conn->commit();
				$_SESSION['approve_success_jnsa'] = 'Loan ID #' . $approve_loan_id_jnsa . ' approved successfully.';
				header('Location: employeeloans_jnsa.php?approved=1');
				exit();
			} catch (Throwable $throwable_jnsa) {
				$conn->rollback();
				$error_message_jnsa = 'Unable to approve the loan right now. Please try again.';
			}
		}
	}
}

$loans_jnsa = [];

// Build dynamic WHERE clauses based on filters.
$where_clauses = [];
if ($q !== '') {
	$esc = $conn->real_escape_string($q);
	if (is_numeric($q)) {
		$where_clauses[] = "(m.member_name_jnsa LIKE '%$esc%' OR l.loan_id_jnsa = " . (int)$q . ")";
	} else {
		$where_clauses[] = "m.member_name_jnsa LIKE '%$esc%'";
	}
}
// no status filter for employee view (sorting replaced filtering)

$where_sql = '';
if (!empty($where_clauses)) {
	$where_sql = ' WHERE ' . implode(' AND ', $where_clauses);
}

// Fetch joined loan and member rows for the table.
$sql_loans = "SELECT l.loan_id_jnsa, l.loan_amount_jnsa, l.loan_term_jnsa, l.date_applied_jnsa, l.date_approved_jnsa, l.date_disbursed_jnsa, l.outstanding_balance_jnsa, m.member_name_jnsa, m.member_id_jnsa
	FROM loan_jnsa l
	INNER JOIN loan_member_jnsa m ON m.member_id_jnsa = l.borrower_id_jnsa" . $where_sql . " ORDER BY $order_by $dir, l.loan_id_jnsa DESC";

$loans_query_jnsa = $conn->query($sql_loans);
if ($loans_query_jnsa && $loans_query_jnsa->num_rows > 0) {
	while ($loan_row_jnsa = $loans_query_jnsa->fetch_assoc()) {
		$loans_jnsa[] = $loan_row_jnsa;
	}
}

// KPI numbers displayed above the table.
$pending_count_jnsa = (int) dashboard_scalar($conn, "SELECT COUNT(*) FROM loan_jnsa WHERE date_approved_jnsa IS NULL");
$approved_count_jnsa = (int) dashboard_scalar($conn, "SELECT COUNT(*) FROM loan_jnsa WHERE date_approved_jnsa IS NOT NULL");
$total_value_pending_jnsa = (float) dashboard_scalar($conn, "SELECT COALESCE(SUM(loan_amount_jnsa), 0) FROM loan_jnsa WHERE date_approved_jnsa IS NULL");

$success_message_jnsa = '';
if (isset($_SESSION['approve_success_jnsa'])) {
	$success_message_jnsa = $_SESSION['approve_success_jnsa'];
	unset($_SESSION['approve_success_jnsa']);
} elseif (isset($_GET['approved']) && $_GET['approved'] == '1') {
	$success_message_jnsa = 'Loan approved successfully.';
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
	<meta charset="UTF-8">
	<meta name="viewport" content="width=device-width, initial-scale=1.0">
	<title>Employee Loans</title>
	<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body style="margin:0; background:#f4f6f9; color:#212529; font-family: Arial, Helvetica, sans-serif; overflow-x:hidden;">
	<div style="min-height:100vh; display:flex; background:#f4f6f9;">
		<!-- Sidebar -->
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

                <!-- navbar to other page -->                
				<nav style="padding:18px 12px 0 12px;">
					<a href="employeedashboard_jn.php" style="display:flex; align-items:center; gap:14px; height:48px; border-radius:8px; padding:0 16px; margin-bottom:10px; color:#cbd5e1; text-decoration:none; font-size:14px; font-weight:500;">Overview</a>
					<a href="employeeloans_jnsa.php" style="display:flex; align-items:center; gap:14px; height:48px; border-radius:8px; padding:0 16px; margin-bottom:10px; <?php echo $loans_nav_active_jnsa ? 'background:rgba(168,35,41,0.16); color:#ffffff; font-weight:600; border:1px solid rgba(168,35,41,0.28); box-shadow:inset 0 1px 0 rgba(255,255,255,0.03);' : 'color:#cbd5e1; font-weight:500;'; ?> text-decoration:none; font-size:14px;">Loans</a>
					<a href="employeepayments_jnsa.php" style="display:flex; align-items:center; gap:14px; height:48px; border-radius:8px; padding:0 16px; margin-bottom:10px; color:#cbd5e1; text-decoration:none; font-size:14px; font-weight:500;">Payments</a>
					<a href="employeeloantypes_jnsa.php" style="display:flex; align-items:center; gap:14px; height:48px; border-radius:8px; padding:0 16px; <?php echo $loan_types_nav_active_jnsa ? 'background:rgba(168,35,41,0.16); color:#ffffff; font-weight:600; border:1px solid rgba(168,35,41,0.28); box-shadow:inset 0 1px 0 rgba(255,255,255,0.03);' : 'color:#cbd5e1; font-weight:500;'; ?> text-decoration:none; font-size:14px;">Loan Types</a>
				</nav>
			</div>

			<div style="padding:20px 14px; border-top:1px solid rgba(226,232,240,0.08);">
				<a href="Loan_login_jn.php" style="display:flex; align-items:center; gap:12px; color:#e2e8f0; text-decoration:none; font-size:15px; font-weight:700; padding:12px 14px; border-radius:8px; background:rgba(255,255,255,0.02); border:1px solid rgba(226,232,240,0.08);">↪ Logout</a>
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

			<!-- Main Section -->
			<section style="padding:28px 18px 18px 18px; background:#f4f6f9; flex:1;">
				<div style="margin-bottom:18px;">
					<div style="font-size:26px; font-weight:600; color:#212529; letter-spacing:-0.2px; margin-bottom:9px;">Loan Administration</div>
					<div style="font-size:14px; color:#495057;">Review submitted loans and approve pending applications.</div>
				</div>

				<!-- Sorting helper (used in table headers) -->
				<?php
				function sort_link($key, $label) {
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

				<?php if ($success_message_jnsa !== ''): ?>
					<div style="margin-bottom:18px; padding:14px 16px; border-radius:8px; background:rgba(16,185,129,0.08); border:1px solid rgba(16,185,129,0.18); color:#047857; font-weight:600;">
						<?php echo htmlspecialchars($success_message_jnsa); ?>
					</div>
				<?php endif; ?>

				<?php if ($error_message_jnsa !== ''): ?>
					<div style="margin-bottom:18px; padding:14px 16px; border-radius:8px; background:rgba(168,35,41,0.08); border:1px solid rgba(168,35,41,0.18); color:#a82329; font-weight:600;">
						<?php echo htmlspecialchars($error_message_jnsa); ?>
					</div>
				<?php endif; ?>

				<!-- KPI Cards -->
				<div class="row gx-4 gy-4" style="margin:0 0 24px 0;">
					<div class="col-12 col-md-4 px-2">
						<div style="height:110px; background:#ffffff; border:1px solid #e5e7eb; border-radius:10px; box-shadow:0 2px 10px rgba(15,23,42,0.06); padding:14px 16px; display:flex; justify-content:space-between; align-items:flex-start;">
							<div>
								<div style="font-size:13px; color:#212529; font-weight:500;">Pending Loans</div>
								<div style="font-size:22px; font-weight:700; color:#212529; margin-top:6px;"><?php echo intval($pending_count_jnsa); ?></div>
							</div>
							<div style="width:42px; height:42px; border-radius:10px; background:rgba(107,114,128,0.08); display:flex; align-items:center; justify-content:center; color:#6b7280; flex:0 0 auto;">⌛</div>
						</div>
					</div>

					<div class="col-12 col-md-4 px-2">
						<div style="height:110px; background:#ffffff; border:1px solid #e5e7eb; border-radius:10px; box-shadow:0 2px 10px rgba(15,23,42,0.06); padding:14px 16px; display:flex; justify-content:space-between; align-items:flex-start;">
							<div>
								<div style="font-size:13px; color:#212529; font-weight:500;">Approved Loans</div>
								<div style="font-size:22px; font-weight:700; color:#212529; margin-top:6px;"><?php echo intval($approved_count_jnsa); ?></div>
							</div>
							<div style="width:42px; height:42px; border-radius:10px; background:rgba(16,185,129,0.08); display:flex; align-items:center; justify-content:center; color:#10b981; flex:0 0 auto;">✓</div>
						</div>
					</div>

					<div class="col-12 col-md-4 px-2">
						<div style="height:110px; background:#ffffff; border:1px solid #e5e7eb; border-radius:10px; box-shadow:0 2px 10px rgba(15,23,42,0.06); padding:14px 16px; display:flex; justify-content:space-between; align-items:flex-start;">
							<div>
								<div style="font-size:13px; color:#212529; font-weight:500;">Total Value Pending</div>
								<div style="font-size:22px; font-weight:700; color:#212529; margin-top:6px;"><?php echo htmlspecialchars(dashboard_money($total_value_pending_jnsa)); ?></div>
							</div>
							<div style="width:42px; height:42px; border-radius:10px; background:rgba(168,35,41,0.08); display:flex; align-items:center; justify-content:center; color:#a82329; flex:0 0 auto;">$</div>
						</div>
					</div>
				</div>

				<!-- Search -->
				<!-- Search form placed below cards and above the table -->
				<form method="get" style="display:flex; gap:8px; align-items:center; margin:0 0 16px 0;">
					<input type="text" name="q" value="<?php echo htmlspecialchars($q); ?>" placeholder="Search member name or loan ID" style="padding:8px 10px; border:1px solid #e5e7eb; border-radius:6px; min-width:220px;">
					<button type="submit" style="padding:8px 12px; background:#a82329; color:#fff; border:none; border-radius:6px;">Search</button>
					<a href="employeeloans_jnsa.php" style="padding:8px 12px; border-radius:6px; border:1px solid #e5e7eb; color:#374151; text-decoration:none;">Clear</a>
				</form>

				<!-- Table Container -->
				<div style="background:#ffffff; border:1px solid #e5e7eb; border-radius:12px; box-shadow:0 10px 30px rgba(15,23,42,0.08); padding:18px; overflow-x:auto;">
					<table style="width:100%; border-collapse:collapse; min-width:920px;">
						<thead>
							<tr style="text-align:left; color:#495057; font-size:12px; letter-spacing:0.2px; text-transform:uppercase;">
								<th style="padding:12px 10px; border-bottom:1px solid #e5e7eb;"><a href="<?php echo sort_link('loan_id','Loan ID'); ?>" style="text-decoration:none; color:inherit;">Loan ID</a></th>
								<th style="padding:12px 10px; border-bottom:1px solid #e5e7eb;"><a href="<?php echo sort_link('member','Member'); ?>" style="text-decoration:none; color:inherit;">Member</a></th>
								<th style="padding:12px 10px; border-bottom:1px solid #e5e7eb;"><a href="<?php echo sort_link('amount','Loan Amount'); ?>" style="text-decoration:none; color:inherit;">Loan Amount</a></th>
								<th style="padding:12px 10px; border-bottom:1px solid #e5e7eb;"><a href="<?php echo sort_link('term','Term'); ?>" style="text-decoration:none; color:inherit;">Term</a></th>
								<th style="padding:12px 10px; border-bottom:1px solid #e5e7eb;"><a href="<?php echo sort_link('applied','Date Applied'); ?>" style="text-decoration:none; color:inherit;">Date Applied</a></th>
								<th style="padding:12px 10px; border-bottom:1px solid #e5e7eb;"><a href="<?php echo sort_link('status','Status'); ?>" style="text-decoration:none; color:inherit;">Status</a></th>
								<th style="padding:12px 10px; border-bottom:1px solid #e5e7eb;">Action</th>
							</tr>
						</thead>
						<tbody>
							<?php if (empty($loans_jnsa)): ?>
								<tr>
									<td colspan="7" style="padding:18px 10px; color:#495057;">No loan records found.</td>
								</tr>
							<?php else: ?>
								<?php foreach ($loans_jnsa as $loan_row_jnsa): ?>
									<tr style="border-bottom:1px solid #f1f3f5; color:#212529;">
										<td style="padding:14px 10px;">#<?php echo intval($loan_row_jnsa['loan_id_jnsa']); ?></td>
										<td style="padding:14px 10px;"><?php echo htmlspecialchars($loan_row_jnsa['member_name_jnsa']); ?></td>
										<td style="padding:14px 10px;"><?php echo htmlspecialchars(dashboard_money($loan_row_jnsa['loan_amount_jnsa'])); ?></td>
										<td style="padding:14px 10px;"><?php echo htmlspecialchars(number_format((float) $loan_row_jnsa['loan_term_jnsa'], 0)); ?> months</td>
										<td style="padding:14px 10px;"><?php echo htmlspecialchars($loan_row_jnsa['date_applied_jnsa']); ?></td>
										<td style="padding:14px 10px;">
											<?php if (is_null($loan_row_jnsa['date_approved_jnsa'])): ?>
												<span style="display:inline-flex; align-items:center; padding:4px 10px; border-radius:999px; background:rgba(107,114,128,0.12); color:#374151; font-size:12px; font-weight:700;">Pending</span>
											<?php else: ?>
												<span style="display:inline-flex; align-items:center; padding:4px 10px; border-radius:999px; background:rgba(16,185,129,0.12); color:#047857; font-size:12px; font-weight:700;">Approved</span>
											<?php endif; ?>
										</td>
										<td style="padding:14px 10px;">
											<?php if (is_null($loan_row_jnsa['date_approved_jnsa'])): ?>
												<form method="post" action="employeeloans_jnsa.php" style="margin:0;">
													<input type="hidden" name="loan_id_jnsa" value="<?php echo intval($loan_row_jnsa['loan_id_jnsa']); ?>">
													<button type="submit" name="approve_loan_jnsa" value="1" style="border:none; border-radius:8px; background:#a82329; color:#ffffff; font-size:13px; font-weight:700; padding:8px 14px; box-shadow:0 6px 18px rgba(168,35,41,0.25);">Approve</button>
												</form>
											<?php else: ?>
												<span style="font-size:13px; color:#6b7280;">No action required</span>
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
