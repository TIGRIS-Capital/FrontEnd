<?php
session_start();
require_once "Naval_FinalsActivity3_DB.php";
$username = $_SESSION['username'] ?? 'officer01';
$userRole = $_SESSION['user_type'] ?? 'Employee';

$displaysql = "Select * from loan_member_jn";
$result = $conn->query($displaysql);
?>
<!doctype html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Members - TIGRIS Capital</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body style="margin:0; background:#f4f5f7; color:#1f2937; font-family: Arial, Helvetica, sans-serif; overflow-x:hidden;">
    <div style="min-height:100vh; display:flex; background:#f4f5f7;">
        <aside style="width:240px; background:#121416; border-right:1px solid rgba(226,232,240,0.08); display:flex; flex-direction:column; justify-content:space-between;">
            <div>
                <div style="height:69px; display:flex; align-items:center; gap:12px; padding:0 18px; border-bottom:1px solid rgba(226,232,240,0.08);">
                    <div style="width:36px; height:36px; border-radius:6px; overflow:hidden;"><img src="Tigris_Logo_NoText.png" alt="logo" style="width:100%;height:100%;object-fit:cover;"></div>
                    <div style="line-height:1.05;"><div style="font-size:16px; font-weight:700; color:#e2e8f0;">Employee Portal</div><div style="font-size:11px; color:#94a3b8; margin-top:2px;">Loan Officer</div></div>
                </div>
                <nav style="padding:18px 12px 0 12px;">
                    <a href="employeedashboard_jn.php" style="display:flex; align-items:center; gap:14px; height:48px; padding:0 16px; margin-bottom:10px; background:rgba(168,35,41,0.16); color:#ffffff; text-decoration:none; font-size:14px; font-weight:600; border-radius:8px;">Overview</a>
                    <a href="Naval_FinalsActivity3_Display.php" style="display:flex; align-items:center; gap:14px; height:48px; padding:0 16px; margin-bottom:10px; color:#cbd5e1; text-decoration:none; font-size:14px;">Members</a>
                    <a href="loans_list_jn.php" style="display:flex; align-items:center; gap:14px; height:48px; padding:0 16px; margin-bottom:10px; color:#cbd5e1; text-decoration:none; font-size:14px;">Loans</a>
                    <a href="#" style="display:flex; align-items:center; gap:14px; height:48px; padding:0 16px; color:#cbd5e1; text-decoration:none; font-size:14px;">Loan Types</a>
                </nav>
            </div>
            <div style="padding:20px 14px; border-top:1px solid rgba(226,232,240,0.08);">
                <a href="Loan_login_jn.php" style="display:flex; align-items:center; gap:12px; color:#e2e8f0; text-decoration:none; font-size:15px; font-weight:700;">↪ Logout</a>
            </div>
        </aside>

        <main style="flex:1; min-width:0; display:flex; flex-direction:column;">
            <header style="height:69px; background:#121416; border-bottom:1px solid rgba(226,232,240,0.08); display:flex; align-items:center; justify-content:space-between; padding:0 18px 0 20px; color:#fff;">
                <div>
                    <div style="font-size:19px; font-weight:600;">Members</div>
                    <div style="font-size:12px; color:#cbd5e1; margin-top:6px;">Welcome back, <?php echo htmlspecialchars($username); ?></div>
                </div>
                <div style="display:flex; align-items:center; gap:18px;"></div>
            </header>

            <section style="padding:24px; background:#ffffff; flex:1;">
                <form action="Naval_FinalsActivity3_Display.php" method="post" style="margin-bottom:18px;">
                    <div style="display:flex; gap:12px;">
                        <input type="search" name="searchinput_jnsa" placeholder="Search" class="form-control" style="border-radius:8px; border:1px solid #e5e7eb; padding:10px;">
                        <input type="submit" name="btnsearch_jnsa" value="Search" class="btn" style="background:#a82329; color:#fff; padding:10px 16px; border-radius:8px;">
                    </div>
                </form>

                <?php if ($result && $result->num_rows > 0): ?>
                    <div style="border:1px solid #e6e9ec; border-radius:8px; overflow:hidden;">
                        <table class="table mb-0">
                            <thead style="background:#fafafa;"><tr><th>Member ID</th><th>Full Name</th><th>Contact</th><th>Address</th><th>Image</th></tr></thead>
                            <tbody>
                            <?php foreach ($result as $row): ?>
                                <tr>
                                    <td><?php echo htmlspecialchars($row['member_id_jnsa']); ?></td>
                                    <td><?php echo htmlspecialchars($row['member_name_jnsa']); ?></td>
                                    <td><?php echo htmlspecialchars($row['contact_information_jnsa']); ?></td>
                                    <td><?php echo htmlspecialchars($row['address_jnsa']); ?></td>
                                    <td><img src="<?php echo htmlspecialchars($row['member_img_jnsa']); ?>" alt="Member Image" width="80" height="80"></td>
                                </tr>
                            <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                <?php else: ?>
                    <div>No record found</div>
                <?php endif; ?>
            </section>
        </main>
    </div>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>


