<?php
session_start();

// Normalize the employee session and gate 
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
$loan_types_nav_active_jnsa = ($current_page_jnsa === 'employeeloantypes_jnsa.php');

require_once "Naval_FinalsActivity3_DB.php";

// scalar helper for the loan-type summary
function dashboard_scalar(mysqli $conn, string $sql, $default = 0) {
	$result = $conn->query($sql);
	if (!$result) {
		return $default;
	}
	$row = $result->fetch_row();
	return $row[0] ?? $default;
}

$success_message_jnsa = '';
$error_message_jnsa = '';
$edit_loan_type_jnsa = null;

if (isset($_SESSION['loan_type_success_jnsa'])) {
	$success_message_jnsa = $_SESSION['loan_type_success_jnsa'];
	unset($_SESSION['loan_type_success_jnsa']);
} elseif (isset($_GET['success']) && $_GET['success'] == '1') {
	$success_message_jnsa = 'Loan type created successfully.';
}

if (isset($_GET['edit_jnsa'])) {
	$edit_loan_type_id_jnsa = (int) $_GET['edit_jnsa'];
	if ($edit_loan_type_id_jnsa > 0) {
		$edit_stmt_jnsa = $conn->prepare("SELECT loan_type_id_jnsa, loan_type_name_jnsa, description_jnsa FROM loan_type_jnsa WHERE loan_type_id_jnsa = ? LIMIT 1");
		if ($edit_stmt_jnsa) {
			$edit_stmt_jnsa->bind_param('i', $edit_loan_type_id_jnsa);
			$edit_stmt_jnsa->execute();
			$edit_result_jnsa = $edit_stmt_jnsa->get_result();
			$edit_loan_type_jnsa = $edit_result_jnsa ? $edit_result_jnsa->fetch_assoc() : null;
			$edit_stmt_jnsa->close();
		}
		if (!$edit_loan_type_jnsa) {
			$error_message_jnsa = 'Unable to find the selected loan type.';
		}
	}
}

if (isset($_GET['delete_jnsa'])) {
	$delete_loan_type_id_jnsa = (int) $_GET['delete_jnsa'];
	if ($delete_loan_type_id_jnsa > 0) {
		$delete_stmt_jnsa = $conn->prepare("DELETE FROM loan_type_jnsa WHERE loan_type_id_jnsa = ?");
		if ($delete_stmt_jnsa) {
			$delete_stmt_jnsa->bind_param('i', $delete_loan_type_id_jnsa);
			if ($delete_stmt_jnsa->execute()) {
				$_SESSION['loan_type_success_jnsa'] = 'Loan type deleted successfully.';
				header('Location: employeeloantypes_jnsa.php');
				exit();
			}
			$error_message_jnsa = 'Unable to delete the loan type right now.';
			$delete_stmt_jnsa->close();
		}
	}
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['save_loan_type_jnsa'])) {
	$loan_type_id_jnsa = isset($_POST['loan_type_id_jnsa']) ? (int) $_POST['loan_type_id_jnsa'] : 0;
	$loan_type_name_jnsa = trim((string) ($_POST['loan_type_name_jnsa'] ?? ''));
	$description_jnsa = trim((string) ($_POST['description_jnsa'] ?? ''));

	if ($loan_type_name_jnsa === '') {
		$error_message_jnsa = 'Please enter a loan type name.';
	} else {
		if ($loan_type_id_jnsa > 0) {
			$update_stmt_jnsa = $conn->prepare("UPDATE loan_type_jnsa SET loan_type_name_jnsa = ?, description_jnsa = ? WHERE loan_type_id_jnsa = ?");
			if (!$update_stmt_jnsa) {
				$error_message_jnsa = 'Unable to prepare the loan type update.';
			} else {
				$update_stmt_jnsa->bind_param('ssi', $loan_type_name_jnsa, $description_jnsa, $loan_type_id_jnsa);
				if ($update_stmt_jnsa->execute()) {
					$_SESSION['loan_type_success_jnsa'] = 'Loan type updated successfully.';
					header('Location: employeeloantypes_jnsa.php');
					exit();
				}
				$error_message_jnsa = 'Unable to update the loan type right now. Please try again.';
				$update_stmt_jnsa->close();
			}
		} else {
			$insert_stmt_jnsa = $conn->prepare("INSERT INTO loan_type_jnsa (loan_type_name_jnsa, description_jnsa) VALUES (?, ?)");
			if (!$insert_stmt_jnsa) {
				$error_message_jnsa = 'Unable to prepare the loan type insert.';
			} else {
				$insert_stmt_jnsa->bind_param('ss', $loan_type_name_jnsa, $description_jnsa);
				if ($insert_stmt_jnsa->execute()) {
					$_SESSION['loan_type_success_jnsa'] = 'Loan type added successfully.';
					header('Location: employeeloantypes_jnsa.php?success=1');
					exit();
				}
				$error_message_jnsa = 'Unable to add the loan type right now. Please try again.';
				$insert_stmt_jnsa->close();
			}
		}
	}
}

// Load current loan type catalog and count
$loan_types_jnsa = [];
$loan_types_query_jnsa = $conn->query("SELECT loan_type_id_jnsa, loan_type_name_jnsa, description_jnsa FROM loan_type_jnsa ORDER BY loan_type_name_jnsa ASC");
if ($loan_types_query_jnsa && $loan_types_query_jnsa->num_rows > 0) {
	while ($loan_type_row_jnsa = $loan_types_query_jnsa->fetch_assoc()) {
		$loan_types_jnsa[] = $loan_type_row_jnsa;
	}
}

$loan_types_count_jnsa = (int) dashboard_scalar($conn, "SELECT COUNT(*) FROM loan_type_jnsa");
?>
<!DOCTYPE html>
<html lang="en">
<head>
	<meta charset="UTF-8">
	<meta name="viewport" content="width=device-width, initial-scale=1.0">
	<title>Employee Loan Types</title>
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
					<a href="employeeloans_jnsa.php" style="display:flex; align-items:center; gap:14px; height:48px; border-radius:8px; padding:0 16px; margin-bottom:10px; color:#cbd5e1; text-decoration:none; font-size:14px; font-weight:500;">Loans</a>
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
					<div style="font-size:26px; font-weight:600; color:#212529; letter-spacing:-0.2px; margin-bottom:9px;">Loan Types</div>
					<div style="font-size:14px; color:#495057;">Create and review the loan categories available to members.</div>
				</div>

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

				<!-- Form Card -->
				<div style="max-width:860px; margin-bottom:24px;">
					<div style="background:#ffffff; border:1px solid #e5e7eb; border-radius:12px; box-shadow:0 10px 30px rgba(15,23,42,0.08); padding:22px;">
							<div style="font-size:16px; font-weight:700; color:#212529; margin-bottom:16px;"><?php echo $edit_loan_type_jnsa ? 'Edit Loan Type' : 'Add New Loan Type'; ?></div>
						<form method="post" action="employeeloantypes_jnsa.php">
								<input type="hidden" name="loan_type_id_jnsa" value="<?php echo (int) ($edit_loan_type_jnsa['loan_type_id_jnsa'] ?? 0); ?>">
							<div class="row g-3">
								<div class="col-12 col-md-5">
									<label for="loan_type_name_jnsa" style="display:block; margin-bottom:8px; color:#212529; font-size:13px; font-weight:700;">Loan Type Name</label>
										<input type="text" id="loan_type_name_jnsa" name="loan_type_name_jnsa" value="<?php echo htmlspecialchars($edit_loan_type_jnsa['loan_type_name_jnsa'] ?? ''); ?>" style="width:100%; height:44px; border-radius:8px; background:#ffffff; color:#212529; border:1px solid #d1d5db; padding:0 12px; outline:none;">
								</div>
								<div class="col-12 col-md-7">
									<label for="description_jnsa" style="display:block; margin-bottom:8px; color:#212529; font-size:13px; font-weight:700;">Description</label>
										<input type="text" id="description_jnsa" name="description_jnsa" value="<?php echo htmlspecialchars($edit_loan_type_jnsa['description_jnsa'] ?? ''); ?>" style="width:100%; height:44px; border-radius:8px; background:#ffffff; color:#212529; border:1px solid #d1d5db; padding:0 12px; outline:none;">
								</div>
								<div class="col-12">
										<button type="submit" name="save_loan_type_jnsa" value="1" style="height:46px; min-width:180px; border:none; border-radius:8px; background:#a82329; color:#ffffff; font-size:14px; font-weight:700; padding:0 18px; box-shadow:0 6px 18px rgba(168,35,41,0.28);"><?php echo $edit_loan_type_jnsa ? 'Update Loan Type' : 'Add Loan Type'; ?></button>
										<?php if ($edit_loan_type_jnsa): ?>
											<a href="employeeloantypes_jnsa.php" style="display:inline-flex; align-items:center; justify-content:center; height:46px; min-width:140px; margin-left:10px; border-radius:8px; border:1px solid #d1d5db; background:#ffffff; color:#374151; text-decoration:none; font-size:14px; font-weight:700; padding:0 18px;">Cancel</a>
										<?php endif; ?>
								</div>
							</div>
						</form>
					</div>
				</div>

				<!-- loan types list -->
				<div style="background:#ffffff; border:1px solid #e5e7eb; border-radius:12px; box-shadow:0 10px 30px rgba(15,23,42,0.08); padding:18px; overflow-x:auto;">
					<table style="width:100%; border-collapse:collapse; min-width:760px;">
						<thead>
							<tr style="text-align:left; color:#495057; font-size:12px; letter-spacing:0.2px; text-transform:uppercase;">
								<th style="padding:12px 10px; border-bottom:1px solid #e5e7eb;">Loan Type Name</th>
								<th style="padding:12px 10px; border-bottom:1px solid #e5e7eb;">Description</th>
								<th style="padding:12px 10px; border-bottom:1px solid #e5e7eb;">Actions</th>
							</tr>
						</thead>
						<tbody>
							<?php if (empty($loan_types_jnsa)): ?>
								<tr>
									<td colspan="3" style="padding:18px 10px; color:#495057;">No loan types found.</td>
								</tr>
							<?php else: ?>
								<?php foreach ($loan_types_jnsa as $loan_type_row_jnsa): ?>
									<tr style="border-bottom:1px solid #f1f3f5; color:#212529;">
										<td style="padding:14px 10px;"><?php echo htmlspecialchars($loan_type_row_jnsa['loan_type_name_jnsa']); ?></td>
										<td style="padding:14px 10px;"><?php echo htmlspecialchars($loan_type_row_jnsa['description_jnsa'] ?? ''); ?></td>
										<td style="padding:14px 10px; white-space:nowrap;">
											<a href="employeeloantypes_jnsa.php?edit_jnsa=<?php echo (int) $loan_type_row_jnsa['loan_type_id_jnsa']; ?>" style="margin-right:12px; color:#a82329; text-decoration:none; font-weight:700;">Edit</a>
											<a href="employeeloantypes_jnsa.php?delete_jnsa=<?php echo (int) $loan_type_row_jnsa['loan_type_id_jnsa']; ?>" onclick="return confirm('Delete this loan type?');" style="color:#374151; text-decoration:none; font-weight:700;">Delete</a>
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
