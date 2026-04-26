<?php 
if (session_status() === PHP_SESSION_NONE) { session_start(); }

// --- ROBUST DATABASE CONNECTION (PDO) ---
$host = 'localhost';
$dbname = 'myhmsdb';
$db_user = 'root';
$db_pass = '';

try {
    $pdo = new PDO("mysql:host=$host;dbname=$dbname", $db_user, $db_pass);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    $pdo->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);

    // --- AUTO-MIGRATION SAFEGUARD ---
    $columns = $pdo->query("SHOW COLUMNS FROM appointmenttb")->fetchAll(PDO::FETCH_COLUMN);
    if (!in_array('current_status', $columns)) {
        $pdo->exec("ALTER TABLE appointmenttb ADD COLUMN current_status VARCHAR(20) DEFAULT 'Outpatient'");
        $pdo->exec("ALTER TABLE appointmenttb ADD COLUMN bed_number VARCHAR(10) DEFAULT 'UNASSIGNED'");
        $pdo->exec("ALTER TABLE appointmenttb ADD COLUMN oxygen_level INT DEFAULT 98");
        $pdo->exec("ALTER TABLE appointmenttb ADD COLUMN oxygen_liters FLOAT DEFAULT 0.0");
        $pdo->exec("ALTER TABLE appointmenttb ADD COLUMN heart_rate INT DEFAULT 75");
        $pdo->exec("ALTER TABLE appointmenttb ADD COLUMN auto_o2_mode TINYINT(1) DEFAULT 0");
        $pdo->exec("ALTER TABLE appointmenttb ADD COLUMN alert_threshold INT DEFAULT 90");
        $pdo->exec("ALTER TABLE appointmenttb ADD COLUMN admission_date DATETIME NULL");
        $pdo->exec("ALTER TABLE appointmenttb ADD COLUMN daily_update TEXT NULL");
        $pdo->exec("UPDATE appointmenttb SET current_status = 'Outpatient'");
    }

} catch(PDOException $e) {
    die("<div style='background-color:#fee2e2; color:#991b1b; padding:20px; text-align:center; font-family:sans-serif;'>System Failure: Database connection lost. Please ensure 'myhmsdb' exists.</div>");
}

$notification = '';

// --- ACTION: ADD DOCTOR ---
if(isset($_POST['docsub'])) {
    $stmt = $pdo->prepare("INSERT INTO doctb (username, password, email, spec, docFees) VALUES (?, ?, ?, ?, ?)");
    if($stmt->execute([$_POST['doctor'], $_POST['dpassword'], $_POST['demail'], $_POST['special'], $_POST['docFees']])) {
        $notification = "<div class='alert-success'><i class='fa-solid fa-circle-check mr-2'></i> New Doctor successfully added to the roster.</div>";
    } else {
        $notification = "<div class='alert-error'><i class='fa-solid fa-triangle-exclamation mr-2'></i> Error adding doctor.</div>";
    }
}

// --- ACTION: DELETE DOCTOR ---
if(isset($_POST['docsub1'])) {
    $stmt = $pdo->prepare("DELETE FROM doctb WHERE email = ?");
    if($stmt->execute([$_POST['demail']])) {
        $notification = "<div class='alert-success'><i class='fa-solid fa-circle-check mr-2'></i> Doctor successfully removed from the system.</div>";
    } else {
        $notification = "<div class='alert-error'><i class='fa-solid fa-triangle-exclamation mr-2'></i> Error removing doctor.</div>";
    }
}

// --- CRASH-PROOF DATA FETCHING ---
try { $active_admissions = $pdo->query("SELECT *, DATEDIFF(CURRENT_TIMESTAMP, admission_date) AS days_occupied FROM appointmenttb WHERE current_status = 'Admitted' AND userStatus = 1 AND doctorStatus = 1")->fetchAll(); } catch(Exception $e) { $active_admissions = []; }
try { $all_patients = $pdo->query("SELECT * FROM patreg ORDER BY pid DESC")->fetchAll(); } catch(Exception $e) { $all_patients = []; }
try { $docs = $pdo->query("SELECT * FROM doctb")->fetchAll(); } catch(Exception $e) { $docs = []; }
try { $appts = $pdo->query("SELECT * FROM appointmenttb ORDER BY appdate DESC, apptime DESC")->fetchAll(); } catch(Exception $e) { $appts = []; }
try { $prescriptions = $pdo->query("SELECT * FROM prestb ORDER BY appdate DESC, apptime DESC")->fetchAll(); } catch(Exception $e) { $prescriptions = []; }
try { $messages = $pdo->query("SELECT * FROM contact ORDER BY id DESC")->fetchAll(); } catch(Exception $e) { $messages = []; }

?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
    <title>KASSAH Vitals Admin | Command Center</title>
    
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    
    <script>
        tailwind.config = {
            theme: {
                extend: {
                    colors: { brand: { 50: '#f0fdfa', 400: '#2dd4bf', 500: '#14b8a6', 600: '#0d9488', 700: '#0f766e' } }
                }
            }
        }
    </script>
    <style>
        body { background: linear-gradient(135deg, #f8fafc 0%, #e0f2fe 100%); color: #334155; font-family: 'Plus Jakarta Sans', sans-serif;}
        .glass-card { background: rgba(255, 255, 255, 0.85); backdrop-filter: blur(12px); border: 1px solid rgba(255,255,255,0.8); box-shadow: 0 10px 25px rgba(0,0,0,0.05); }
        .tab-content { display: none; animation: slideUp 0.3s ease-out; }
        .tab-content.active { display: block; }
        @keyframes slideUp { from { opacity: 0; transform: translateY(10px); } to { opacity: 1; transform: translateY(0); } }
        ::-webkit-scrollbar { width: 6px; height: 6px;}
        ::-webkit-scrollbar-track { background: transparent; }
        ::-webkit-scrollbar-thumb { background: #cbd5e1; border-radius: 4px; }
        
        .alert-success { background: #d1fae5; color: #065f46; padding: 12px 16px; border-radius: 8px; margin-bottom: 24px; border: 1px solid #a7f3d0; display: flex; align-items: center;}
        .alert-error { background: #fee2e2; color: #991b1b; padding: 12px 16px; border-radius: 8px; margin-bottom: 24px; border: 1px solid #fecaca; display: flex; align-items: center;}
    </style>
</head>

<body class="h-screen w-full flex flex-col overflow-hidden">

    <nav class="glass-card h-20 px-8 flex justify-between items-center shrink-0 z-40 border-b border-white/50 shadow-sm">
        <div class="flex items-center gap-3">
            <div class="bg-gradient-to-br from-slate-700 to-slate-900 text-white p-2.5 rounded-xl shadow-lg">
                <i class="fa-solid fa-shield-halved text-2xl"></i>
            </div>
            <h1 class="text-2xl font-extrabold tracking-tight text-slate-800">KASSAH Vitals <span class="text-slate-400 font-medium">Admin</span></h1>
        </div>
        <div class="flex items-center gap-6">
            <div class="text-right hidden md:block">
                <p class="text-sm font-bold text-slate-800">System Administrator</p>
                <p class="text-xs text-brand-600 bg-brand-50 font-semibold px-2 py-0.5 rounded-full inline-block mt-1">
                    <i class="fa-solid fa-circle text-[8px] animate-pulse mr-1"></i> Core Online
                </p>
            </div>
            <a href="logout1.php" class="bg-white border border-slate-200 text-slate-600 hover:text-red-600 px-5 py-2.5 rounded-xl text-sm font-bold transition shadow-sm hover:shadow">
                <i class="fa fa-power-off mr-2"></i> Logout
            </a>
        </div>
    </nav>

    <div class="flex w-full h-[calc(100vh-5rem)] overflow-hidden">
        
        <aside class="w-72 shrink-0 glass-card p-6 hidden md:flex flex-col border-r border-white/50 z-30 overflow-y-auto">
            <p class="text-xs font-bold text-slate-400 uppercase tracking-widest mb-4">Dashboard</p>
            <nav class="flex flex-col gap-2 mb-8" id="nav-tabs">
                <button onclick="switchTab('telemetry')" class="tab-btn active-tab w-full text-left px-4 py-3 rounded-xl bg-brand-50 text-brand-700 font-bold transition" data-target="telemetry">
                    <i class="fa-solid fa-bed-pulse text-center w-6"></i> Live Ward Status
                </button>
                <button onclick="switchTab('citizens')" class="tab-btn w-full text-left px-4 py-3 rounded-xl text-slate-600 hover:bg-slate-50 hover:text-slate-900 font-semibold transition" data-target="citizens">
                    <i class="fa-solid fa-users text-center w-6"></i> Patient Directory
                </button>
                <button onclick="switchTab('roster')" class="tab-btn w-full text-left px-4 py-3 rounded-xl text-slate-600 hover:bg-slate-50 hover:text-slate-900 font-semibold transition" data-target="roster">
                    <i class="fa-solid fa-user-doctor text-center w-6"></i> Doctor Roster
                </button>
                <button onclick="switchTab('quests')" class="tab-btn w-full text-left px-4 py-3 rounded-xl text-slate-600 hover:bg-slate-50 hover:text-slate-900 font-semibold transition" data-target="quests">
                    <i class="fa-solid fa-calendar-check text-center w-6"></i> All Appointments
                </button>
            </nav>

            <p class="text-xs font-bold text-slate-400 uppercase tracking-widest mb-4">Records</p>
            <nav class="flex flex-col gap-2 mb-8">
                <button onclick="switchTab('prescriptions')" class="tab-btn w-full text-left px-4 py-3 rounded-xl text-slate-600 hover:bg-slate-50 hover:text-slate-900 font-semibold transition" data-target="prescriptions">
                    <i class="fa-solid fa-file-prescription text-center w-6"></i> Medical Logs
                </button>
                <button onclick="switchTab('messages')" class="tab-btn w-full text-left px-4 py-3 rounded-xl text-slate-600 hover:bg-slate-50 hover:text-slate-900 font-semibold transition" data-target="messages">
                    <i class="fa-solid fa-envelope text-center w-6"></i> Contact Messages
                </button>
            </nav>

            <p class="text-xs font-bold text-slate-400 uppercase tracking-widest mb-4 mt-auto">System</p>
            <nav class="flex flex-col gap-2">
                <button onclick="switchTab('config')" class="tab-btn w-full text-left px-4 py-3 rounded-xl text-slate-600 hover:bg-slate-50 hover:text-slate-900 font-semibold transition" data-target="config">
                    <i class="fa-solid fa-sliders text-center w-6"></i> Configuration
                </button>
            </nav>
        </aside>

        <main class="flex-1 w-full h-full p-6 md:p-8 overflow-y-auto relative z-10">
            
            <?= $notification ?>

            <div id="telemetry" class="tab-content active max-w-screen-2xl mx-auto">
                <div class="mb-8 flex justify-between items-end">
                    <div>
                        <h2 class="text-3xl font-extrabold text-slate-800">Live Ward Status</h2>
                        <p class="text-slate-500 font-medium mt-1">Read-only real-time monitoring of admitted patients.</p>
                    </div>
                    <button onclick="window.location.reload();" class="bg-white border border-slate-200 text-brand-600 px-4 py-2 rounded-xl text-sm font-bold shadow-sm hover:shadow hover:bg-slate-50 transition">
                        <i class="fa-solid fa-rotate-right mr-1"></i> Pull Live Data
                    </button>
                </div>
                
                <div class="grid grid-cols-1 xl:grid-cols-3 lg:grid-cols-2 gap-6">
                    <?php 
                    if(count($active_admissions) > 0):
                        foreach($active_admissions as $pat): 
                            $isCritical = ($pat['oxygen_level'] <= $pat['alert_threshold']);
                            $days = max(1, $pat['days_occupied']);
                            
                            // Check who updated this patient last
                            $last_log_stmt = $pdo->prepare("SELECT changed_by FROM patient_vitals_log WHERE appt_id = ? ORDER BY recorded_at DESC LIMIT 1");
                            $last_log_stmt->execute([$pat['ID']]);
                            $last_modifier = $last_log_stmt->fetchColumn() ?: "System";
                    ?>
                    <div class="bg-white rounded-2xl shadow-sm border <?= $isCritical ? 'border-red-300 ring-2 ring-red-100 bg-red-50/30' : 'border-slate-200' ?> relative overflow-hidden transition-all flex flex-col">
                        
                        <div class="p-6 pb-4">
                            <div class="flex justify-between items-start mb-6">
                                <div>
                                    <span class="bg-indigo-50 text-indigo-700 font-bold px-3 py-1 rounded-full text-xs mb-2 inline-block"><i class="fa-solid fa-bed mr-1"></i> <?= htmlspecialchars($pat['bed_number']) ?></span>
                                    <h3 class="text-xl font-bold text-slate-800"><?= htmlspecialchars($pat['fname'] . ' ' . $pat['lname']) ?></h3>
                                    <p class="text-slate-500 text-xs">Dr. <?= htmlspecialchars($pat['doctor']) ?> • Appt #<?= $pat['ID'] ?></p>
                                </div>
                                <div class="text-right">
                                    <span class="bg-slate-100 text-slate-600 text-xs px-2 py-1 rounded font-mono">PID: #<?= $pat['pid'] ?></span>
                                    <p class="text-brand-600 text-xs font-bold mt-2">DAY <?= $days ?></p>
                                </div>
                            </div>
                            
                            <!-- Display Current Vitals -->
                            <div class="grid grid-cols-2 gap-4 mb-4">
                                <div class="bg-slate-50 p-4 rounded-xl border border-slate-100">
                                    <p class="text-slate-400 text-[10px] uppercase font-bold tracking-widest mb-1">O2 Saturation</p>
                                    <span class="text-2xl font-black <?= $isCritical ? 'text-red-600 animate-pulse' : 'text-slate-700' ?>"><?= $pat['oxygen_level'] ?>%</span>
                                    <div class="w-full h-1.5 bg-slate-200 rounded-full mt-2 overflow-hidden">
                                        <div class="h-full <?= $isCritical ? 'bg-red-500' : 'bg-brand-500' ?>" style="width: <?= $pat['oxygen_level'] ?>%;"></div>
                                    </div>
                                </div>
                                <div class="bg-slate-50 p-4 rounded-xl border border-slate-100">
                                    <p class="text-slate-400 text-[10px] uppercase font-bold tracking-widest mb-1">Heart Rate</p>
                                    <span class="text-2xl font-black text-slate-700"><?= $pat['heart_rate'] ?> <span class="text-xs text-slate-400 font-normal">bpm</span></span>
                                </div>
                            </div>
                        </div>

                        <!-- Read-Only Vitals & Notes View -->
                        <div class="mt-auto pt-4 pb-6 px-6 bg-slate-50 border-t border-slate-100 flex flex-col gap-4">
                            <div class="flex justify-between items-center">
                                <span class="text-[10px] font-bold text-slate-500 uppercase tracking-wider"><i class="fa-solid fa-notes-medical text-brand-500 mr-1"></i> Clinical Status</span>
                                <span class="text-[10px] font-bold text-slate-400"><i class="fa-solid fa-clock-rotate-left mr-1"></i> Last Edited: <?= htmlspecialchars($last_modifier) ?></span>
                            </div>

                            <div>
                                <label class="text-[10px] font-bold text-slate-400 uppercase mb-1 block">Patient Condition / Notes</label>
                                <div class="w-full bg-white border border-slate-200 rounded-lg px-3 py-2 text-sm text-slate-600 italic min-h-[3rem]">
                                    <?= !empty($pat['daily_update']) ? nl2br(htmlspecialchars($pat['daily_update'])) : 'No recent updates provided.' ?>
                                </div>
                            </div>
                            
                            <div class="flex justify-between items-end">
                                <label class="text-[10px] font-bold text-slate-400 uppercase tracking-wider">Current O2 Flow Rate</label>
                                <span class="text-xl font-black text-brand-600"><?= $pat['oxygen_liters'] ?> <span class="text-sm font-bold text-slate-400">L/min</span></span>
                            </div>
                        </div>

                    </div>
                    <?php endforeach; else: ?>
                        <div class="col-span-full bg-white p-12 rounded-2xl border border-slate-200 text-center text-slate-500">
                            <i class="fa-solid fa-bed text-4xl text-slate-300 mb-3 block"></i>
                            <p class="font-medium">No patients are currently admitted to the inpatient ward.</p>
                        </div>
                    <?php endif; ?>
                </div>
            </div>

            <div id="citizens" class="tab-content max-w-screen-2xl mx-auto">
                <div class="mb-8 flex justify-between items-end">
                    <div>
                        <h2 class="text-3xl font-extrabold text-slate-800">Patient Directory</h2>
                        <p class="text-slate-500 font-medium mt-1">Master database of all registered patients.</p>
                    </div>
                    <form action="patientsearch.php" method="post" class="flex gap-2 relative">
                        <i class="fa-solid fa-search absolute left-4 top-1/2 transform -translate-y-1/2 text-slate-400"></i>
                        <input type="text" name="patient_contact" placeholder="Search by Contact..." required class="pl-10 pr-4 py-2 bg-white border border-slate-200 rounded-xl text-sm focus:outline-none focus:ring-2 focus:ring-brand-500 shadow-sm w-64">
                        <button type="submit" name="patient_search_submit" class="bg-slate-800 text-white px-4 py-2 rounded-xl text-sm font-bold hover:bg-slate-700 transition shadow-sm">Search</button>
                    </form>
                </div>
                
                <div class="bg-white rounded-2xl border border-slate-200 shadow-sm overflow-hidden">
                    <div class="overflow-x-auto">
                        <table class="w-full text-left text-sm whitespace-nowrap">
                            <thead class="bg-slate-50 border-b border-slate-200 text-slate-500 font-bold uppercase tracking-wider text-[11px]">
                                <tr>
                                    <th class="p-4">PID</th>
                                    <th class="p-4">Full Name</th>
                                    <th class="p-4">Gender</th>
                                    <th class="p-4">Contact Data</th>
                                    <th class="p-4 text-right">Medical History</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-slate-100 text-slate-700 font-medium">
                                <?php foreach($all_patients as $pat): ?>
                                <tr class="hover:bg-slate-50 transition">
                                    <td class="p-4 font-mono text-slate-400">#<?= htmlspecialchars($pat['pid']) ?></td>
                                    <td class="p-4 font-bold text-slate-900"><?= htmlspecialchars($pat['fname'] . ' ' . $pat['lname']) ?></td>
                                    <td class="p-4"><span class="bg-slate-100 text-slate-600 px-2 py-1 rounded text-xs"><?= htmlspecialchars($pat['gender']) ?></span></td>
                                    <td class="p-4">
                                        <?= htmlspecialchars($pat['contact']) ?><br>
                                        <span class="text-xs text-slate-500 font-normal"><?= htmlspecialchars($pat['email']) ?></span>
                                    </td>
                                    <td class="p-4 text-right">
                                        <button onclick="openHistoryModal(<?= $pat['pid'] ?>)" class="bg-white border border-slate-200 text-brand-600 hover:bg-brand-50 px-3 py-1.5 rounded-lg text-xs font-bold transition shadow-sm">
                                            <i class="fa-solid fa-folder-open mr-1"></i> View Records
                                        </button>
                                    </td>
                                </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                </div>

                <?php foreach($all_patients as $pat): ?>
                <div id="modal-<?= $pat['pid'] ?>" class="hidden fixed inset-0 z-[100] flex justify-center items-center backdrop-blur-md bg-slate-900/60 p-4">
                    <div class="bg-white w-full max-w-4xl max-h-[85vh] rounded-3xl overflow-hidden flex flex-col shadow-2xl border border-slate-200">
                        <div class="p-6 border-b border-slate-100 flex justify-between items-center bg-slate-50">
                            <div>
                                <h3 class="font-bold text-2xl text-slate-800">Patient Dossier</h3>
                                <p class="text-slate-500 text-sm"><?= htmlspecialchars($pat['fname'] . ' ' . $pat['lname']) ?> • PID #<?= $pat['pid'] ?></p>
                            </div>
                            <button onclick="closeHistoryModal(<?= $pat['pid'] ?>)" class="w-10 h-10 bg-white border border-slate-200 rounded-full flex items-center justify-center text-slate-500 hover:text-red-500 hover:border-red-200 transition shadow-sm"><i class="fa-solid fa-xmark"></i></button>
                        </div>
                        <div class="p-6 overflow-y-auto bg-white flex-1">
                            <h4 class="text-brand-600 font-bold uppercase tracking-wider mb-4 text-xs"><i class="fa-solid fa-clock-rotate-left mr-2"></i>Prescription History</h4>
                            
                            <div class="border border-slate-200 rounded-xl overflow-hidden">
                                <table class="w-full text-left text-sm">
                                    <thead class="bg-slate-50 border-b border-slate-200 text-slate-500 font-bold uppercase tracking-wider text-[10px]">
                                        <tr><th class="p-3">Date</th><th class="p-3">Doctor</th><th class="p-3">Diagnosis</th><th class="p-3">Allergy</th><th class="p-3">Prescription</th></tr>
                                    </thead>
                                    <tbody class="divide-y divide-slate-100 text-slate-700">
                                        <?php 
                                        $history = [];
                                        try {
                                            $stmt = $pdo->prepare("SELECT * FROM prestb WHERE pid = ? ORDER BY appdate DESC");
                                            $stmt->execute([$pat['pid']]);
                                            $history = $stmt->fetchAll();
                                        } catch(Exception $e) {}
                                        
                                        if($history):
                                            foreach($history as $rec): ?>
                                            <tr class="hover:bg-slate-50">
                                                <td class="p-3 whitespace-nowrap text-slate-500"><?= htmlspecialchars($rec['appdate']) ?></td>
                                                <td class="p-3 font-medium text-slate-800">Dr. <?= htmlspecialchars($rec['doctor']) ?></td>
                                                <td class="p-3 text-rose-600 font-medium"><?= htmlspecialchars($rec['disease']) ?></td>
                                                <td class="p-3 text-xs"><?= htmlspecialchars($rec['allergy']) ?></td>
                                                <td class="p-3 font-mono text-xs text-slate-500 bg-slate-50"><?= htmlspecialchars($rec['prescription']) ?></td>
                                            </tr>
                                        <?php endforeach; else: ?>
                                            <tr><td colspan="5" class="p-6 text-center text-slate-400 italic">No prescription records found for this patient.</td></tr>
                                        <?php endif; ?>
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>
                <?php endforeach; ?>
            </div>

            <div id="roster" class="tab-content max-w-screen-2xl mx-auto">
                <div class="mb-8 flex justify-between items-end">
                    <div>
                        <h2 class="text-3xl font-extrabold text-slate-800">Doctor Roster</h2>
                        <p class="text-slate-500 font-medium mt-1">Manage active medical personnel and specialties.</p>
                    </div>
                    <form action="doctorsearch.php" method="post" class="flex gap-2 relative">
                        <i class="fa-solid fa-search absolute left-4 top-1/2 transform -translate-y-1/2 text-slate-400"></i>
                        <input type="email" name="doctor_contact" placeholder="Search Doctor Email..." required class="pl-10 pr-4 py-2 bg-white border border-slate-200 rounded-xl text-sm focus:outline-none focus:ring-2 focus:ring-brand-500 shadow-sm w-64">
                        <button type="submit" name="doctor_search_submit" class="bg-slate-800 text-white px-4 py-2 rounded-xl text-sm font-bold hover:bg-slate-700 transition shadow-sm">Search</button>
                    </form>
                </div>
                
                <div class="bg-white rounded-2xl border border-slate-200 shadow-sm overflow-hidden">
                    <table class="w-full text-left whitespace-nowrap text-sm">
                        <thead class="bg-slate-50 border-b border-slate-200 text-slate-500 font-bold uppercase tracking-wider text-[11px]">
                            <tr><th class="p-4">Name</th><th class="p-4">Specialization</th><th class="p-4">Email Contact</th><th class="p-4 text-right">Consult Fee</th></tr>
                        </thead>
                        <tbody class="divide-y divide-slate-100 text-slate-700">
                            <?php foreach($docs as $doc): ?>
                            <tr class="hover:bg-slate-50 transition">
                                <td class="p-4 font-bold text-slate-900">Dr. <?= htmlspecialchars($doc['username']) ?></td>
                                <td class="p-4"><span class="bg-indigo-50 text-indigo-700 px-2.5 py-1 rounded-full text-xs font-semibold"><?= htmlspecialchars($doc['spec']) ?></span></td>
                                <td class="p-4 text-slate-500"><?= htmlspecialchars($doc['email']) ?></td>
                                <td class="p-4 text-emerald-600 font-bold text-right font-mono">₹<?= htmlspecialchars($doc['docFees']) ?></td>
                            </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </div>

            <div id="quests" class="tab-content max-w-screen-2xl mx-auto">
                <div class="mb-8 flex justify-between items-end">
                    <div>
                        <h2 class="text-3xl font-extrabold text-slate-800">All Appointments</h2>
                        <p class="text-slate-500 font-medium mt-1">System-wide view of all patient bookings.</p>
                    </div>
                    <form action="appsearch.php" method="post" class="flex gap-2 relative">
                        <i class="fa-solid fa-search absolute left-4 top-1/2 transform -translate-y-1/2 text-slate-400"></i>
                        <input type="text" name="app_contact" placeholder="Search by Contact..." required class="pl-10 pr-4 py-2 bg-white border border-slate-200 rounded-xl text-sm focus:outline-none focus:ring-2 focus:ring-brand-500 shadow-sm w-64">
                        <button type="submit" name="app_search_submit" class="bg-slate-800 text-white px-4 py-2 rounded-xl text-sm font-bold hover:bg-slate-700 transition shadow-sm">Search</button>
                    </form>
                </div>
                
                <div class="bg-white rounded-2xl border border-slate-200 shadow-sm overflow-hidden overflow-x-auto">
                    <table class="w-full text-left whitespace-nowrap text-sm">
                        <thead class="bg-slate-50 border-b border-slate-200 text-slate-500 font-bold uppercase tracking-wider text-[11px]">
                            <tr>
                                <th class="p-4">Appt ID</th><th class="p-4">Patient Name</th><th class="p-4">Assigned Doctor</th>
                                <th class="p-4">Date & Time</th><th class="p-4">Fee</th><th class="p-4 text-center">Status</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-100 text-slate-700 font-medium">
                            <?php foreach($appts as $appt): ?>
                            <tr class="hover:bg-slate-50 transition">
                                <td class="p-4 font-mono text-slate-400 text-xs">A#<?= htmlspecialchars($appt['ID']) ?></td>
                                <td class="p-4 font-bold text-slate-900"><?= htmlspecialchars($appt['fname'] . ' ' . $appt['lname']) ?></td>
                                <td class="p-4 text-brand-600 font-semibold">Dr. <?= htmlspecialchars($appt['doctor']) ?></td>
                                <td class="p-4 text-slate-500 text-xs"><?= htmlspecialchars($appt['appdate']) ?><br><span class="text-slate-400"><?= htmlspecialchars($appt['apptime']) ?></span></td>
                                <td class="p-4 font-mono">₹<?= htmlspecialchars($appt['docFees']) ?></td>
                                <td class="p-4 text-center">
                                    <?php 
                                        if($appt['userStatus'] == 1 && $appt['doctorStatus'] == 1) {
                                            echo '<span class="bg-emerald-50 text-emerald-700 px-2.5 py-1 rounded-full text-[10px] uppercase font-bold border border-emerald-200">Active</span>';
                                        } elseif($appt['userStatus'] == 0) {
                                            echo '<span class="bg-rose-50 text-rose-700 px-2.5 py-1 rounded-full text-[10px] uppercase font-bold border border-rose-200">Cancelled by User</span>';
                                        } else {
                                            echo '<span class="bg-amber-50 text-amber-700 px-2.5 py-1 rounded-full text-[10px] uppercase font-bold border border-amber-200">Cancelled by Doctor</span>';
                                        }
                                    ?>
                                </td>
                            </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </div>

            <div id="prescriptions" class="tab-content max-w-screen-2xl mx-auto">
                <div class="mb-8">
                    <h2 class="text-3xl font-extrabold text-slate-800">Global Medical Logs</h2>
                    <p class="text-slate-500 font-medium mt-1">Archive of all written prescriptions across the hospital.</p>
                </div>
                
                <div class="bg-white rounded-2xl border border-slate-200 shadow-sm overflow-hidden overflow-x-auto">
                    <table class="w-full text-left whitespace-nowrap text-sm">
                        <thead class="bg-slate-50 border-b border-slate-200 text-slate-500 font-bold uppercase tracking-wider text-[11px]">
                            <tr><th class="p-4">Doctor</th><th class="p-4">Patient</th><th class="p-4">Date/Time</th><th class="p-4">Diagnosis</th><th class="p-4">Allergy</th><th class="p-4">Prescription</th></tr>
                        </thead>
                        <tbody class="divide-y divide-slate-100 text-slate-700 font-medium">
                            <?php foreach($prescriptions as $p): ?>
                            <tr class="hover:bg-slate-50 transition">
                                <td class="p-4 font-bold text-slate-900">Dr. <?= htmlspecialchars($p['doctor']) ?></td>
                                <td class="p-4 text-brand-700">PID #<?= htmlspecialchars($p['pid']) ?> <br><span class="text-xs text-slate-500 font-normal"><?= htmlspecialchars($p['fname'] . ' ' . $p['lname']) ?></span></td>
                                <td class="p-4 text-slate-500 text-xs"><?= htmlspecialchars($p['appdate']) ?><br><span class="text-slate-400"><?= htmlspecialchars($p['apptime']) ?></span></td>
                                <td class="p-4 text-rose-600"><?= htmlspecialchars($p['disease']) ?></td>
                                <td class="p-4 text-xs text-slate-500"><?= htmlspecialchars($p['allergy']) ?></td>
                                <td class="p-4 font-mono text-xs max-w-xs truncate bg-slate-50 rounded" title="<?= htmlspecialchars($p['prescription']) ?>"><?= htmlspecialchars($p['prescription']) ?></td>
                            </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </div>

            <div id="messages" class="tab-content max-w-screen-2xl mx-auto">
                <div class="mb-8 flex justify-between items-end">
                    <div>
                        <h2 class="text-3xl font-extrabold text-slate-800">Contact Messages</h2>
                        <p class="text-slate-500 font-medium mt-1">Inquiries and messages submitted from the public portal.</p>
                    </div>
                    <form action="messearch.php" method="post" class="flex gap-2 relative">
                        <i class="fa-solid fa-search absolute left-4 top-1/2 transform -translate-y-1/2 text-slate-400"></i>
                        <input type="text" name="mes_contact" placeholder="Search by Contact..." required class="pl-10 pr-4 py-2 bg-white border border-slate-200 rounded-xl text-sm focus:outline-none focus:ring-2 focus:ring-brand-500 shadow-sm w-64">
                        <button type="submit" name="mes_search_submit" class="bg-slate-800 text-white px-4 py-2 rounded-xl text-sm font-bold hover:bg-slate-700 transition shadow-sm">Search</button>
                    </form>
                </div>
                
                <div class="bg-white rounded-2xl border border-slate-200 shadow-sm overflow-hidden overflow-x-auto">
                    <table class="w-full text-left text-sm">
                        <thead class="bg-slate-50 border-b border-slate-200 text-slate-500 font-bold uppercase tracking-wider text-[11px]">
                            <tr><th class="p-4 w-1/4">Sender Details</th><th class="p-4 w-3/4">Message Content</th></tr>
                        </thead>
                        <tbody class="divide-y divide-slate-100 text-slate-700">
                            <?php foreach($messages as $msg): ?>
                            <tr class="hover:bg-slate-50 transition">
                                <td class="p-4 align-top">
                                    <span class="font-bold text-slate-900 block mb-1"><?= htmlspecialchars($msg['name']) ?></span>
                                    <span class="text-xs text-slate-500 block"><i class="fa-solid fa-envelope mr-1 w-3"></i> <?= htmlspecialchars($msg['email']) ?></span>
                                    <span class="text-xs text-slate-500 block"><i class="fa-solid fa-phone mr-1 w-3"></i> <?= htmlspecialchars($msg['contact']) ?></span>
                                </td>
                                <td class="p-4 text-slate-600 bg-slate-50/50 italic leading-relaxed">
                                    "<?= htmlspecialchars($msg['message']) ?>"
                                </td>
                            </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </div>

            <div id="config" class="tab-content max-w-screen-2xl mx-auto">
                <div class="mb-8">
                    <h2 class="text-3xl font-extrabold text-slate-800">System Configuration</h2>
                    <p class="text-slate-500 font-medium mt-1">Manage hospital staff and administrative settings.</p>
                </div>
                
                <div class="grid grid-cols-1 lg:grid-cols-2 gap-8">
                    
                    <div class="bg-white p-8 rounded-3xl border border-slate-200 shadow-sm">
                        <div class="w-12 h-12 bg-emerald-50 text-emerald-600 rounded-xl flex items-center justify-center text-xl mb-6">
                            <i class="fa-solid fa-user-plus"></i>
                        </div>
                        <h3 class="text-xl font-bold text-slate-800 mb-6">Onboard New Doctor</h3>
                        
                        <form method="post" action="" class="space-y-5" onsubmit="return validatePasswords()">
                            <div>
                                <label class="block text-xs font-bold text-slate-500 uppercase tracking-wider mb-2">Doctor Name</label>
                                <input type="text" name="doctor" required class="w-full bg-slate-50 border border-slate-200 rounded-xl px-4 py-3 text-sm focus:outline-none focus:ring-2 focus:ring-brand-500 transition">
                            </div>
                            
                            <div>
                                <label class="block text-xs font-bold text-slate-500 uppercase tracking-wider mb-2">Specialization</label>
                                <select name="special" required class="w-full bg-slate-50 border border-slate-200 rounded-xl px-4 py-3 text-sm focus:outline-none focus:ring-2 focus:ring-brand-500 transition appearance-none">
                                    <option value="" disabled selected>Select Medical Field</option>
                                    <option value="General">General Physician</option>
                                    <option value="Cardiologist">Cardiologist</option>
                                    <option value="Neurologist">Neurologist</option>
                                    <option value="Pediatrician">Pediatrician</option>
                                </select>
                            </div>
                            
                            <div>
                                <label class="block text-xs font-bold text-slate-500 uppercase tracking-wider mb-2">Email Address</label>
                                <input type="email" name="demail" required class="w-full bg-slate-50 border border-slate-200 rounded-xl px-4 py-3 text-sm focus:outline-none focus:ring-2 focus:ring-brand-500 transition">
                            </div>
                            
                            <div class="grid grid-cols-2 gap-4">
                                <div>
                                    <label class="block text-xs font-bold text-slate-500 uppercase tracking-wider mb-2">Password</label>
                                    <input type="password" name="dpassword" id="dpass" required class="w-full bg-slate-50 border border-slate-200 rounded-xl px-4 py-3 text-sm focus:outline-none focus:ring-2 focus:ring-brand-500 transition">
                                </div>
                                <div>
                                    <label class="block text-xs font-bold text-slate-500 uppercase tracking-wider mb-2">Confirm</label>
                                    <input type="password" name="cdpassword" id="cdpass" onkeyup="checkPass()" required class="w-full bg-slate-50 border border-slate-200 rounded-xl px-4 py-3 text-sm focus:outline-none focus:ring-2 focus:ring-brand-500 transition">
                                </div>
                            </div>
                            <span id="pass-msg" class="text-xs font-bold block"></span>
                            
                            <div>
                                <label class="block text-xs font-bold text-slate-500 uppercase tracking-wider mb-2">Consultancy Fee (₹)</label>
                                <input type="number" name="docFees" required class="w-full bg-slate-50 border border-slate-200 rounded-xl px-4 py-3 text-sm focus:outline-none focus:ring-2 focus:ring-brand-500 transition">
                            </div>
                            
                            <button type="submit" name="docsub" class="w-full bg-slate-900 hover:bg-slate-800 text-white font-bold py-3.5 rounded-xl transition shadow-md mt-4">
                                Create Account
                            </button>
                        </form>
                    </div>

                    <div class="bg-white p-8 rounded-3xl border border-slate-200 shadow-sm h-max">
                        <div class="w-12 h-12 bg-rose-50 text-rose-600 rounded-xl flex items-center justify-center text-xl mb-6">
                            <i class="fa-solid fa-user-minus"></i>
                        </div>
                        <h3 class="text-xl font-bold text-slate-800 mb-2">Terminate Contract</h3>
                        <p class="text-slate-500 text-sm mb-6">Warning: Removing a doctor revokes their access to the KASSAH Vitals Clinical panel immediately.</p>
                        
                        <form method="post" action="" class="space-y-5" onsubmit="return confirm('WARNING: Are you sure you want to completely remove this doctor from the system?');">
                            <div>
                                <label class="block text-xs font-bold text-slate-500 uppercase tracking-wider mb-2">Doctor Email</label>
                                <input type="email" name="demail" placeholder="doctor@KASSAH Vitals.org" required class="w-full bg-slate-50 border border-slate-200 rounded-xl px-4 py-3 text-sm focus:outline-none focus:ring-2 focus:ring-rose-500 transition">
                            </div>
                            <button type="submit" name="docsub1" class="w-full bg-white border border-rose-200 text-rose-600 hover:bg-rose-50 font-bold py-3.5 rounded-xl transition shadow-sm">
                                Revoke Access & Delete
                            </button>
                        </form>
                    </div>

                </div>
            </div>

        </main>
    </div>

    <script>
        // Bulletproof Tab Switching
        function switchTab(tabId) {
            // Hide all tab content
            document.querySelectorAll('.tab-content').forEach(el => {
                el.style.display = 'none';
                el.classList.remove('active');
            });
            
            // Reset all buttons
            document.querySelectorAll('.tab-btn').forEach(btn => {
                btn.className = "tab-btn w-full text-left px-4 py-3 rounded-xl text-slate-600 hover:bg-slate-50 hover:text-slate-900 font-semibold transition";
            });
            
            // Show Target Tab
            const targetContent = document.getElementById(tabId);
            if(targetContent) {
                targetContent.style.display = 'block';
                targetContent.classList.add('active');
            }
            
            // Highlight Target Button
            const activeBtn = document.querySelector(`button[data-target="${tabId}"]`);
            if(activeBtn) {
                activeBtn.className = "tab-btn active-tab w-full text-left px-4 py-3 rounded-xl bg-brand-50 text-brand-700 font-bold transition";
            }
        }

        // Initialize First Tab Safely
        document.addEventListener("DOMContentLoaded", () => {
            switchTab('telemetry');
        });

        // Clean Password Matcher Visualizer
        function checkPass() {
            const pass = document.getElementById('dpass').value;
            const confirm = document.getElementById('cdpass').value;
            const msg = document.getElementById('pass-msg');
            
            if(confirm === "") { msg.innerHTML = ""; return; }
            
            if(pass === confirm) {
                msg.style.color = "#059669"; // emerald-600
                msg.innerHTML = '<i class="fa-solid fa-check mr-1"></i> Passwords match';
            } else {
                msg.style.color = "#dc2626"; // red-600
                msg.innerHTML = '<i class="fa-solid fa-xmark mr-1"></i> Passwords do not match';
            }
        }
        
        // Active Password Form Validation
        function validatePasswords() {
            const pass = document.getElementById('dpass').value;
            const confirm = document.getElementById('cdpass').value;
            if (pass !== confirm) {
                alert("Cannot create account: Passwords do not match!");
                return false;
            }
            return true;
        }

        // Modals
        function openHistoryModal(pid) {
            document.getElementById('modal-' + pid).classList.remove('hidden');
            document.body.style.overflow = 'hidden';
        }
        function closeHistoryModal(pid) {
            document.getElementById('modal-' + pid).classList.add('hidden');
            document.body.style.overflow = 'auto';
        }
    </script>
</body>
</html>