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
<body style="margin:0; background:#f4f5f7; color:#1f2937; font-family: Arial, Helvetica, sans-serif; overflow-x:hidden;">
    <div style="min-height:100vh; display:flex; background:#f4f5f7;">
        <aside style="width:190px; background:#121416; border-right:1px solid rgba(226,232,240,0.08); box-shadow: 0 0 0 1px rgba(0,0,0,0.08); display:flex; flex-direction:column; justify-content:space-between;">
            <div>
                <div style="height:69px; display:flex; align-items:center; gap:12px; padding:0 16px; border-bottom:1px solid rgba(226,232,240,0.08);">
                    <div style="width:30px; height:30px; border-radius:6px; background:#121416; display:flex; align-items:center; justify-content:center; color:#fff; flex:0 0 auto; box-shadow:0 0 0 1px rgba(255,255,255,0.04); overflow:hidden;">
                        <img src="Tigris_Logo_NoText.png" alt="Tigris Capital Logo" style="width:100%; height:100%; object-fit:cover;">
                    </div>
                    <div style="line-height:1.05;">
                        <div style="font-size:16px; font-weight:700; color:#e2e8f0;">Employee Portal</div>
                        <div style="font-size:11px; color:#94a3b8; margin-top:2px;">Loan Officer</div>
                    </div>
                </div>

                <nav style="padding:14px 10px 0 10px;">
                    <a href="#" style="display:flex; align-items:center; gap:12px; height:36px; border-radius:6px; padding:0 12px; margin-bottom:8px; background:rgba(168,35,41,0.16); color:#ffffff; text-decoration:none; font-size:13px; font-weight:600; border:1px solid rgba(168,35,41,0.28); box-shadow:inset 0 1px 0 rgba(255,255,255,0.03);">
                        <span style="width:16px; height:16px; border-radius:4px; border:1px solid currentColor; display:inline-flex; align-items:center; justify-content:center; font-size:9px; line-height:1; font-weight:700;">O</span>
                        Overview
                    </a>
                    <a href="#" style="display:flex; align-items:center; gap:12px; height:36px; border-radius:6px; padding:0 12px; margin-bottom:8px; color:#cbd5e1; text-decoration:none; font-size:13px; font-weight:500;">
                        <span style="width:16px; height:16px; border-radius:50%; border:1px solid currentColor; display:inline-flex; align-items:center; justify-content:center; font-size:9px; line-height:1; font-weight:700;">M</span>
                        Members
                    </a>
                    <a href="#" style="display:flex; align-items:center; gap:12px; height:36px; border-radius:6px; padding:0 12px; margin-bottom:8px; color:#cbd5e1; text-decoration:none; font-size:13px; font-weight:500;">
                        <span style="width:16px; height:16px; border-radius:4px; border:1px solid currentColor; display:inline-flex; align-items:center; justify-content:center; font-size:9px; line-height:1; font-weight:700;">L</span>
                        Loans
                    </a>
                    <a href="#" style="display:flex; align-items:center; gap:12px; height:36px; border-radius:6px; padding:0 12px; margin-bottom:8px; color:#cbd5e1; text-decoration:none; font-size:13px; font-weight:500;">
                        <span style="width:16px; height:16px; border-radius:4px; border:1px solid currentColor; display:inline-flex; align-items:center; justify-content:center; font-size:9px; line-height:1; font-weight:700;">P</span>
                        Payments
                    </a>
                    <a href="#" style="display:flex; align-items:center; gap:12px; height:36px; border-radius:6px; padding:0 12px; color:#cbd5e1; text-decoration:none; font-size:13px; font-weight:500;">
                        <span style="width:16px; height:16px; border-radius:4px; border:1px solid currentColor; display:inline-flex; align-items:center; justify-content:center; font-size:8px; line-height:1; font-weight:700;">LT</span>
                        Loan Types
                    </a>
                </nav>
            </div>

            <div style="padding:18px 12px; border-top:1px solid rgba(226,232,240,0.08);">
                <a href="Loan_login_jn.php" style="display:flex; align-items:center; gap:10px; color:#e2e8f0; text-decoration:none; font-size:13px; font-weight:600; padding:10px 10px; border-radius:8px; background:rgba(255,255,255,0.02); border:1px solid rgba(226,232,240,0.08);">
                    <span style="font-size:16px; line-height:1; font-weight:700;">↪</span>
                    Logout
                </a>
            </div>
        </aside>

        <main style="flex:1; min-width:0; display:flex; flex-direction:column;">
            <header style="height:69px; background:#121416; border-bottom:1px solid rgba(226,232,240,0.08); display:flex; align-items:center; justify-content:space-between; padding:0 18px 0 20px;">
                <div>
                    <div style="font-size:19px; font-weight:600; color:#ffffff; letter-spacing:-0.2px;">Loan Management System - Employee Panel</div>
                    <div style="font-size:12px; color:#cbd5e1; margin-top:6px;">Welcome back, <?php echo htmlspecialchars($username); ?></div>
                </div>

                <div style="display:flex; align-items:center; gap:18px;">
                    <div style="position:relative; width:26px; height:26px; display:flex; align-items:center; justify-content:center; color:#e2e8f0;">
                        <span style="font-size:18px; line-height:1;">◔</span>
                        <span style="position:absolute; top:1px; right:1px; width:7px; height:7px; background:#a82329; border-radius:50%; border:1px solid #121416;"></span>
                    </div>

                    <div style="display:flex; align-items:center; gap:10px;">
                        <div style="width:26px; height:26px; border-radius:50%; background:#a82329; display:flex; align-items:center; justify-content:center; color:#fff; flex:0 0 auto; box-shadow:0 0 0 1px rgba(255,255,255,0.06);">
                            <span style="font-size:14px; line-height:1; font-weight:700;">U</span>
                        </div>
                        <div style="line-height:1.05;">
                            <div style="font-size:12px; font-weight:700; color:#ffffff;"><?php echo htmlspecialchars($username); ?></div>
                            <div style="font-size:11px; color:#cbd5e1; margin-top:4px;"><?php echo htmlspecialchars($userRole); ?></div>
                        </div>
                        <span style="font-size:14px; line-height:1; font-weight:700; color:#e2e8f0;">⌄</span>
                    </div>
                </div>
            </header>

            <section style="padding:28px 18px 18px 18px; background:#ffffff; flex:1;">
                <div style="margin-bottom:18px;">
                    <div style="font-size:26px; font-weight:600; color:#1f2937; letter-spacing:-0.2px; margin-bottom:9px;">Employee Dashboard</div>
                    <div style="font-size:14px; color:#6b7280;">Welcome back! Here&apos;s your loan approval queue.</div>
                </div>

                <div class="row gx-4 gy-4" style="margin:0 0 24px 0;">
                    <div class="col-12 col-md-6 col-xl-3 px-2">
                        <div style="height:128px; background:#fff; border:1px solid #dbe0e6; border-radius:10px; box-shadow:0 2px 10px rgba(15,23,42,0.08); padding:18px 20px; display:flex; justify-content:space-between; align-items:flex-start;">
                            <div style="display:flex; flex-direction:column; gap:8px;">
                                <div style="font-size:14px; color:#54708a; font-weight:500;">Pending Approvals</div>
                                <div style="font-size:28px; font-weight:700; color:#111827; line-height:1;">3</div>
                            </div>
                            <div style="width:44px; height:44px; border-radius:10px; background:rgba(249,115,22,0.08); display:flex; align-items:center; justify-content:center; color:#f97316; flex:0 0 auto;">
                                <span style="font-size:20px; line-height:1; font-weight:700;">⏰</span>
                            </div>
                        </div>
                    </div>

                    <div class="col-12 col-md-6 col-xl-3 px-2">
                        <div style="height:128px; background:#fff; border:1px solid #dbe0e6; border-radius:10px; box-shadow:0 2px 10px rgba(15,23,42,0.08); padding:18px 20px; display:flex; justify-content:space-between; align-items:flex-start;">
                            <div style="display:flex; flex-direction:column; gap:8px;">
                                <div style="font-size:14px; color:#54708a; font-weight:500;">Approved Today</div>
                                <div style="font-size:28px; font-weight:700; color:#111827; line-height:1;">8</div>
                            </div>
                            <div style="width:44px; height:44px; border-radius:10px; background:rgba(16,185,129,0.08); display:flex; align-items:center; justify-content:center; color:#10b981; flex:0 0 auto;">
                                <span style="font-size:20px; line-height:1; font-weight:700;">✓</span>
                            </div>
                        </div>
                    </div>

                    <div class="col-12 col-md-6 col-xl-3 px-2">
                        <div style="height:128px; background:#fff; border:1px solid #dbe0e6; border-radius:10px; box-shadow:0 2px 10px rgba(15,23,42,0.08); padding:18px 20px; display:flex; justify-content:space-between; align-items:flex-start;">
                            <div style="display:flex; flex-direction:column; gap:8px;">
                                <div style="font-size:14px; color:#54708a; font-weight:500;">Total Value Pending</div>
                                <div style="font-size:28px; font-weight:700; color:#111827; line-height:1;">$115,750</div>
                            </div>
                            <div style="width:44px; height:44px; border-radius:10px; background:rgba(168,35,41,0.08); display:flex; align-items:center; justify-content:center; color:#a82329; flex:0 0 auto;">
                                <span style="font-size:20px; line-height:1; font-weight:700;">$</span>
                            </div>
                        </div>
                    </div>

                    <div class="col-12 col-md-6 col-xl-3 px-2">
                        <div style="height:128px; background:#fff; border:1px solid #dbe0e6; border-radius:10px; box-shadow:0 2px 10px rgba(15,23,42,0.08); padding:18px 20px; display:flex; justify-content:space-between; align-items:flex-start;">
                            <div style="display:flex; flex-direction:column; gap:8px;">
                                <div style="font-size:14px; color:#54708a; font-weight:500;">Active Members</div>
                                <div style="font-size:28px; font-weight:700; color:#111827; line-height:1;">1,248</div>
                            </div>
                            <div style="width:44px; height:44px; border-radius:10px; background:rgba(107,114,128,0.08); display:flex; align-items:center; justify-content:center; color:#6b7280; flex:0 0 auto;">
                                <span style="font-size:18px; line-height:1; font-weight:700;">👥</span>
                            </div>
                        </div>
                    </div>
                </div>


                <div class="row g-3" style="margin-top:24px; margin-left:0; margin-right:0;">
                    <div class="col-12 col-lg-4" style="padding-left:0; padding-right:0;">
                        <div style="background:#fff; border:1px solid #e5e7eb; border-radius:6px; box-shadow:0 1px 4px rgba(16,24,40,.04); min-height:176px; padding:16px;">
                            <div style="font-size:14px; font-weight:700; color:#1f2937; margin-bottom:14px;">Quick Actions</div>
                            <div style="display:flex; flex-direction:column; gap:10px;">
                                <div style="height:38px; border:1px solid #d1d5db; border-radius:6px; display:flex; align-items:center; gap:10px; padding:0 12px; color:#374151; font-size:13px; font-weight:600; background:#fff;">
                                    <span style="width:16px; height:16px; border-radius:3px; border:1px solid #a82329; display:inline-flex; align-items:center; justify-content:center; font-size:9px; line-height:1; color:#a82329; font-weight:700;">L</span>
                                    View All Loans
                                </div>
                                <div style="height:38px; border:1px solid #d1d5db; border-radius:6px; display:flex; align-items:center; gap:10px; padding:0 12px; color:#374151; font-size:13px; font-weight:600; background:#fff;">
                                    <span style="width:16px; height:16px; border-radius:50%; border:1px solid #a82329; display:inline-flex; align-items:center; justify-content:center; font-size:9px; line-height:1; color:#a82329; font-weight:700;">M</span>
                                    Manage Members
                                </div>
                                <div style="height:38px; border:1px solid #d1d5db; border-radius:6px; display:flex; align-items:center; gap:10px; padding:0 12px; color:#374151; font-size:13px; font-weight:600; background:#fff;">
                                    <span style="width:16px; height:16px; border-radius:3px; border:1px solid #a82329; display:inline-flex; align-items:center; justify-content:center; font-size:9px; line-height:1; color:#a82329; font-weight:700;">R</span>
                                    View Reports
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="col-12 col-lg-4" style="padding-left:0; padding-right:0;">
                        <div style="background:#fff; border:1px solid #e5e7eb; border-radius:6px; box-shadow:0 1px 4px rgba(16,24,40,.04); min-height:176px; padding:16px;">
                            <div style="font-size:14px; font-weight:700; color:#1f2937; margin-bottom:18px;">This Week&apos;s Activity</div>
                            <div style="margin-bottom:16px;">
                                <div style="display:flex; justify-content:space-between; font-size:13px; color:#6b7280; margin-bottom:8px;"><span>Approved</span><span>45 loans</span></div>
                                <div style="height:6px; background:#e5e7eb; border-radius:999px; overflow:hidden;"><div style="width:74%; height:100%; background:#a82329; border-radius:999px;"></div></div>
                            </div>
                            <div style="margin-bottom:16px;">
                                <div style="display:flex; justify-content:space-between; font-size:13px; color:#6b7280; margin-bottom:8px;"><span>Rejected</span><span>8 loans</span></div>
                                <div style="height:6px; background:#e5e7eb; border-radius:999px; overflow:hidden;"><div style="width:14%; height:100%; background:#4b5563; border-radius:999px;"></div></div>
                            </div>
                            <div>
                                <div style="display:flex; justify-content:space-between; font-size:13px; color:#6b7280; margin-bottom:8px;"><span>Under Review</span><span>12 loans</span></div>
                                <div style="height:6px; background:#e5e7eb; border-radius:999px; overflow:hidden;"><div style="width:32%; height:100%; background:#a82329; opacity:0.32; border-radius:999px;"></div></div>
                            </div>
                        </div>
                    </div>

                    <div class="col-12 col-lg-4" style="padding-left:0; padding-right:0;">
                        <div style="background:#fff; border:1px solid #e5e7eb; border-radius:6px; box-shadow:0 1px 4px rgba(16,24,40,.04); min-height:176px; padding:16px;">
                            <div style="font-size:14px; font-weight:700; color:#1f2937; margin-bottom:18px;">Performance Metrics</div>
                            <div style="margin-bottom:16px;">
                                <div style="display:flex; justify-content:space-between; font-size:13px; color:#6b7280; margin-bottom:8px;"><span>Approval Rate</span><span>85%</span></div>
                                <div style="height:6px; background:#e5e7eb; border-radius:999px; overflow:hidden;"><div style="width:85%; height:100%; background:#a82329; border-radius:999px;"></div></div>
                            </div>
                            <div style="margin-bottom:16px; display:flex; justify-content:space-between; align-items:flex-end; gap:16px;">
                                <div>
                                    <div style="font-size:13px; color:#6b7280; margin-bottom:6px;">Avg. Processing Time</div>
                                    <div style="font-size:13px; color:#a82329; margin-bottom:4px;">↓ 0.5 days faster than last month</div>
                                </div>
                                <div style="font-size:13px; color:#374151; white-space:nowrap;">2.3 days</div>
                            </div>
                            <div style="display:flex; justify-content:space-between; align-items:flex-end; gap:16px;">
                                <div>
                                    <div style="font-size:13px; color:#6b7280; margin-bottom:6px;">Total Processed</div>
                                    <div style="font-size:13px; color:#a82329; margin-bottom:4px;">↑ 18% this month</div>
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