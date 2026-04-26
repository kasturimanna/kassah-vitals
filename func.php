<?php
if (session_status() === PHP_SESSION_NONE) { session_start(); }


$con = mysqli_connect("localhost", "root", "", "myhmsdb", 3306);

if(isset($_POST['patreg'])) {
    $fname = mysqli_real_escape_string($con, $_POST['fname']);
    $lname = mysqli_real_escape_string($con, $_POST['lname']);
    $gender = mysqli_real_escape_string($con, $_POST['gender']);
    $email = mysqli_real_escape_string($con, $_POST['email']);
    $contact = mysqli_real_escape_string($con, $_POST['contact']);
    $password = mysqli_real_escape_string($con, $_POST['password']);
    $cpassword = mysqli_real_escape_string($con, $_POST['cpassword']);

    if($password == $cpassword) {
        
        $check_query = "SELECT * FROM patreg WHERE email='$email'";
        $check_res = mysqli_query($con, $check_query);
        
        if(mysqli_num_rows($check_res) > 0) {
             echo "<script>alert('Email already registered! Please use another email.'); window.location.href = 'registration.php';</script>";
             exit();
        }

        
        $query = "INSERT INTO patreg(fname, lname, gender, email, contact, password, cpassword) 
                  VALUES ('$fname', '$lname', '$gender', '$email', '$contact', '$password', '$cpassword')";
        $result = mysqli_query($con, $query);

        if($result) {
            
            
            $new_user_query = "SELECT pid FROM patreg WHERE email='$email'";
            $new_user_res = mysqli_query($con, $new_user_query);
            $user_data = mysqli_fetch_array($new_user_res);

            $_SESSION['pid'] = $user_data['pid'];
            $_SESSION['username'] = $fname . " " . $lname;
            $_SESSION['fname'] = $fname;
            $_SESSION['lname'] = $lname;
            $_SESSION['gender'] = $gender;
            $_SESSION['contact'] = $contact;
            $_SESSION['email'] = $email;

            
            header("Location: admin-panel.php");
            exit(); 
        } else {
            
            die("Registration Error: " . mysqli_error($con));
        }
    } else {
        echo "<script>alert('Passwords do not match'); window.location.href = 'registration.php';</script>";
        exit();
    }
}

if(isset($_POST['patsub'])) {
    $email = mysqli_real_escape_string($con, $_POST['email']);
    $password = mysqli_real_escape_string($con, $_POST['password2']);

    $query = "SELECT * FROM patreg WHERE email='$email' AND password='$password'";
    $result = mysqli_query($con, $query);

    if(mysqli_num_rows($result) == 1) {
        $user_data = mysqli_fetch_array($result);
        
        $_SESSION['pid'] = $user_data['pid'];
        $_SESSION['username'] = $user_data['fname'] . " " . $user_data['lname'];
        $_SESSION['fname'] = $user_data['fname'];
        $_SESSION['lname'] = $user_data['lname'];
        $_SESSION['gender'] = $user_data['gender'];
        $_SESSION['contact'] = $user_data['contact'];
        $_SESSION['email'] = $user_data['email'];

        header("Location: admin-panel.php");
        exit();
    } else {
        echo "<script>alert('Invalid Email or Password. Please try again.'); window.location.href = 'index1.php';</script>";
        exit();
    }
}