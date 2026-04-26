<?php
// 1. Safely start the session
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// 2. Secure PDO Database Connection
$host = 'localhost';
$dbname = 'myhmsdb';
$db_user = 'root';
$db_pass = '';

try {
    $pdo = new PDO("mysql:host=$host;dbname=$dbname", $db_user, $db_pass);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    $pdo->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);
} catch(PDOException $e) {
    die("<script>alert('System Error: Database connection failed.'); window.location.href='index.php';</script>");
}

// 3. Handle Doctor Login Action
if(isset($_POST['docsub1'])) {
    
    // We fetch the email input from the form (we changed this to email3 in index.php)
    // Fallback to username3 just in case your HTML hasn't updated yet
    $doctor_email = isset($_POST['email3']) ? trim($_POST['email3']) : (isset($_POST['username3']) ? trim($_POST['username3']) : ''); 
    $dpassword = $_POST['password3'];

    // 4. Secure Prepared Statement to prevent SQL Injection
    // We check both email AND username just to make it bulletproof
    $stmt = $pdo->prepare("SELECT * FROM doctb WHERE (email = ? OR username = ?) AND password = ?");
    $stmt->execute([$doctor_email, $doctor_email, $dpassword]);
    $result = $stmt->fetch();

    if($result) {
        // Login Success! 
        // We set the session to their actual Name so the Doctor Panel can fetch their patients
        $_SESSION['dname'] = $result['username'];
        
        // Redirect to the newly styled Doctor Panel
        header("Location: doctor-panel.php");
        exit();
    } else {
        // Login Failed - Send them back to the portal section with an alert
        echo "<script>
                alert('Access Denied: Invalid Doctor Credentials.'); 
                window.location.href='index.php#portals';
              </script>";
        exit();
    }
}
?>