<?php
session_start();
require_once "Naval_FinalsActivity3_DB.php";
$username = $_SESSION['username'] ?? 'officer01';
$userRole = $_SESSION['user_type'] ?? 'Employee';

$pendingLoans = [
    ['id' => 'LN-2024-008', 'borrower' => 'Robert Wilson', 'type' => 'Personal Loan', 'amount' => '$18,750', 'term' => '36 months', 'rate' => '8.5%', 'date' => '2024-05-18', 'status'=>'Pending'],
    ['id' => 'LN-2024-007', 'borrower' => 'Emily Davis', 'type' => 'Business Loan', 'amount' => '$50,000', 'term' => '60 months', 'rate' => '10.2%', 'date' => '2024-05-17', 'status'=>'Approved'],
    ['id' => 'LN-2024-006', 'borrower' => 'Michael Brown', 'type' => 'Home Loan', 'amount' => '$250,000', 'term' => '240 months', 'rate' => '6.75%', 'date' => '2024-05-17', 'status'=>'Disbursed'],
];
?>
<!doctype html>
<html lang="en">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>Loans - TIGRIS Capital</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body style="margin:0; background:#f4f5f7; color:#1f2937; font-family: Arial, Helvetica, sans-serif; overflow-x:hidden;">
  <div style="min-height:100vh; display:flex; background:#f4f5f7;">
    <aside style="width:240px; background:#121416; border-right:1px solid rgba(226,232,240,0.08); display:flex; flex-direction:column; justify-content:space-between;">
      <div>
        <div style="height:69px; display:flex; align-items:center; gap:12px; padding:0 18px; border-bottom:1px solid rgba(226,232,240,0.08);">
          <div style="width:36px; height:36px; border-radius:6px; overflow:hidden;">
            <img src="Tigris_Logo_NoText.png" alt="logo" style="width:100%;height:100%;object-fit:cover;">
          </div>
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
          <div style="font-size:19px; font-weight:600;">Loans</div>
          <div style="font-size:12px; color:#cbd5e1; margin-top:6px;">Welcome back, <?php echo htmlspecialchars($username); ?></div>
        </div>
        <div style="display:flex; align-items:center; gap:18px;">
          <div style="width:26px; height:26px; border-radius:50%; background:#a82329; display:flex; align-items:center; justify-content:center; color:#fff;">U</div>
        </div>
      </header>

      <section style="padding:24px; background:#ffffff; flex:1;">
        <div style="display:flex; gap:16px; align-items:center; justify-content:space-between; margin-bottom:16px;">
          <div style="flex:1; display:flex; gap:12px;">
            <input type="search" placeholder="Search by Member Name or Loan ID" style="flex:1; padding:12px; border-radius:8px; border:1px solid #e5e7eb;">
            <select style="padding:12px 14px; border-radius:8px; border:1px solid #e5e7eb; min-width:180px;">
              <option>All Statuses</option>
            </select>
          </div>
          <a href="#" style="background:#a82329; color:#fff; padding:10px 14px; border-radius:8px; text-decoration:none;">+ Add Loan</a>
        </div>

        <div style="border:1px solid #e6e9ec; border-radius:8px; overflow:hidden;">
          <table class="table mb-0">
            <thead style="background:#fafafa;">
              <tr>
                <th>Loan ID</th><th>Borrower</th><th>Loan Type</th><th>Amount</th><th>Interest Rate</th><th>Term</th><th>Date Applied</th><th>Status</th>
              </tr>
            </thead>
            <tbody>
              <?php foreach($pendingLoans as $l): ?>
                <tr>
                  <td><?php echo htmlspecialchars($l['id']); ?></td>
                  <td><?php echo htmlspecialchars($l['borrower']); ?></td>
                  <td><?php echo htmlspecialchars($l['type']); ?></td>
                  <td><?php echo htmlspecialchars($l['amount']); ?></td>
                  <td><?php echo htmlspecialchars($l['rate']); ?></td>
                  <td><?php echo htmlspecialchars($l['term']); ?></td>
                  <td><?php echo htmlspecialchars($l['date']); ?></td>
                  <td><span style="padding:6px 10px; border-radius:999px; background:rgba(168,35,41,0.08); color:#a82329; font-weight:700; font-size:12px;"><?php echo htmlspecialchars($l['status']); ?></span></td>
                </tr>
              <?php endforeach; ?>
            </tbody>
          </table>
          <div style="padding:12px 16px; color:#6b7280;">Showing <?php echo count($pendingLoans); ?> of <?php echo count($pendingLoans); ?> loans</div>
        </div>
      </section>
    </main>
  </div>
  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
