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

$loan_type_options_jnsa = [];
$loan_type_result_jnsa = $conn->query("SELECT loan_type_id_jnsa, loan_type_name_jnsa FROM loan_type_jnsa ORDER BY loan_type_name_jnsa ASC");
if ($loan_type_result_jnsa && $loan_type_result_jnsa->num_rows > 0) {
	while ($loan_type_row_jnsa = $loan_type_result_jnsa->fetch_assoc()) {
		$loan_type_options_jnsa[] = $loan_type_row_jnsa;
	}
}

$error_message_jnsa = '';
$success_message_jnsa = '';
$selected_loan_type_id_jnsa = '';
$selected_loan_type_name_jnsa = '';
$selected_loan_amount_jnsa = '';
$selected_loan_term_jnsa = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
	$selected_loan_type_id_jnsa = isset($_POST['loan_type_id_jnsa']) ? (int) $_POST['loan_type_id_jnsa'] : 0;
	$selected_loan_amount_jnsa = isset($_POST['loan_amount_jnsa']) ? (float) $_POST['loan_amount_jnsa'] : 0;
	$selected_loan_term_jnsa = isset($_POST['loan_term_jnsa']) ? (int) $_POST['loan_term_jnsa'] : 0;
	$interest_rate_jnsa = 5.00;
	$outstanding_balance_jnsa = $selected_loan_amount_jnsa;

	foreach ($loan_type_options_jnsa as $loan_type_row_jnsa) {
		if ((int) $loan_type_row_jnsa['loan_type_id_jnsa'] === $selected_loan_type_id_jnsa) {
			$selected_loan_type_name_jnsa = $loan_type_row_jnsa['loan_type_name_jnsa'];
			break;
		}
	}

	if ($selected_loan_type_id_jnsa <= 0) {
		$error_message_jnsa = 'Please select a loan type.';
	} elseif ($selected_loan_type_name_jnsa === '') {
		$error_message_jnsa = 'The selected loan type is not available.';
	} elseif ($selected_loan_amount_jnsa <= 0) {
		$error_message_jnsa = 'Please enter a valid loan amount.';
	} elseif ($selected_loan_term_jnsa <= 0) {
		$error_message_jnsa = 'Please enter a valid loan term.';
	} else {
		$insert_sql_jnsa = "INSERT INTO loan_jnsa (borrower_id_jnsa, loan_amount_jnsa, loan_term_jnsa, date_applied_jnsa, date_approved_jnsa, date_disbursed_jnsa, outstanding_balance_jnsa) VALUES (?, ?, ?, CURDATE(), NULL, NULL, ?)";
		$insert_stmt_jnsa = $conn->prepare($insert_sql_jnsa);

		if ($insert_stmt_jnsa) {
			$insert_stmt_jnsa->bind_param(
				"idid",
				$member_id_jnsa,
				$selected_loan_amount_jnsa,
				$selected_loan_term_jnsa,
				$outstanding_balance_jnsa
			);

			if ($insert_stmt_jnsa->execute()) {
				$new_loan_id_jnsa = (int) $conn->insert_id;
				$action_jnsa = 'Applied for ' . $selected_loan_type_name_jnsa;

				$log_columns_result_jnsa = $conn->query("SHOW COLUMNS FROM loan_logs_jnsa");
				$log_columns_jnsa = [];
				if ($log_columns_result_jnsa && $log_columns_result_jnsa->num_rows > 0) {
					while ($log_column_row_jnsa = $log_columns_result_jnsa->fetch_assoc()) {
						$log_columns_jnsa[] = $log_column_row_jnsa['Field'];
					}
				}

				$log_insert_columns_jnsa = [];
				$log_insert_values_jnsa = [];
				$log_bind_types_jnsa = '';
				$log_bind_values_jnsa = [];

				if (in_array('member_id_jnsa', $log_columns_jnsa, true)) {
					$log_insert_columns_jnsa[] = 'member_id_jnsa';
					$log_insert_values_jnsa[] = '?';
					$log_bind_types_jnsa .= 'i';
					$log_bind_values_jnsa[] = $member_id_jnsa;
				}

				if (in_array('loan_id_jnsa', $log_columns_jnsa, true)) {
					$log_insert_columns_jnsa[] = 'loan_id_jnsa';
					$log_insert_values_jnsa[] = '?';
					$log_bind_types_jnsa .= 'i';
					$log_bind_values_jnsa[] = $new_loan_id_jnsa;
				}

				if (in_array('action_jnsa', $log_columns_jnsa, true)) {
					$log_insert_columns_jnsa[] = 'action_jnsa';
					$log_insert_values_jnsa[] = '?';
					$log_bind_types_jnsa .= 's';
					$log_bind_values_jnsa[] = $action_jnsa;
				}

				if (in_array('datetime_jnsa', $log_columns_jnsa, true)) {
					$log_insert_columns_jnsa[] = 'datetime_jnsa';
					$log_insert_values_jnsa[] = 'NOW()';
				}

				if (!empty($log_insert_columns_jnsa)) {
					$log_insert_sql_jnsa = 'INSERT INTO loan_logs_jnsa (' . implode(', ', $log_insert_columns_jnsa) . ') VALUES (' . implode(', ', $log_insert_values_jnsa) . ')';
					$log_stmt_jnsa = $conn->prepare($log_insert_sql_jnsa);
					if ($log_stmt_jnsa) {
						if (count($log_bind_values_jnsa) === 2) {
							$log_stmt_jnsa->bind_param($log_bind_types_jnsa, $log_bind_values_jnsa[0], $log_bind_values_jnsa[1]);
							$log_stmt_jnsa->execute();
						} elseif (count($log_bind_values_jnsa) === 1) {
							$log_stmt_jnsa->bind_param($log_bind_types_jnsa, $log_bind_values_jnsa[0]);
							$log_stmt_jnsa->execute();
						} else {
							$log_stmt_jnsa->execute();
						}
						$log_stmt_jnsa->close();
					}
				}

				$_SESSION['loan_success_jnsa'] = 'Your loan application was submitted successfully.';
				header("Location: memberloanstatus_jnsa.php?success=1");
				exit();
			}

			$error_message_jnsa = 'Unable to submit your loan application. Please try again.';
			$insert_stmt_jnsa->close();
		} else {
			$error_message_jnsa = 'Unable to prepare the loan application request.';
		}
	}
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
	<meta charset="UTF-8">
	<meta name="viewport" content="width=device-width, initial-scale=1.0">
	<title>Member Loan Application</title>
	<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body style="margin:0; background:#f4f6f9; color:#212529; font-family: Arial, Helvetica, sans-serif; overflow-x:hidden;">
	<div style="min-height:100vh; display:flex; background:#f4f6f9;">
		<!-- Sidebar: Member navigation (Dashboard / Apply / Status / Payment) -->
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
					<a href="memberloanform_jnsa.php" style="display:flex; align-items:center; gap:14px; height:48px; border-radius:8px; padding:0 16px; margin-bottom:10px; background:rgba(168,35,41,0.16); color:#ffffff; text-decoration:none; font-size:14px; font-weight:600; border:1px solid rgba(168,35,41,0.28); box-shadow:inset 0 1px 0 rgba(255,255,255,0.03);">Apply for a Loan</a>
					<a href="memberloanstatus_jnsa.php" style="display:flex; align-items:center; gap:14px; height:48px; border-radius:8px; padding:0 16px; color:#cbd5e1; text-decoration:none; font-size:14px; font-weight:500;">View Loan Status</a>
					<a href="memberpayment_jnsa.php" style="display:flex; align-items:center; gap:14px; height:48px; border-radius:8px; padding:0 16px; color:#cbd5e1; text-decoration:none; font-size:14px; font-weight:500;">Make Payment</a>
				</nav>
			</div>

			<div style="padding:20px 14px; border-top:1px solid rgba(226,232,240,0.08);">
				<a href="Loan_login_jn.php" style="display:flex; align-items:center; gap:12px; color:#e2e8f0; text-decoration:none; font-size:15px; font-weight:700; padding:12px 14px; border-radius:8px; background:rgba(255,255,255,0.02); border:1px solid rgba(226,232,240,0.08);">↪ Logout</a>
			</div>
		</aside>

		<main style="flex:1; min-width:0; display:flex; flex-direction:column;">
			<!-- Header: Top bar with title and member info -->
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

			<!-- Main Section: Loan application form with validations and messages -->
			<section style="padding:28px 18px 18px 18px; background:#f4f6f9; flex:1;">
				<div style="margin-bottom:18px;">
					<div style="font-size:26px; font-weight:600; color:#212529; letter-spacing:-0.2px; margin-bottom:9px;">Apply for a Loan</div>
					<div style="font-size:14px; color:#495057;">Submit a new loan request from your member account.</div>
				</div>

				<?php if ($error_message_jnsa !== ''): ?>
					<div style="margin-bottom:18px; padding:14px 16px; border-radius:8px; background:rgba(168,35,41,0.08); border:1px solid rgba(168,35,41,0.18); color:#a82329; font-weight:600;">
						<?php echo htmlspecialchars($error_message_jnsa); ?>
					</div>
				<?php endif; ?>

				<!-- Form Card: Loan application form -->
				<div style="max-width:760px;">
					<form method="post" action="memberloanform_jnsa.php" style="background:#ffffff; border:1px solid #e5e7eb; border-radius:12px; box-shadow:0 10px 30px rgba(15,23,42,0.08); padding:22px;">
						<div class="row g-3">
							<div class="col-12">
								<label for="loan_type_id_jnsa" style="display:block; margin-bottom:8px; color:#212529; font-size:13px; font-weight:700;">Loan Type</label>
								<select id="loan_type_id_jnsa" name="loan_type_id_jnsa" style="width:100%; height:44px; border-radius:8px; background:#ffffff; color:#212529; border:1px solid #d1d5db; padding:0 12px; outline:none;">
									<option value="">Select loan type</option>
									<?php foreach ($loan_type_options_jnsa as $loan_type_row_jnsa): ?>
										<option value="<?php echo intval($loan_type_row_jnsa['loan_type_id_jnsa']); ?>" <?php echo ((int)$selected_loan_type_id_jnsa === (int)$loan_type_row_jnsa['loan_type_id_jnsa']) ? 'selected' : ''; ?>><?php echo htmlspecialchars($loan_type_row_jnsa['loan_type_name_jnsa']); ?></option>
									<?php endforeach; ?>
								</select>
							</div>

							<div class="col-12 col-md-6">
									<label for="loan_amount_jnsa" style="display:block; margin-bottom:8px; color:#212529; font-size:13px; font-weight:700;">Desired Loan Amount</label>
									<input type="number" step="0.01" min="0" id="loan_amount_jnsa" name="loan_amount_jnsa" value="<?php echo htmlspecialchars((string)$selected_loan_amount_jnsa); ?>" style="width:100%; height:44px; border-radius:8px; background:#ffffff; color:#212529; border:1px solid #d1d5db; padding:0 12px; outline:none;">
							</div>

							<div class="col-12 col-md-6">
									<label for="loan_term_jnsa" style="display:block; margin-bottom:8px; color:#212529; font-size:13px; font-weight:700;">Loan Term in Months</label>
									<input type="number" min="1" id="loan_term_jnsa" name="loan_term_jnsa" value="<?php echo htmlspecialchars((string)$selected_loan_term_jnsa); ?>" style="width:100%; height:44px; border-radius:8px; background:#ffffff; color:#212529; border:1px solid #d1d5db; padding:0 12px; outline:none;">
							</div>

							<div class="col-12">
									<div style="padding:14px 16px; border-radius:8px; background:#f8f9fa; border:1px solid #e5e7eb; color:#495057; font-size:13px; line-height:1.6;">
									Interest rate is fixed at 5.00% for this application. Your outstanding balance will start at the requested loan amount.
								</div>
							</div>

							<div class="col-12" style="padding-top:4px;">
								<button type="submit" style="height:46px; min-width:180px; border:none; border-radius:8px; background:#a82329; color:#ffffff; font-size:14px; font-weight:700; padding:0 18px; box-shadow:0 6px 18px rgba(168,35,41,0.28);">
									Submit Loan Request
								</button>
							</div>
						</div>
					</form>
				</div>
			</section>
		</main>
	</div>

	<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
