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

$active_loans_jnsa = [];
$active_loans_query_jnsa = $conn->query("SELECT loan_id_jnsa, loan_amount_jnsa, outstanding_balance_jnsa FROM loan_jnsa WHERE borrower_id_jnsa = $member_id_jnsa AND outstanding_balance_jnsa > 0 ORDER BY date_applied_jnsa DESC");
if ($active_loans_query_jnsa && $active_loans_query_jnsa->num_rows > 0) {
	while ($active_loan_row_jnsa = $active_loans_query_jnsa->fetch_assoc()) {
		$active_loans_jnsa[] = $active_loan_row_jnsa;
	}
}

$error_message_jnsa = '';
$selected_loan_id_jnsa = '';
$selected_payment_amount_jnsa = '';
$selected_loan_row_jnsa = null;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
	$selected_loan_id_jnsa = isset($_POST['loan_id_jnsa']) ? (int) $_POST['loan_id_jnsa'] : 0;
	$selected_payment_amount_jnsa = isset($_POST['payment_amount_jnsa']) ? (float) $_POST['payment_amount_jnsa'] : 0;

	if ($selected_loan_id_jnsa <= 0) {
		$error_message_jnsa = 'Please select an active loan.';
	} elseif ($selected_payment_amount_jnsa <= 0) {
		$error_message_jnsa = 'Please enter a valid payment amount.';
	} else {
		$loan_lookup_stmt_jnsa = $conn->prepare("SELECT loan_id_jnsa, outstanding_balance_jnsa FROM loan_jnsa WHERE loan_id_jnsa = ? AND borrower_id_jnsa = ? AND outstanding_balance_jnsa > 0 LIMIT 1");
		if ($loan_lookup_stmt_jnsa) {
			$loan_lookup_stmt_jnsa->bind_param('ii', $selected_loan_id_jnsa, $member_id_jnsa);
			$loan_lookup_stmt_jnsa->execute();
			$loan_lookup_result_jnsa = $loan_lookup_stmt_jnsa->get_result();
			$selected_loan_row_jnsa = $loan_lookup_result_jnsa ? $loan_lookup_result_jnsa->fetch_assoc() : null;
			$loan_lookup_stmt_jnsa->close();
		}

		if (!$selected_loan_row_jnsa) {
			$error_message_jnsa = 'The selected loan is not available for payment.';
		} elseif ($selected_payment_amount_jnsa > (float) $selected_loan_row_jnsa['outstanding_balance_jnsa']) {
			$error_message_jnsa = 'Payment amount cannot exceed the outstanding balance.';
		} else {
			try {
				$conn->begin_transaction();

				$payment_insert_stmt_jnsa = $conn->prepare("INSERT INTO payment_jnsa (loan_id_jnsa, payment_amount_jnsa, payment_date_jnsa) VALUES (?, ?, NOW())");
				if (!$payment_insert_stmt_jnsa) {
					throw new Exception('Unable to prepare payment record.');
				}
				$payment_insert_stmt_jnsa->bind_param('id', $selected_loan_id_jnsa, $selected_payment_amount_jnsa);
				if (!$payment_insert_stmt_jnsa->execute()) {
					throw new Exception('Unable to save payment record.');
				}
				$payment_insert_stmt_jnsa->close();

				$loan_update_stmt_jnsa = $conn->prepare("UPDATE loan_jnsa SET outstanding_balance_jnsa = outstanding_balance_jnsa - ? WHERE loan_id_jnsa = ?");
				if (!$loan_update_stmt_jnsa) {
					throw new Exception('Unable to prepare balance update.');
				}
				$loan_update_stmt_jnsa->bind_param('di', $selected_payment_amount_jnsa, $selected_loan_id_jnsa);
				if (!$loan_update_stmt_jnsa->execute()) {
					throw new Exception('Unable to update outstanding balance.');
				}
				$loan_update_stmt_jnsa->close();

				$payment_action_jnsa = 'Made payment of P' . number_format($selected_payment_amount_jnsa, 2, '.', '');
				$log_stmt_jnsa = $conn->prepare("INSERT INTO loan_logs_jnsa (member_id_jnsa, action_jnsa, datetime_jnsa) VALUES (?, CONCAT('Made payment of P', ?), NOW())");
				if (!$log_stmt_jnsa) {
					throw new Exception('Unable to prepare payment log.');
				}
				$log_stmt_jnsa->bind_param('id', $member_id_jnsa, $selected_payment_amount_jnsa);
				if (!$log_stmt_jnsa->execute()) {
					throw new Exception('Unable to save payment log.');
				}
				$log_stmt_jnsa->close();

				$conn->commit();
				$_SESSION['payment_success_jnsa'] = 'Your payment was processed successfully.';
				header('Location: memberloanstatus_jnsa.php?payment_success=1');
				exit();
			} catch (Throwable $throwable_jnsa) {
				$conn->rollback();
				$error_message_jnsa = 'Unable to process your payment right now. Please try again.';
			}
		}
	}
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
	<meta charset="UTF-8">
	<meta name="viewport" content="width=device-width, initial-scale=1.0">
	<title>Member Payment</title>
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
					<a href="memberloanstatus_jnsa.php" style="display:flex; align-items:center; gap:14px; height:48px; border-radius:8px; padding:0 16px; margin-bottom:10px; color:#cbd5e1; text-decoration:none; font-size:14px; font-weight:500;">View Loan Status</a>
					<a href="memberpayment_jnsa.php" style="display:flex; align-items:center; gap:14px; height:48px; border-radius:8px; padding:0 16px; background:rgba(168,35,41,0.16); color:#ffffff; text-decoration:none; font-size:14px; font-weight:600; border:1px solid rgba(168,35,41,0.28); box-shadow:inset 0 1px 0 rgba(255,255,255,0.03);">Make Payment</a>
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
					<div style="font-size:26px; font-weight:600; color:#212529; letter-spacing:-0.2px; margin-bottom:9px;">Make a Payment</div>
					<div style="font-size:14px; color:#495057;">Select an active loan and post a payment safely.</div>
				</div>

				<?php if ($error_message_jnsa !== ''): ?>
					<div style="margin-bottom:18px; padding:14px 16px; border-radius:8px; background:rgba(168,35,41,0.08); border:1px solid rgba(168,35,41,0.18); color:#a82329; font-weight:600;">
						<?php echo htmlspecialchars($error_message_jnsa); ?>
					</div>
				<?php endif; ?>

				<div style="max-width:760px;">
					<form method="post" action="memberpayment_jnsa.php" style="background:#ffffff; border:1px solid #e5e7eb; border-radius:12px; box-shadow:0 10px 30px rgba(15,23,42,0.08); padding:22px;">
						<div class="row g-3">
							<div class="col-12">
								<label for="loan_id_jnsa" style="display:block; margin-bottom:8px; color:#212529; font-size:13px; font-weight:700;">Active Loan</label>
								<select id="loan_id_jnsa" name="loan_id_jnsa" style="width:100%; height:44px; border-radius:8px; background:#ffffff; color:#212529; border:1px solid #d1d5db; padding:0 12px; outline:none;">
									<option value="">Select active loan</option>
									<?php foreach ($active_loans_jnsa as $active_loan_row_jnsa): ?>
										<option value="<?php echo intval($active_loan_row_jnsa['loan_id_jnsa']); ?>" <?php echo ((int)$selected_loan_id_jnsa === (int)$active_loan_row_jnsa['loan_id_jnsa']) ? 'selected' : ''; ?>>Loan #<?php echo intval($active_loan_row_jnsa['loan_id_jnsa']); ?> - Balance: <?php echo '$' . number_format((float) $active_loan_row_jnsa['outstanding_balance_jnsa'], 2); ?></option>
									<?php endforeach; ?>
								</select>
							</div>

							<div class="col-12 col-md-6">
								<label for="payment_amount_jnsa" style="display:block; margin-bottom:8px; color:#212529; font-size:13px; font-weight:700;">Payment Amount</label>
								<input type="number" step="0.01" min="0" id="payment_amount_jnsa" name="payment_amount_jnsa" value="<?php echo htmlspecialchars((string)$selected_payment_amount_jnsa); ?>" style="width:100%; height:44px; border-radius:8px; background:#ffffff; color:#212529; border:1px solid #d1d5db; padding:0 12px; outline:none;">
							</div>

							<div class="col-12">
								<div style="padding:14px 16px; border-radius:8px; background:#f8f9fa; border:1px solid #e5e7eb; color:#495057; font-size:13px; line-height:1.6;">
									Payments are posted in a single database transaction. The loan balance and system log are updated together.
								</div>
							</div>

							<div class="col-12" style="padding-top:4px;">
								<button type="submit" style="height:46px; min-width:180px; border:none; border-radius:8px; background:#a82329; color:#ffffff; font-size:14px; font-weight:700; padding:0 18px; box-shadow:0 6px 18px rgba(168,35,41,0.28);">Submit Payment</button>
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
