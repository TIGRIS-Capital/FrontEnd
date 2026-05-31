<?php
session_start();
require_once "Naval_FinalsActivity3_DB.php";

$admin_username_jnsa = $_SESSION['username'] ?? 'admin01';
$admin_role_jnsa = $_SESSION['user_type'] ?? 'Admin';
$admin_notice_jnsa = '';
$admin_error_jnsa = '';

function admin_safe_value_jnsa($value_jnsa) {
	return htmlspecialchars((string) $value_jnsa, ENT_QUOTES, 'UTF-8');
}

function admin_redirect_jnsa($query_jnsa = []) {
	$target_jnsa = 'adminmembers_jnsa.php';
	if (!empty($query_jnsa)) {
		$target_jnsa .= '?' . http_build_query($query_jnsa);
	}
	header('Location: ' . $target_jnsa);
	exit();
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['save_member_jnsa'])) {
	// Handle create and update submissions accounts
	$member_id_jnsa = isset($_POST['member_id_jnsa']) ? (int) $_POST['member_id_jnsa'] : 0;
	$member_name_jnsa = trim($_POST['member_name_jnsa'] ?? '');
	$username_jnsa = trim($_POST['username_jnsa'] ?? '');
	$user_type_jnsa = trim($_POST['user_type_jnsa'] ?? 'member');
	$email_jnsa = trim($_POST['email_jnsa'] ?? '');
	$user_status_jnsa = trim($_POST['user_status_jnsa'] ?? 'Active');
	$password_jnsa = (string) ($_POST['password_jnsa'] ?? '');

	if ($member_name_jnsa === '' || $username_jnsa === '' || $user_type_jnsa === '' || $email_jnsa === '' || $user_status_jnsa === '') {
		$admin_error_jnsa = 'Please complete every required field.';
	} elseif ($member_id_jnsa <= 0 && $password_jnsa === '') {
		$admin_error_jnsa = 'Password is required when creating a new user.';
	} else {
		if ($member_id_jnsa > 0) {
			$check_stmt_jnsa = $conn->prepare("SELECT member_id_jnsa FROM loan_member_jnsa WHERE member_id_jnsa = ? LIMIT 1");
			$check_stmt_jnsa->bind_param('i', $member_id_jnsa);
			$check_stmt_jnsa->execute();
			$check_result_jnsa = $check_stmt_jnsa->get_result();
			$existing_member_jnsa = $check_result_jnsa ? $check_result_jnsa->fetch_assoc() : null;
			$check_stmt_jnsa->close();

			if (!$existing_member_jnsa) {
				$admin_error_jnsa = 'The selected member record no longer exists.';
			} else {
				if ($password_jnsa !== '') {
					$password_hash_jnsa = password_hash($password_jnsa, PASSWORD_DEFAULT);
					$update_stmt_jnsa = $conn->prepare("UPDATE loan_member_jnsa SET member_name_jnsa = ?, username_jnsa = ?, user_type_jnsa = ?, email_jnsa = ?, user_status_jnsa = ?, password_jnsa = ? WHERE member_id_jnsa = ?");
					$update_stmt_jnsa->bind_param('ssssssi', $member_name_jnsa, $username_jnsa, $user_type_jnsa, $email_jnsa, $user_status_jnsa, $password_hash_jnsa, $member_id_jnsa);
				} else {
					$update_stmt_jnsa = $conn->prepare("UPDATE loan_member_jnsa SET member_name_jnsa = ?, username_jnsa = ?, user_type_jnsa = ?, email_jnsa = ?, user_status_jnsa = ? WHERE member_id_jnsa = ?");
					$update_stmt_jnsa->bind_param('sssssi', $member_name_jnsa, $username_jnsa, $user_type_jnsa, $email_jnsa, $user_status_jnsa, $member_id_jnsa);
				}

				if ($update_stmt_jnsa->execute()) {
					$_SESSION['adminmembers_notice_jnsa'] = 'User account updated successfully.';
					admin_redirect_jnsa();
				}

				$admin_error_jnsa = 'Unable to update the user account.';
				$update_stmt_jnsa->close();
			}
		} else {
			$password_hash_jnsa = password_hash($password_jnsa, PASSWORD_DEFAULT);
			$insert_stmt_jnsa = $conn->prepare("INSERT INTO loan_member_jnsa (member_name_jnsa, username_jnsa, user_type_jnsa, email_jnsa, user_status_jnsa, password_jnsa) VALUES (?, ?, ?, ?, ?, ?)");
			$insert_stmt_jnsa->bind_param('ssssss', $member_name_jnsa, $username_jnsa, $user_type_jnsa, $email_jnsa, $user_status_jnsa, $password_hash_jnsa);

			if ($insert_stmt_jnsa->execute()) {
				$_SESSION['adminmembers_notice_jnsa'] = 'New user created successfully.';
				admin_redirect_jnsa();
			}

			$admin_error_jnsa = 'Unable to create the user account.';
			$insert_stmt_jnsa->close();
		}
	}
}

if (isset($_GET['toggle_jnsa'])) {
	// Toggle active and inactive state
	$toggle_member_id_jnsa = (int) $_GET['toggle_jnsa'];
	if ($toggle_member_id_jnsa > 0) {
		$toggle_stmt_jnsa = $conn->prepare("UPDATE loan_member_jnsa SET user_status_jnsa = CASE WHEN user_status_jnsa = 'Active' THEN 'Inactive' ELSE 'Active' END WHERE member_id_jnsa = ?");
		$toggle_stmt_jnsa->bind_param('i', $toggle_member_id_jnsa);
		$toggle_stmt_jnsa->execute();
		$toggle_stmt_jnsa->close();
		$_SESSION['adminmembers_notice_jnsa'] = 'User status updated.';
	}
	admin_redirect_jnsa();
}

if (isset($_SESSION['adminmembers_notice_jnsa'])) {
	$admin_notice_jnsa = $_SESSION['adminmembers_notice_jnsa'];
	unset($_SESSION['adminmembers_notice_jnsa']);
}

// Load selected record
$edit_member_id_jnsa = isset($_GET['edit_jnsa']) ? (int) $_GET['edit_jnsa'] : 0;
$edit_member_jnsa = null;
if ($edit_member_id_jnsa > 0) {
	$edit_stmt_jnsa = $conn->prepare("SELECT member_id_jnsa, member_name_jnsa, username_jnsa, user_type_jnsa, email_jnsa, user_status_jnsa FROM loan_member_jnsa WHERE member_id_jnsa = ? LIMIT 1");
	$edit_stmt_jnsa->bind_param('i', $edit_member_id_jnsa);
	$edit_stmt_jnsa->execute();
	$edit_result_jnsa = $edit_stmt_jnsa->get_result();
	$edit_member_jnsa = $edit_result_jnsa ? $edit_result_jnsa->fetch_assoc() : null;
	$edit_stmt_jnsa->close();
	if (!$edit_member_jnsa) {
		$admin_error_jnsa = 'Unable to find the selected user.';
	}
}

// Load member list
//  compute the summary counts
$member_rows_jnsa = [];
$member_query_jnsa = $conn->query("SELECT member_id_jnsa, member_name_jnsa, username_jnsa, user_type_jnsa, email_jnsa, user_status_jnsa FROM loan_member_jnsa ORDER BY member_id_jnsa DESC");
if ($member_query_jnsa) {
	while ($member_row_jnsa = $member_query_jnsa->fetch_assoc()) {
		$member_rows_jnsa[] = $member_row_jnsa;
	}
}

$total_members_jnsa = count($member_rows_jnsa);
$active_members_jnsa = 0;
$inactive_members_jnsa = 0;
foreach ($member_rows_jnsa as $member_row_jnsa) {
	if (($member_row_jnsa['user_status_jnsa'] ?? '') === 'Active') {
		$active_members_jnsa++;
	} else {
		$inactive_members_jnsa++;
	}
}
?>
<!doctype html>
<html lang="en">
<head>
	<meta charset="utf-8">
	<meta name="viewport" content="width=device-width, initial-scale=1">
	<title>Admin Members - TIGRIS Capital</title>
	<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body style="margin:0; background:#f4f5f7; color:#1f2937; font-family: Arial, Helvetica, sans-serif; overflow-x:hidden;">
	<div style="min-height:100vh; display:flex; background:#f4f5f7;">
		<!-- sideb... -->
		<aside style="width:240px; background:#121416; border-right:1px solid rgba(226,232,240,0.08); display:flex; flex-direction:column; justify-content:space-between; box-shadow:0 0 0 1px rgba(0,0,0,0.08);">
			<div>
				<div style="height:69px; display:flex; align-items:center; gap:12px; padding:0 18px; border-bottom:1px solid rgba(226,232,240,0.08);">
					<div style="width:36px; height:36px; border-radius:6px; overflow:hidden;"><img src="Tigris_Logo_NoText.png" alt="logo" style="width:100%;height:100%;object-fit:cover;"></div>
					<div style="line-height:1.05;"><div style="font-size:16px; font-weight:700; color:#e2e8f0;">Admin Portal</div><div style="font-size:11px; color:#94a3b8; margin-top:2px;">System Admin</div></div>
				</div>
				<nav style="padding:18px 12px 0 12px;">
					<a href="admindashboard_jn.php" style="display:flex; align-items:center; gap:14px; height:48px; padding:0 16px; margin-bottom:10px; color:#cbd5e1; text-decoration:none; font-size:14px; font-weight:500; border-radius:8px;">Overview</a>
					<a href="adminmembers_jnsa.php" style="display:flex; align-items:center; gap:14px; height:48px; padding:0 16px; margin-bottom:10px; background:rgba(168,35,41,0.16); color:#ffffff; text-decoration:none; font-size:14px; font-weight:600; border-radius:8px; border:1px solid rgba(168,35,41,0.28);">Members</a>
					<a href="adminloans_jnsa.php" style="display:flex; align-items:center; gap:14px; height:48px; padding:0 16px; margin-bottom:10px; color:#cbd5e1; text-decoration:none; font-size:14px; font-weight:500;">Loans</a>
					<a href="adminreports_jnsa.php" style="display:flex; align-items:center; gap:14px; height:48px; padding:0 16px; color:#cbd5e1; text-decoration:none; font-size:14px; font-weight:500;">Reports</a>
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
					<div style="font-size:19px; font-weight:600;">User Management</div>
					<div style="font-size:12px; color:#cbd5e1; margin-top:6px;">Welcome back, <?php echo admin_safe_value_jnsa($admin_username_jnsa); ?></div>
				</div>
				<div style="display:flex; align-items:center; gap:18px;">
					<div style="width:26px; height:26px; border-radius:50%; background:#a82329; display:flex; align-items:center; justify-content:center; color:#fff;">A</div>
					<div style="line-height:1.05; text-align:right;">
						<div style="font-size:12px; font-weight:700; color:#ffffff;"><?php echo admin_safe_value_jnsa($admin_username_jnsa); ?></div>
						<div style="font-size:11px; color:#cbd5e1; margin-top:4px;"><?php echo admin_safe_value_jnsa($admin_role_jnsa); ?></div>
					</div>
				</div>
			</header>

			<!-- main... -->
			<section style="padding:28px 18px 18px 18px; background:#f4f5f7; flex:1;">
				<div style="margin-bottom:18px;">
					<div style="font-size:26px; font-weight:600; color:#1f2937; letter-spacing:-0.2px; margin-bottom:9px;">Admin Members</div>
					<div style="font-size:14px; color:#6b7280;">Create, update, and manage all user accounts from one screen.</div>
				</div>

				<?php if ($admin_notice_jnsa !== ''): ?>
					<div style="margin-bottom:16px; padding:12px 14px; background:rgba(16,185,129,0.08); border:1px solid rgba(16,185,129,0.18); color:#047857; border-radius:8px; font-size:14px;">
						<?php echo admin_safe_value_jnsa($admin_notice_jnsa); ?>
					</div>
				<?php endif; ?>

				<?php if ($admin_error_jnsa !== ''): ?>
					<div style="margin-bottom:16px; padding:12px 14px; background:rgba(168,35,41,0.08); border:1px solid rgba(168,35,41,0.18); color:#991b1b; border-radius:8px; font-size:14px;">
						<?php echo admin_safe_value_jnsa($admin_error_jnsa); ?>
					</div>
				<?php endif; ?>

				<!-- kpi... -->
				<div class="row gx-4 gy-4" style="margin:0 0 24px 0;">
					<div class="col-12 col-md-4 px-2">
						<div style="height:110px; background:#ffffff; border:1px solid #e5e7eb; border-radius:10px; box-shadow:0 2px 10px rgba(15,23,42,0.06); padding:14px 16px; display:flex; justify-content:space-between; align-items:flex-start;">
							<div>
								<div style="font-size:13px; color:#212529; font-weight:500;">Total Members</div>
								<div style="font-size:22px; font-weight:700; color:#212529; margin-top:6px;"><?php echo (int) $total_members_jnsa; ?></div>
							</div>
							<div style="width:42px; height:42px; border-radius:10px; background:rgba(168,35,41,0.08); display:flex; align-items:center; justify-content:center; color:#a82329; flex:0 0 auto;">👥</div>
						</div>
					</div>
					<div class="col-12 col-md-4 px-2">
						<div style="height:110px; background:#ffffff; border:1px solid #e5e7eb; border-radius:10px; box-shadow:0 2px 10px rgba(15,23,42,0.06); padding:14px 16px; display:flex; justify-content:space-between; align-items:flex-start;">
							<div>
								<div style="font-size:13px; color:#212529; font-weight:500;">Active Users</div>
								<div style="font-size:22px; font-weight:700; color:#212529; margin-top:6px;"><?php echo (int) $active_members_jnsa; ?></div>
							</div>
							<div style="width:42px; height:42px; border-radius:10px; background:rgba(16,185,129,0.08); display:flex; align-items:center; justify-content:center; color:#10b981; flex:0 0 auto;">✓</div>
						</div>
					</div>
					<div class="col-12 col-md-4 px-2">
						<div style="height:110px; background:#ffffff; border:1px solid #e5e7eb; border-radius:10px; box-shadow:0 2px 10px rgba(15,23,42,0.06); padding:14px 16px; display:flex; justify-content:space-between; align-items:flex-start;">
							<div>
								<div style="font-size:13px; color:#212529; font-weight:500;">Inactive Users</div>
								<div style="font-size:22px; font-weight:700; color:#212529; margin-top:6px;"><?php echo (int) $inactive_members_jnsa; ?></div>
							</div>
							<div style="width:42px; height:42px; border-radius:10px; background:rgba(249,115,22,0.08); display:flex; align-items:center; justify-content:center; color:#f97316; flex:0 0 auto;">⏸</div>
						</div>
					</div>
				</div>

				<!-- form... -->
				<div class="row g-3" style="margin:0 0 20px 0;">
					<div class="col-12 col-lg-12 px-2">
						<div style="background:#ffffff; border:1px solid #e5e7eb; border-radius:10px; box-shadow:0 1px 4px rgba(16,24,40,.04); padding:16px;">
							<div style="font-size:14px; font-weight:700; color:#212529; margin-bottom:10px;"><?php echo $edit_member_jnsa ? 'Edit User Account' : 'Add User Account'; ?></div>
							<form method="post" style="display:flex; flex-wrap:wrap; gap:12px;">
								<input type="hidden" name="member_id_jnsa" value="<?php echo (int) ($edit_member_jnsa['member_id_jnsa'] ?? 0); ?>">
								<div style="flex:1 1 220px; min-width:220px;">
									<label style="display:block; font-size:12px; color:#6b7280; margin-bottom:6px;">Full Name</label>
									<input type="text" name="member_name_jnsa" value="<?php echo admin_safe_value_jnsa($edit_member_jnsa['member_name_jnsa'] ?? ''); ?>" required style="width:100%; padding:10px 12px; border:1px solid #d1d5db; border-radius:8px; background:#fff;">
								</div>
								<div style="flex:1 1 220px; min-width:220px;">
									<label style="display:block; font-size:12px; color:#6b7280; margin-bottom:6px;">Username</label>
									<input type="text" name="username_jnsa" value="<?php echo admin_safe_value_jnsa($edit_member_jnsa['username_jnsa'] ?? ''); ?>" required style="width:100%; padding:10px 12px; border:1px solid #d1d5db; border-radius:8px; background:#fff;">
								</div>
								<div style="flex:1 1 180px; min-width:180px;">
									<label style="display:block; font-size:12px; color:#6b7280; margin-bottom:6px;">Role Type</label>
									<select name="user_type_jnsa" required style="width:100%; padding:10px 12px; border:1px solid #d1d5db; border-radius:8px; background:#fff;">
										<?php $current_type_jnsa = $edit_member_jnsa['user_type_jnsa'] ?? 'member'; ?>
										<option value="member" <?php echo $current_type_jnsa === 'member' ? 'selected' : ''; ?>>member</option>
										<option value="Employee" <?php echo $current_type_jnsa === 'Employee' ? 'selected' : ''; ?>>Employee</option>
										<option value="Admin" <?php echo $current_type_jnsa === 'Admin' ? 'selected' : ''; ?>>Admin</option>
									</select>
								</div>
								<div style="flex:1 1 240px; min-width:240px;">
									<label style="display:block; font-size:12px; color:#6b7280; margin-bottom:6px;">Email</label>
									<input type="email" name="email_jnsa" value="<?php echo admin_safe_value_jnsa($edit_member_jnsa['email_jnsa'] ?? ''); ?>" required style="width:100%; padding:10px 12px; border:1px solid #d1d5db; border-radius:8px; background:#fff;">
								</div>
								<div style="flex:1 1 180px; min-width:180px;">
									<label style="display:block; font-size:12px; color:#6b7280; margin-bottom:6px;">Status</label>
									<select name="user_status_jnsa" required style="width:100%; padding:10px 12px; border:1px solid #d1d5db; border-radius:8px; background:#fff;">
										<?php $current_status_jnsa = $edit_member_jnsa['user_status_jnsa'] ?? 'Active'; ?>
										<option value="Active" <?php echo $current_status_jnsa === 'Active' ? 'selected' : ''; ?>>Active</option>
										<option value="Inactive" <?php echo $current_status_jnsa === 'Inactive' ? 'selected' : ''; ?>>Inactive</option>
									</select>
								</div>
								<div style="flex:1 1 240px; min-width:240px;">
									<label style="display:block; font-size:12px; color:#6b7280; margin-bottom:6px;">Password <?php echo $edit_member_jnsa ? '(leave blank to keep existing)' : ''; ?></label>
									<input type="password" name="password_jnsa" <?php echo $edit_member_jnsa ? '' : 'required'; ?> style="width:100%; padding:10px 12px; border:1px solid #d1d5db; border-radius:8px; background:#fff;">
								</div>
								<div style="width:100%; display:flex; gap:10px; align-items:center; margin-top:4px;">
									<button type="submit" name="save_member_jnsa" value="1" style="padding:10px 16px; border:none; border-radius:8px; background:#a82329; color:#fff; font-weight:700;">Save User</button>
									<?php if ($edit_member_jnsa): ?>
										<a href="adminmembers_jnsa.php" style="padding:10px 16px; border-radius:8px; border:1px solid #d1d5db; color:#374151; text-decoration:none; background:#fff;">Cancel Edit</a>
									<?php endif; ?>
								</div>
							</form>
						</div>
					</div>
				</div>

				<!-- list... -->
				<div style="background:#ffffff; border:1px solid #e5e7eb; border-radius:10px; box-shadow:0 1px 4px rgba(16,24,40,.04); padding:16px;">
					<div style="display:flex; justify-content:space-between; align-items:center; margin-bottom:14px; gap:12px; flex-wrap:wrap;">
						<div>
							<div style="font-size:14px; font-weight:700; color:#212529;">All Users</div>
							<div style="font-size:12px; color:#6b7280; margin-top:4px;">Review accounts and manage status from the table below.</div>
						</div>
					</div>

					<div style="overflow-x:auto;">
						<table style="width:100%; border-collapse:collapse; font-size:13px; min-width:920px;">
							<thead>
								<tr style="text-align:left; color:#495057; font-size:12px; letter-spacing:0.2px; text-transform:uppercase;">
									<th style="padding:12px 10px; border-bottom:1px solid #e5e7eb;">User ID</th>
									<th style="padding:12px 10px; border-bottom:1px solid #e5e7eb;">Name</th>
									<th style="padding:12px 10px; border-bottom:1px solid #e5e7eb;">Username</th>
									<th style="padding:12px 10px; border-bottom:1px solid #e5e7eb;">Role Type</th>
									<th style="padding:12px 10px; border-bottom:1px solid #e5e7eb;">Email</th>
									<th style="padding:12px 10px; border-bottom:1px solid #e5e7eb;">Status</th>
									<th style="padding:12px 10px; border-bottom:1px solid #e5e7eb;">Actions</th>
								</tr>
							</thead>
							<tbody>
								<?php if (empty($member_rows_jnsa)): ?>
									<tr>
										<td colspan="7" style="padding:18px 10px; color:#6b7280;">No user records found.</td>
									</tr>
								<?php else: ?>
									<?php foreach ($member_rows_jnsa as $member_row_jnsa): ?>
										<tr style="border-top:1px solid #f1f5f9; color:#212529;">
											<td style="padding:12px 10px;"><?php echo (int) $member_row_jnsa['member_id_jnsa']; ?></td>
											<td style="padding:12px 10px;"><?php echo admin_safe_value_jnsa($member_row_jnsa['member_name_jnsa']); ?></td>
											<td style="padding:12px 10px;"><?php echo admin_safe_value_jnsa($member_row_jnsa['username_jnsa']); ?></td>
											<td style="padding:12px 10px;"><?php echo admin_safe_value_jnsa($member_row_jnsa['user_type_jnsa']); ?></td>
											<td style="padding:12px 10px;"><?php echo admin_safe_value_jnsa($member_row_jnsa['email_jnsa']); ?></td>
											<td style="padding:12px 10px;">
												<?php if (($member_row_jnsa['user_status_jnsa'] ?? '') === 'Active'): ?>
													<span style="display:inline-flex; align-items:center; padding:4px 10px; border-radius:999px; background:rgba(16,185,129,0.12); color:#047857; font-size:12px; font-weight:700;">Active</span>
												<?php else: ?>
													<span style="display:inline-flex; align-items:center; padding:4px 10px; border-radius:999px; background:rgba(249,115,22,0.12); color:#9a3412; font-size:12px; font-weight:700;">Inactive</span>
												<?php endif; ?>
											</td>
											<td style="padding:12px 10px;">
												<div style="display:flex; gap:10px; flex-wrap:wrap;">
													<a href="adminmembers_jnsa.php?edit_jnsa=<?php echo (int) $member_row_jnsa['member_id_jnsa']; ?>" style="color:#a82329; text-decoration:none; font-weight:700;">Edit</a>
													<a href="adminmembers_jnsa.php?toggle_jnsa=<?php echo (int) $member_row_jnsa['member_id_jnsa']; ?>" onclick="return confirm('Toggle this user status?');" style="color:#374151; text-decoration:none; font-weight:700;">Toggle Status</a>
												</div>
											</td>
										</tr>
									<?php endforeach; ?>
								<?php endif; ?>
							</tbody>
						</table>
					</div>
				</div>
			</section>
		</main>
	</div>

	<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
