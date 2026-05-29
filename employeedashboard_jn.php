<?php
session_start();
$username = $_SESSION['username'] ?? 'officer01';
$userRole = $_SESSION['user_type'] ?? 'Employee';

$pendingLoans = [
    ['id' => 'LN-2024-008', 'borrower' => 'Robert Wilson', 'type' => 'Personal Loan', 'amount' => '$18,750', 'term' => '36 months', 'rate' => '8.5%', 'date' => 'May 18, 2024'],
    ['id' => 'LN-2024-009', 'borrower' => 'Sarah Martinez', 'type' => 'Business Loan', 'amount' => '$65,000', 'term' => '60 months', 'rate' => '10.2%', 'date' => 'May 19, 2024'],
    ['id' => 'LN-2024-010', 'borrower' => 'David Chen', 'type' => 'Auto Loan', 'amount' => '$32,000', 'term' => '48 months', 'rate' => '7.25%', 'date' => 'May 19, 2024'],
];
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Loan Management System - Employee Panel</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body style="margin:0; background:#f6f7fb; color:#2b3445; font-family: Arial, Helvetica, sans-serif; overflow-x:hidden;">
    <div style="min-height:100vh; display:flex; background:#f6f7fb;">
        <aside style="width:190px; background:#fff; border-right:1px solid #e8ebf1; box-shadow: 0 0 0 1px rgba(0,0,0,0.01); display:flex; flex-direction:column; justify-content:space-between;">
            <div>
                <div style="height:69px; display:flex; align-items:center; gap:12px; padding:0 16px; border-bottom:1px solid #e8ebf1;">
                    <div style="width:30px; height:30px; border-radius:6px; background:#ff8a00; display:flex; align-items:center; justify-content:center; color:#fff; flex:0 0 auto;">
                        <svg width="17" height="17" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M4 4h7v7H4z"></path><path d="M13 4h7v7h-7z"></path><path d="M4 13h7v7H4z"></path><path d="M13 13h7v7h-7z"></path></svg>
                    </div>
                    <div style="line-height:1.05;">
                        <div style="font-size:16px; font-weight:700; color:#334155;">Employee Portal</div>
                        <div style="font-size:11px; color:#8a94a6; margin-top:2px;">Loan Officer</div>
                    </div>
                </div>

                <nav style="padding:14px 10px 0 10px;">
                    <a href="#" style="display:flex; align-items:center; gap:12px; height:36px; border-radius:6px; padding:0 12px; margin-bottom:8px; background:#fff2df; color:#f28a00; text-decoration:none; font-size:13px; font-weight:500;">
                        <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M4 4h6v6H4z"></path><path d="M14 4h6v6h-6z"></path><path d="M4 14h6v6H4z"></path><path d="M14 14h6v6h-6z"></path></svg>
                        Overview
                    </a>
                    <a href="#" style="display:flex; align-items:center; gap:12px; height:36px; border-radius:6px; padding:0 12px; margin-bottom:8px; color:#6b7280; text-decoration:none; font-size:13px; font-weight:500;">
                        <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M20 21a8 8 0 0 0-16 0"></path><circle cx="12" cy="7" r="4"></circle></svg>
                        Members
                    </a>
                    <a href="#" style="display:flex; align-items:center; gap:12px; height:36px; border-radius:6px; padding:0 12px; margin-bottom:8px; color:#6b7280; text-decoration:none; font-size:13px; font-weight:500;">
                        <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect x="3" y="5" width="18" height="14" rx="2"></rect><path d="M3 9h18"></path></svg>
                        Loans
                    </a>
                    <a href="#" style="display:flex; align-items:center; gap:12px; height:36px; border-radius:6px; padding:0 12px; margin-bottom:8px; color:#6b7280; text-decoration:none; font-size:13px; font-weight:500;">
                        <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect x="3" y="5" width="18" height="14" rx="2"></rect><path d="M7 15h10"></path></svg>
                        Payments
                    </a>
                    <a href="#" style="display:flex; align-items:center; gap:12px; height:36px; border-radius:6px; padding:0 12px; color:#6b7280; text-decoration:none; font-size:13px; font-weight:500;">
                        <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"></path><path d="M14 2v6h6"></path><path d="M8 13h8"></path><path d="M8 17h8"></path></svg>
                        Loan Types
                    </a>
                </nav>
            </div>

            <div style="padding:18px 12px; border-top:1px solid #e8ebf1;">
                <a href="Loan_login_jn.php" style="display:flex; align-items:center; gap:10px; color:#6b7280; text-decoration:none; font-size:13px; font-weight:600; padding:10px 10px; border-radius:8px;">
                    <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M10 17l5-5-5-5"></path><path d="M15 12H3"></path><path d="M21 3v18"></path></svg>
                    Logout
                </a>
            </div>
        </aside>

        <main style="flex:1; min-width:0; display:flex; flex-direction:column;">
            <header style="height:69px; background:#fff; border-bottom:1px solid #e8ebf1; display:flex; align-items:center; justify-content:space-between; padding:0 18px 0 20px;">
                <div>
                    <div style="font-size:19px; font-weight:500; color:#293241; letter-spacing:-0.2px;">Loan Management System - Employee Panel</div>
                    <div style="font-size:12px; color:#7f8aa3; margin-top:6px;">Welcome back, <?php echo htmlspecialchars($username); ?></div>
                </div>

                <div style="display:flex; align-items:center; gap:18px;">
                    <div style="position:relative; width:26px; height:26px; display:flex; align-items:center; justify-content:center; color:#8b94a7;">
                        <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M18 8a6 6 0 0 0-12 0c0 7-3 9-3 9h18s-3-2-3-9"></path><path d="M13.73 21a2 2 0 0 1-3.46 0"></path></svg>
                        <span style="position:absolute; top:1px; right:1px; width:7px; height:7px; background:#ff4b5c; border-radius:50%; border:1px solid #fff;"></span>
                    </div>

                    <div style="display:flex; align-items:center; gap:10px;">
                        <div style="width:26px; height:26px; border-radius:50%; background:#ff8a00; display:flex; align-items:center; justify-content:center; color:#fff; flex:0 0 auto;">
                            <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M20 21a8 8 0 0 0-16 0"></path><circle cx="12" cy="8" r="4"></circle></svg>
                        </div>
                        <div style="line-height:1.05;">
                            <div style="font-size:12px; font-weight:700; color:#293241;"><?php echo htmlspecialchars($username); ?></div>
                            <div style="font-size:11px; color:#7f8aa3; margin-top:4px;"><?php echo htmlspecialchars($userRole); ?></div>
                        </div>
                        <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="#9aa3b2" stroke-width="2"><path d="m6 9 6 6 6-6"></path></svg>
                    </div>
                </div>
            </header>

            <section style="padding:28px 18px 18px 18px;">
                <div style="margin-bottom:18px;">
                    <div style="font-size:26px; font-weight:500; color:#243044; letter-spacing:-0.2px; margin-bottom:9px;">Employee Dashboard</div>
                    <div style="font-size:14px; color:#8a94a6;">Welcome back! Here&apos;s your loan approval queue.</div>
                </div>


                <div class="row g-3" style="margin-top:24px; margin-left:0; margin-right:0;">
                    <div class="col-12 col-lg-4" style="padding-left:0; padding-right:0;">
                        <div style="background:#fff; border:1px solid #e8ebf1; border-radius:6px; box-shadow:0 1px 4px rgba(16,24,40,.04); min-height:176px; padding:16px;">
                            <div style="font-size:14px; font-weight:700; color:#2b3445; margin-bottom:14px;">Quick Actions</div>
                            <div style="display:flex; flex-direction:column; gap:10px;">
                                <div style="height:38px; border:1px solid #e5e7eb; border-radius:6px; display:flex; align-items:center; gap:10px; padding:0 12px; color:#334155; font-size:13px; font-weight:600; background:#fff;">
                                    <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="#ff8a00" stroke-width="2"><path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"></path><path d="M14 2v6h6"></path><path d="M8 13h8"></path></svg>
                                    View All Loans
                                </div>
                                <div style="height:38px; border:1px solid #e5e7eb; border-radius:6px; display:flex; align-items:center; gap:10px; padding:0 12px; color:#334155; font-size:13px; font-weight:600; background:#fff;">
                                    <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="#ff8a00" stroke-width="2"><path d="M20 21a8 8 0 0 0-16 0"></path><circle cx="12" cy="7" r="4"></circle></svg>
                                    Manage Members
                                </div>
                                <div style="height:38px; border:1px solid #e5e7eb; border-radius:6px; display:flex; align-items:center; gap:10px; padding:0 12px; color:#334155; font-size:13px; font-weight:600; background:#fff;">
                                    <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="#ff8a00" stroke-width="2"><path d="M3 17l6-6 4 4 8-8"></path><path d="M14 7h7v7"></path></svg>
                                    View Reports
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="col-12 col-lg-4" style="padding-left:0; padding-right:0;">
                        <div style="background:#fff; border:1px solid #e8ebf1; border-radius:6px; box-shadow:0 1px 4px rgba(16,24,40,.04); min-height:176px; padding:16px;">
                            <div style="font-size:14px; font-weight:700; color:#2b3445; margin-bottom:18px;">This Week&apos;s Activity</div>
                            <div style="margin-bottom:16px;">
                                <div style="display:flex; justify-content:space-between; font-size:13px; color:#6b7280; margin-bottom:8px;"><span>Approved</span><span>45 loans</span></div>
                                <div style="height:6px; background:#e9edf3; border-radius:999px; overflow:hidden;"><div style="width:74%; height:100%; background:#18d0a2; border-radius:999px;"></div></div>
                            </div>
                            <div style="margin-bottom:16px;">
                                <div style="display:flex; justify-content:space-between; font-size:13px; color:#6b7280; margin-bottom:8px;"><span>Rejected</span><span>8 loans</span></div>
                                <div style="height:6px; background:#e9edf3; border-radius:999px; overflow:hidden;"><div style="width:14%; height:100%; background:#f04b59; border-radius:999px;"></div></div>
                            </div>
                            <div>
                                <div style="display:flex; justify-content:space-between; font-size:13px; color:#6b7280; margin-bottom:8px;"><span>Under Review</span><span>12 loans</span></div>
                                <div style="height:6px; background:#e9edf3; border-radius:999px; overflow:hidden;"><div style="width:32%; height:100%; background:#dfe6ef; border-radius:999px;"></div></div>
                            </div>
                        </div>
                    </div>

                    <div class="col-12 col-lg-4" style="padding-left:0; padding-right:0;">
                        <div style="background:#fff; border:1px solid #e8ebf1; border-radius:6px; box-shadow:0 1px 4px rgba(16,24,40,.04); min-height:176px; padding:16px;">
                            <div style="font-size:14px; font-weight:700; color:#2b3445; margin-bottom:18px;">Performance Metrics</div>
                            <div style="margin-bottom:16px;">
                                <div style="display:flex; justify-content:space-between; font-size:13px; color:#6b7280; margin-bottom:8px;"><span>Approval Rate</span><span>85%</span></div>
                                <div style="height:6px; background:#e9edf3; border-radius:999px; overflow:hidden;"><div style="width:85%; height:100%; background:#18d0a2; border-radius:999px;"></div></div>
                            </div>
                            <div style="margin-bottom:16px; display:flex; justify-content:space-between; align-items:flex-end; gap:16px;">
                                <div>
                                    <div style="font-size:13px; color:#6b7280; margin-bottom:6px;">Avg. Processing Time</div>
                                    <div style="font-size:13px; color:#18d0a2; margin-bottom:4px;">↓ 0.5 days faster than last month</div>
                                </div>
                                <div style="font-size:13px; color:#374151; white-space:nowrap;">2.3 days</div>
                            </div>
                            <div style="display:flex; justify-content:space-between; align-items:flex-end; gap:16px;">
                                <div>
                                    <div style="font-size:13px; color:#6b7280; margin-bottom:6px;">Total Processed</div>
                                    <div style="font-size:13px; color:#18d0a2; margin-bottom:4px;">↑ 18% this month</div>
                                </div>
                                <div style="font-size:13px; color:#374151; white-space:nowrap;">127 loans</div>
                            </div>
                        </div>
                    </div>
                </div>
            </section>
        </main>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>