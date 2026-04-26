<?php 
if (session_status() === PHP_SESSION_NONE) { session_start(); }
include('func.php');  
include('newfunc.php');


$host = 'localhost';
$dbname = 'myhmsdb';
$db_user = 'root';
$db_pass = '';

try {
    $pdo = new PDO("mysql:host=$host;dbname=$dbname", $db_user, $db_pass);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    $pdo->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);
} catch(PDOException $e) {
    die("<div style='background-color:#fee2e2; color:#991b1b; padding:20px; font-family: sans-serif;'>System Error: Database connection failed.</div>");
}

$pid = $_SESSION['pid'];
$fname = $_SESSION['fname'];
$lname = $_SESSION['lname'];
$gender = $_SESSION['gender'];
$email = $_SESSION['email'];
$contact = $_SESSION['contact'];

$notification = '';


$vitals_stmt = $pdo->prepare("
    SELECT ID as appt_id, bed_number, current_status, oxygen_level, oxygen_liters, heart_rate, admission_date, daily_update, DATEDIFF(CURRENT_TIMESTAMP, admission_date) AS days_occupied 
    FROM appointmenttb 
    WHERE pid = ? 
    ORDER BY appdate DESC, apptime DESC LIMIT 1
");
$vitals_stmt->execute([$pid]);
$my_appt = $vitals_stmt->fetch();


$is_admitted = false;
$current_ui_status = "Outpatient";

if ($my_appt) {
    $current_ui_status = $my_appt['current_status'];
    if ($current_ui_status === 'Admitted') {
        $is_admitted = true;
    }
}


$graph_labels = []; $graph_spo2 = []; $graph_liters = [];
if ($is_admitted && $my_appt) {
    
    $history_stmt = $pdo->prepare("
        SELECT * FROM (
            SELECT * FROM patient_vitals_log 
            WHERE appt_id = ? 
            ORDER BY recorded_at DESC LIMIT 10
        ) sub 
        ORDER BY recorded_at ASC
    ");
    $history_stmt->execute([$my_appt['appt_id']]);
    $vitals_history = $history_stmt->fetchAll();

    foreach($vitals_history as $log) {
        $graph_labels[] = date('h:i A', strtotime($log['recorded_at']));
        $graph_spo2[] = $log['oxygen_level'];
        $graph_liters[] = $log['oxygen_liters'];
    }
}


$meds_stmt = $pdo->prepare("SELECT doctor, disease, prescription, appdate FROM prestb WHERE pid = ? ORDER BY appdate DESC, apptime DESC LIMIT 1");
$meds_stmt->execute([$pid]);
$latest_meds = $meds_stmt->fetch();


if(isset($_POST['app-submit'])) {
    $doctor = $_POST['doctor']; $docFees = $_POST['docFees']; $appdate = $_POST['appdate']; $apptime = $_POST['apptime'];
    date_default_timezone_set('Asia/Kolkata');
    $cur_date = date("Y-m-d"); $cur_time = date("H:i:s");
    $apptime1 = strtotime($apptime); $appdate1 = strtotime($appdate);
  
    if(date("Y-m-d", $appdate1) >= $cur_date) {
        if((date("Y-m-d", $appdate1) == $cur_date && date("H:i:s", $apptime1) > $cur_time) || date("Y-m-d", $appdate1) > $cur_date) {
            $stmt = $pdo->prepare("SELECT apptime FROM appointmenttb WHERE doctor = ? AND appdate = ? AND apptime = ?");
            $stmt->execute([$doctor, $appdate, $apptime]);
            if($stmt->rowCount() == 0) {
                $insert = $pdo->prepare("INSERT INTO appointmenttb(pid, fname, lname, gender, email, contact, doctor, docFees, appdate, apptime, userStatus, doctorStatus, current_status) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, '1', '1', 'Scheduled')");
                if($insert->execute([$pid, $fname, $lname, $gender, $email, $contact, $doctor, $docFees, $appdate, $apptime])) {
                    $notification = "<div class='bg-emerald-100 text-emerald-800 px-4 py-3 rounded-xl mb-6 flex justify-between shadow-sm'><span><i class='fa-solid fa-check-circle mr-2'></i> Appointment booked successfully.</span><button onclick='this.parentElement.style.display=\"none\"'><i class='fa-solid fa-xmark'></i></button></div>";
                    echo "<meta http-equiv='refresh' content='2'>";
                }
            } else {
                $notification = "<div class='bg-amber-100 text-amber-800 px-4 py-3 rounded-xl mb-6 flex justify-between shadow-sm'><span><i class='fa-solid fa-triangle-exclamation mr-2'></i> Doctor unavailable at this time.</span><button onclick='this.parentElement.style.display=\"none\"'><i class='fa-solid fa-xmark'></i></button></div>";
            }
        } else {
            $notification = "<div class='bg-rose-100 text-rose-800 px-4 py-3 rounded-xl mb-6 flex justify-between shadow-sm'><span><i class='fa-solid fa-circle-xmark mr-2'></i> Please select a future time.</span><button onclick='this.parentElement.style.display=\"none\"'><i class='fa-solid fa-xmark'></i></button></div>";
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
    <title>KASSAH Vitals | Patient Portal</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <script>
        tailwind.config = {
            theme: {
                extend: {
                    fontFamily: { sans: ['Plus Jakarta Sans', 'sans-serif'] },
                    colors: {
                        brand: { 50: '#f0fdfa', 500: '#14b8a6', 600: '#0d9488', 900: '#134e4a' },
                        accent: { 500: '#3b82f6', 600: '#2563eb' },
                        highlight: { 500: '#8b5cf6' }
                    },
                    animation: { 'blob': 'blob 7s infinite' },
                    keyframes: { blob: { '0%': { transform: 'translate(0,0) scale(1)' }, '33%': { transform: 'translate(30px,-50px) scale(1.1)' }, '66%': { transform: 'translate(-20px,20px) scale(0.9)' }, '100%': { transform: 'translate(0,0) scale(1)' } } }
                }
            }
        }
    </script>
    <style>
        body { background: 
        .glass-card { background: rgba(15,23,42,0.7); backdrop-filter: blur(12px); border: 1px solid rgba(51,65,85,0.5); box-shadow: 0 10px 25px rgba(0,0,0,0.3); }
        .tab-content { display: none; animation: slideUp 0.3s ease-out; }
        .tab-content.active { display: block; }
        @keyframes slideUp { from { opacity: 0; transform: translateY(10px); } to { opacity: 1; transform: translateY(0); } }
        ::-webkit-scrollbar { width: 6px; }
        ::-webkit-scrollbar-thumb { background: 
    </style>
</head>

<body class="h-screen w-full flex flex-col overflow-hidden relative">
    <div class="fixed inset-0 -z-10 overflow-hidden pointer-events-none">
        <div class="absolute top-[-10%] left-[10%] w-96 h-96 bg-brand-500/20 rounded-full blur-3xl animate-blob"></div>
        <div class="absolute top-[30%] right-[5%] w-80 h-80 bg-accent-500/20 rounded-full blur-3xl animate-blob" style="animation-delay:2s"></div>
        <div class="absolute bottom-0 left-[40%] w-96 h-96 bg-highlight-500/15 rounded-full blur-3xl animate-blob" style="animation-delay:4s"></div>
    </div>

    <nav class="glass-card h-20 px-8 flex justify-between items-center shrink-0 z-40 border-b border-slate-800/50">
        <div class="flex items-center gap-3">
            <div class="bg-gradient-to-br from-brand-500 to-blue-600 text-white p-2.5 rounded-xl shadow-lg shadow-brand-500/30">
                <i class="fa-solid fa-hospital-user text-2xl"></i>
            </div>
            <h1 class="text-2xl font-extrabold tracking-tight text-white">KASSAH <span class="text-transparent bg-clip-text bg-gradient-to-r from-brand-500 to-accent-500">Vitals</span></h1>
        </div>
        <div class="flex items-center gap-6">
            <div class="text-right hidden md:block">
                <p class="text-sm font-bold text-white"><?= htmlspecialchars($fname . ' ' . $lname) ?></p>
                <p class="text-xs <?= $is_admitted ? 'text-brand-400 bg-brand-500/10 border border-brand-500/30' : 'text-slate-400 bg-slate-800' ?> font-semibold px-2 py-0.5 rounded-full inline-block mt-1">
                    PID: 
                </p>
            </div>
            <a href="logout.php" class="bg-slate-800 border border-slate-700 text-slate-400 hover:text-red-400 px-5 py-2.5 rounded-xl text-sm font-bold transition">
                <i class="fa fa-sign-out-alt mr-2"></i> Exit
            </a>
        </div>
    </nav>

    <div class="flex w-full h-[calc(100vh-5rem)] overflow-hidden">
        <aside class="w-72 shrink-0 glass-card p-6 hidden md:flex flex-col border-r border-slate-800/50 z-30">
            <p class="text-xs font-bold text-slate-500 uppercase tracking-widest mb-6">Patient Menu</p>
            <nav class="flex flex-col gap-2" id="nav-tabs">
                <button onclick="switchTab('dash')" class="tab-btn w-full text-left px-4 py-3.5 rounded-xl bg-brand-500 text-white font-bold shadow-md shadow-brand-500/20 transition group" data-target="dash">
                    <i class="fa-solid fa-chart-pie w-6 text-center"></i> <?= $is_admitted ? 'Live Vitals' : 'Dashboard' ?>
                </button>
                <button onclick="switchTab('book')" class="tab-btn w-full text-left px-4 py-3.5 rounded-xl text-slate-400 hover:bg-slate-800 font-semibold transition" data-target="book">
                    <i class="fa-solid fa-calendar-plus w-6 text-center text-slate-500"></i> Book Specialist
                </button>
                <button onclick="switchTab('history')" class="tab-btn w-full text-left px-4 py-3.5 rounded-xl text-slate-400 hover:bg-slate-800 font-semibold transition" data-target="history">
                    <i class="fa-solid fa-clock-rotate-left w-6 text-center text-slate-500"></i> Appointments
                </button>
                <button onclick="switchTab('prescriptions')" class="tab-btn w-full text-left px-4 py-3.5 rounded-xl text-slate-400 hover:bg-slate-800 font-semibold transition" data-target="prescriptions">
                    <i class="fa-solid fa-file-prescription w-6 text-center text-slate-500"></i> Records & Bills
                </button>
                <div class="border-t border-slate-800 my-3 pt-3">
                    <p class="text-xs font-bold text-slate-600 uppercase tracking-widest mb-2">Advanced Features</p>
                </div>
                <a href="ai_triage.php" class="w-full text-left px-4 py-3.5 rounded-xl text-slate-400 hover:bg-slate-800 font-semibold transition flex items-center gap-2">
                    <i class="fa-solid fa-robot w-6 text-center text-brand-500"></i> AI Health Triage
                    <span class="ml-auto text-xs bg-brand-500/20 text-brand-400 px-2 py-0.5 rounded-full">NEW</span>
                </a>
                <a href="blockchain_records.php" class="w-full text-left px-4 py-3.5 rounded-xl text-slate-400 hover:bg-slate-800 font-semibold transition flex items-center gap-2">
                    <i class="fa-solid fa-link w-6 text-center text-highlight-500"></i> Blockchain Records
                    <span class="ml-auto text-xs bg-highlight-500/20 text-purple-400 px-2 py-0.5 rounded-full">NEW</span>
                </a>
            </nav>
        </aside>

        <main class="flex-1 w-full h-full p-6 md:p-8 overflow-y-auto relative z-10">
            <?= $notification ?>

            <div id="dash" class="tab-content active max-w-7xl mx-auto">
                <?php if($is_admitted): ?>
                    
                    <div class="flex justify-between items-end mb-8">
                        <div>
                            <h2 class="text-3xl font-extrabold text-slate-800">Health Telemetry</h2>
                            <p class="text-slate-500 font-medium mt-1">Live monitoring for Bed: <span class="text-brand-600 font-bold"><?= htmlspecialchars($my_appt['bed_number']) ?></span> (Day <?= max(1, $my_appt['days_occupied']) ?>)</p>
                        </div>
                        <?php if($my_appt['oxygen_level'] <= 92): ?>
                            <div class="bg-red-50 border border-red-200 text-red-700 px-4 py-2 rounded-xl flex items-center shadow-sm animate-pulse">
                                <i class="fa-solid fa-triangle-exclamation text-xl mr-2"></i> <span class="font-bold">CRITICAL O2 LEVEL</span>
                            </div>
                        <?php endif; ?>
                    </div>
                    
                    <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-8">
                        <div class="glass-card p-6 rounded-2xl relative overflow-hidden">
                            <p class="text-slate-500 text-sm font-bold uppercase tracking-wider mb-2">Oxygen (SpO2)</p>
                            <h3 class="text-5xl font-black <?= ($my_appt['oxygen_level'] <= 92) ? 'text-red-600' : 'text-slate-800' ?>"><?= $my_appt['oxygen_level'] ?><span class="text-2xl text-slate-400 font-medium">%</span></h3>
                        </div>
                        <div class="glass-card p-6 rounded-2xl relative overflow-hidden">
                            <p class="text-slate-500 text-sm font-bold uppercase tracking-wider mb-2">O2 Flow Rate</p>
                            <h3 class="text-5xl font-black text-brand-600"><?= $my_appt['oxygen_liters'] ?><span class="text-2xl text-slate-400 font-medium"> L/min</span></h3>
                        </div>
                        <div class="glass-card p-6 rounded-2xl relative overflow-hidden">
                            <p class="text-slate-500 text-sm font-bold uppercase tracking-wider mb-2">Heart Rate</p>
                            <h3 class="text-5xl font-black text-slate-800"><?= $my_appt['heart_rate'] ?><span class="text-2xl text-slate-400 font-medium"> bpm</span></h3>
                        </div>
                    </div>

                    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6 mb-8">
                        <div class="lg:col-span-2 glass-card p-6 rounded-2xl">
                            <h3 class="text-lg font-bold text-slate-800 mb-4">O2 Administration History</h3>
                            <div class="w-full h-64"><canvas id="vitalsChart"></canvas></div>
                        </div>

                        <div class="lg:col-span-1 glass-card p-6 rounded-2xl flex flex-col gap-4">
                            <h3 class="text-lg font-bold text-slate-800">Ward Updates</h3>
                            <?php if(!empty($my_appt['daily_update'])): ?>
                                <div class="bg-amber-50 border border-amber-100 p-4 rounded-xl shadow-sm">
                                    <span class="bg-amber-100 text-amber-700 text-xs font-bold px-2 py-1 rounded inline-block mb-2">Daily Clinical Note</span>
                                    <p class="text-slate-700 text-sm italic italic leading-relaxed">"<?= nl2br(htmlspecialchars($my_appt['daily_update'])) ?>"</p>
                                </div>
                            <?php endif; ?>
                            <?php if($latest_meds): ?>
                                <div class="bg-blue-50/50 border border-blue-100 p-4 rounded-xl flex-1 flex flex-col">
                                    <p class="text-slate-800 font-bold text-xl mb-4"><?= htmlspecialchars($latest_meds['disease']) ?></p>
                                    <div class="font-mono text-sm text-slate-600 bg-white p-3 rounded-lg border border-slate-200 flex-1 overflow-y-auto"><?= nl2br(htmlspecialchars($latest_meds['prescription'])) ?></div>
                                    <p class="text-xs text-slate-400 mt-4">Dr. <?= htmlspecialchars($latest_meds['doctor']) ?></p>
                                </div>
                            <?php endif; ?>
                        </div>
                    </div>

                <?php else: ?>
                    
                    <div class="mb-8">
                        <h2 class="text-3xl font-extrabold text-white">Welcome, <?= htmlspecialchars($fname) ?> 👋</h2>
                        <p class="text-slate-400 font-medium mt-1">What would you like to do today?</p>
                    </div>
                    <div class="bg-brand-500/10 border-l-4 border-brand-500 p-6 rounded-r-xl mb-8">
                        <h3 class="text-lg font-bold text-white">Health Profile</h3>
                        <p class="text-sm text-slate-400 mt-1">Current Status: <span class="font-semibold text-brand-400"><?= $current_ui_status ?></span></p>
                    </div>
                    <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-6">
                        <div onclick="switchTab('book')" class="glass-card p-6 rounded-2xl border border-slate-700/50 hover:border-brand-500/50 hover:shadow-lg hover:shadow-brand-500/10 transition cursor-pointer group">
                             <i class="fa-solid fa-calendar-plus text-3xl text-brand-500 mb-4 block group-hover:scale-110 transition"></i>
                             <h3 class="text-xl font-bold text-white mb-2">Book Appointment</h3>
                             <p class="text-slate-400 text-sm">Schedule a new consultation with a specialized doctor.</p>
                        </div>
                        <div onclick="switchTab('history')" class="glass-card p-6 rounded-2xl border border-slate-700/50 hover:border-accent-500/50 hover:shadow-lg hover:shadow-accent-500/10 transition cursor-pointer group">
                             <i class="fa-solid fa-clock-rotate-left text-3xl text-accent-500 mb-4 block group-hover:scale-110 transition"></i>
                             <h3 class="text-xl font-bold text-white mb-2">My Appointments</h3>
                             <p class="text-slate-400 text-sm">View your upcoming scheduled visits and consultation history.</p>
                        </div>
                        <div onclick="switchTab('prescriptions')" class="glass-card p-6 rounded-2xl border border-slate-700/50 hover:border-highlight-500/50 hover:shadow-lg hover:shadow-highlight-500/10 transition cursor-pointer group">
                             <i class="fa-solid fa-file-prescription text-3xl text-highlight-500 mb-4 block group-hover:scale-110 transition"></i>
                             <h3 class="text-xl font-bold text-white mb-2">Medical Records</h3>
                             <p class="text-slate-400 text-sm">Access your prescriptions and download invoices.</p>
                        </div>
                    </div>
                    
                    <h3 class="text-sm font-bold text-slate-500 uppercase tracking-widest mb-4">Advanced Healthcare Features</h3>
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <a href="ai_triage.php" class="glass-card p-6 rounded-2xl border border-brand-500/30 hover:border-brand-500/60 hover:shadow-lg hover:shadow-brand-500/10 transition group flex items-start gap-4">
                            <div class="w-14 h-14 bg-brand-500/20 rounded-2xl flex items-center justify-center shrink-0 group-hover:scale-110 transition">
                                <i class="fa-solid fa-robot text-brand-400 text-2xl"></i>
                            </div>
                            <div>
                                <div class="flex items-center gap-2 mb-1">
                                    <h3 class="text-lg font-bold text-white">AI Health Triage</h3>
                                    <span class="text-xs bg-brand-500/20 text-brand-400 px-2 py-0.5 rounded-full font-bold">AI POWERED</span>
                                </div>
                                <p class="text-slate-400 text-sm">Describe your symptoms and get instant AI-powered analysis, condition predictions, and specialist recommendations.</p>
                            </div>
                        </a>
                        <a href="blockchain_records.php" class="glass-card p-6 rounded-2xl border border-highlight-500/30 hover:border-highlight-500/60 hover:shadow-lg hover:shadow-highlight-500/10 transition group flex items-start gap-4">
                            <div class="w-14 h-14 bg-highlight-500/20 rounded-2xl flex items-center justify-center shrink-0 group-hover:scale-110 transition">
                                <i class="fa-solid fa-link text-purple-400 text-2xl"></i>
                            </div>
                            <div>
                                <div class="flex items-center gap-2 mb-1">
                                    <h3 class="text-lg font-bold text-white">Blockchain Records</h3>
                                    <span class="text-xs bg-highlight-500/20 text-purple-400 px-2 py-0.5 rounded-full font-bold">SHA-256 SECURED</span>
                                </div>
                                <p class="text-slate-400 text-sm">View your cryptographically verified medical history. Any tampering with your records is instantly detected.</p>
                            </div>
                        </a>
                    </div>
                <?php endif; ?>
            </div>

            
            <div id="book" class="tab-content max-w-4xl mx-auto">
                <div class="mb-8"><h2 class="text-3xl font-extrabold text-slate-800">Book Appointment</h2></div>
                <div class="glass-card p-8 rounded-2xl">
                    <form method="post" action="" class="space-y-6">
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-8">
                            <?php
                                $specs = $pdo->query("SELECT DISTINCT spec FROM doctb")->fetchAll();
                                $docs = $pdo->query("SELECT username, spec, docFees FROM doctb")->fetchAll();
                            ?>
                            <div>
                                <label class="block text-slate-700 text-sm font-bold mb-2">Specialization</label>
                                <select id="spec" required class="w-full bg-slate-50 border border-slate-200 rounded-xl py-3 px-4 outline-none transition focus:ring-2 focus:ring-brand-500">
                                    <option value="" disabled selected>Select Specialization...</option>
                                    <?php foreach($specs as $s): ?><option value="<?= $s['spec'] ?>"><?= $s['spec'] ?></option><?php endforeach; ?>
                                </select>
                            </div>
                            <div>
                                <label class="block text-slate-700 text-sm font-bold mb-2">Specialist</label>
                                <select name="doctor" id="doctor" required class="w-full bg-slate-50 border border-slate-200 rounded-xl py-3 px-4 outline-none transition focus:ring-2 focus:ring-brand-500">
                                    <option value="" disabled selected>Select department first...</option>
                                    <?php foreach($docs as $d): ?>
                                        <option value="<?= $d['username'] ?>" data-spec="<?= $d['spec'] ?>" data-fees="<?= $d['docFees'] ?>" style="display:none;">Dr. <?= $d['username'] ?></option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                            <div>
                                <label class="block text-slate-700 text-sm font-bold mb-2">Date</label>
                                <input type="date" name="appdate" required class="w-full bg-slate-50 border border-slate-200 rounded-xl py-3 px-4 outline-none" min="<?= date('Y-m-d') ?>">
                            </div>
                            <div>
                                <label class="block text-slate-700 text-sm font-bold mb-2">Time Slot</label>
                                <select name="apptime" required class="w-full bg-slate-50 border border-slate-200 rounded-xl py-3 px-4 outline-none">
                                    <option value="08:00:00">08:00 AM</option><option value="10:00:00">10:00 AM</option><option value="12:00:00">12:00 PM</option><option value="14:00:00">02:00 PM</option><option value="16:00:00">04:00 PM</option>
                                </select>
                            </div>
                        </div>
                        <input type="hidden" name="docFees" id="docFees_hidden">
                        <button type="submit" name="app-submit" class="w-full bg-slate-800 text-white font-bold py-4 rounded-xl hover:bg-brand-600 transition shadow-lg">Confirm Booking</button>
                    </form>
                </div>
            </div>

            
            <div id="history" class="tab-content max-w-6xl mx-auto">
                <div class="mb-8"><h2 class="text-3xl font-extrabold text-slate-800">My Appointments</h2></div>
                <div class="glass-card rounded-2xl overflow-hidden">
                    <table class="w-full text-left text-sm">
                        <thead class="bg-slate-50 border-b border-slate-200 text-slate-500 font-bold uppercase tracking-wider">
                            <tr><th class="p-5">Doctor</th><th class="p-5">Date & Time</th><th class="p-5">Status</th><th class="p-5 text-right">Action</th></tr>
                        </thead>
                        <tbody class="divide-y divide-slate-100 text-slate-700">
                            <?php 
                            $appts_q = $pdo->prepare("SELECT ID, doctor, appdate, apptime, userStatus, doctorStatus, current_status FROM appointmenttb WHERE pid = ? ORDER BY appdate DESC");
                            $appts_q->execute([$pid]);
                            $results = $appts_q->fetchAll();
                            foreach($results as $row): ?>
                                <tr class="hover:bg-white/50 transition">
                                    <td class="p-5 font-bold">Dr. <?= htmlspecialchars($row['doctor']) ?></td>
                                    <td class="p-5"><?= htmlspecialchars($row['appdate']) ?> (<?= date('h:i A', strtotime($row['apptime'])) ?>)</td>
                                    <td class="p-5">
                                        <span class="bg-slate-100 px-3 py-1 rounded-full text-xs font-bold"><?= $row['current_status'] ?></span>
                                    </td>
                                    <td class="p-5 text-right">
                                        <?php if($row['userStatus']==1 && $row['doctorStatus']==1): ?>
                                            <a href="?ID=<?= $row['ID'] ?>&cancel=update" onclick="return confirm('Cancel appointment?')" class="text-rose-500 font-bold hover:bg-rose-50 px-4 py-2 rounded-lg transition">Cancel</a>
                                        <?php else: ?>
                                            <span class="text-slate-300">Concluded</span>
                                        <?php endif; ?>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </div>

            
            <div id="prescriptions" class="tab-content max-w-6xl mx-auto">
                <div class="mb-8"><h2 class="text-3xl font-extrabold text-slate-800">Medical Records</h2></div>
                <div class="glass-card rounded-2xl overflow-hidden">
                    <table class="w-full text-left text-sm">
                        <thead class="bg-slate-50 border-b border-slate-200 text-slate-500 font-bold uppercase tracking-wider">
                            <tr><th class="p-5">Doctor</th><th class="p-5">Date</th><th class="p-5">Diagnosis</th><th class="p-5 text-right">Download</th></tr>
                        </thead>
                        <tbody class="divide-y divide-slate-100 text-slate-700">
                            <?php 
                            $pres_q = $pdo->prepare("SELECT doctor, ID, appdate, disease, prescription FROM prestb WHERE pid= ? ORDER BY appdate DESC");
                            $pres_q->execute([$pid]);
                            foreach($pres_q->fetchAll() as $row): ?>
                                <tr class="hover:bg-white/50 transition">
                                    <td class="p-5 font-bold">Dr. <?= htmlspecialchars($row['doctor']) ?></td>
                                    <td class="p-5"><?= htmlspecialchars($row['appdate']) ?></td>
                                    <td class="p-5 font-medium text-rose-600"><?= htmlspecialchars($row['disease']) ?></td>
                                    <td class="p-5 text-right">
                                        <form method="get" action="generate_bill.php" target="_blank">
                                            <input type="hidden" name="ID" value="<?= $row['ID'] ?>"/>
                                            <button type="submit" class="bg-slate-800 text-white px-4 py-2 rounded-lg text-xs font-bold shadow-sm">Download Bill</button>
                                        </form>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </main>
    </div>

    <script>
        function switchTab(tabId) {
            document.querySelectorAll('.tab-content').forEach(el => el.classList.remove('active'));
            document.querySelectorAll('.tab-btn').forEach(btn => {
                btn.className = "tab-btn w-full text-left px-4 py-3.5 rounded-xl text-slate-600 hover:bg-white transition font-semibold";
            });
            document.getElementById(tabId).classList.add('active');
            let activeBtn = document.querySelector(`button[data-target="${tabId}"]`);
            if(activeBtn) {
                activeBtn.className = "tab-btn w-full text-left px-4 py-3.5 rounded-xl bg-brand-500 text-white font-bold shadow-md transition";
            }
        }

        document.getElementById('spec').addEventListener('change', function() {
            let selectedSpec = this.value;
            let docSelect = document.getElementById('doctor');
            docSelect.value = "";
            docSelect.querySelectorAll('option:not([disabled])').forEach(opt => {
                opt.style.display = (opt.getAttribute('data-spec') === selectedSpec) ? 'block' : 'none';
            });
        });

        document.getElementById('doctor').addEventListener('change', function() {
            document.getElementById('docFees_hidden').value = this.options[this.selectedIndex].getAttribute('data-fees');
        });

        
        const canvas = document.getElementById('vitalsChart');
        if (canvas) {
            const ctx = canvas.getContext('2d');
            new Chart(ctx, {
                type: 'line',
                data: {
                    labels: <?= json_encode($graph_labels ?? []) ?>,
                    datasets: [
                        { 
                            label: 'O2 Saturation (%)', 
                            data: <?= json_encode($graph_spo2 ?? []) ?>, 
                            borderColor: '#2563eb', 
                            backgroundColor: 'rgba(37, 99, 235, 0.1)', 
                            borderWidth: 3, 
                            tension: 0.4, 
                            pointRadius: 4,
                            pointHoverRadius: 6,
                            pointBackgroundColor: '#2563eb',
                            fill: true, 
                            yAxisID: 'y' 
                        },
                        { 
                            label: 'O2 Flow (L/min)', 
                            data: <?= json_encode($graph_liters ?? []) ?>, 
                            borderColor: '#0d9488', 
                            borderWidth: 3, 
                            borderDash: [5, 5], 
                            tension: 0.4, 
                            pointRadius: 4,
                            pointHoverRadius: 6,
                            pointBackgroundColor: '#0d9488',
                            fill: false, 
                            yAxisID: 'y1' 
                        }
                    ]
                },
                options: { 
                    responsive: true, 
                    maintainAspectRatio: false, 
                    interaction: { mode: 'index', intersect: false },
                    plugins: {
                        legend: { position: 'top', labels: { usePointStyle: true, boxWidth: 8 } },
                        tooltip: { padding: 12, cornerRadius: 8, backgroundColor: 'rgba(15, 23, 42, 0.9)' }
                    },
                    scales: { 
                        x: { grid: { display: false } },
                        y: { type: 'linear', position: 'left', min: 70, max: 100, grid: { borderDash: [4, 4] } }, 
                        y1: { type: 'linear', position: 'right', min: 0, max: 15, grid: { display: false } } 
                    } 
                }
            });
        }
    </script>
</body>
</html>