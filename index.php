<?php
// -------------------- PHP LOGIC --------------------

// Initialize variables
$name = $email = "";
$nameErr = $emailErr = $passwordErr = $confirmPasswordErr = "";
$successMessage = $errorMessage = "";

if ($_SERVER["REQUEST_METHOD"] == "POST") {

    // Name validation
    if (empty($_POST["user_name"])) {
        $nameErr = "Name is required";
    } else {
        $name = htmlspecialchars(trim($_POST["user_name"]));
    }

    // Email validation
    if (empty($_POST["email_address"])) {
        $emailErr = "Email is required";
    } elseif (!filter_var($_POST["email_address"], FILTER_VALIDATE_EMAIL)) {
        $emailErr = "Invalid email format";
    } else {
        $email = htmlspecialchars(trim($_POST["email_address"]));
    }

    // Password validation
if (empty($_POST["password"])) {
    $passwordErr = "Password is required";
} elseif (strlen($_POST["password"]) < 8) {
    $passwordErr = "Password must be at least 8 characters long";
} elseif (!preg_match("/[@#$%^&*!]/", $_POST["password"])) {
    $passwordErr = "Password must contain at least one special character (@#$%^&*!)";
} else {
    $password = $_POST["password"];
}

    // Confirm password validation
if (empty($_POST["confirm_password"])) {
    $confirmPasswordErr = "Confirm password is required";
} elseif ($password !== $_POST["confirm_password"]) {
    $confirmPasswordErr = "Passwords do not match";
}

    // If no errors
    if (empty($nameErr) && empty($emailErr) && empty($passwordErr) && empty($confirmPasswordErr)) {

        $file = "users.json";

        try {
            // Create users.json if not exists
            if (!file_exists($file)) {
                file_put_contents($file, json_encode([]));
            }

            // Read JSON file
            $jsonData = file_get_contents($file);
            if ($jsonData === false) {
                throw new Exception("Error reading users file.");
            }

            $users = json_decode($jsonData, true);
            if (!is_array($users)) {
                $users = [];
            }

            // Check duplicate email
            foreach ($users as $user) {
                if ($user["email"] === $email) {
                    throw new Exception("Email already registered.");
                }
            }

            // Add new user
            $users[] = [
                "name" => $name,
                "email" => $email,
                "password" => password_hash($password, PASSWORD_DEFAULT)
            ];

            // Write back to JSON
            if (file_put_contents($file, json_encode($users, JSON_PRETTY_PRINT)) === false) {
                throw new Exception("Error saving user data.");
            }

            // Success message
            $successMessage = "Registration successful! Your account has been created.";

            // Clear form
            $name = $email = "";

        } catch (Exception $e) {
            $errorMessage = $e->getMessage();
        }
    }
}
?>

<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>User Registration System</title>
    <style>
        body {
            font-family: Arial;
            background: #f4f4f4;
        }
        form {
            width: 400px;
            background: #fff;
            padding: 20px;
            margin: 50px auto;
            border-radius: 5px;
        }
        input[type="text"],
        input[type="password"] {
            width: 100%;
            padding: 8px;
            margin-top: 5px;
        }
        input[type="submit"] {
            padding: 10px;
            width: 100%;
            background: #4CAF50;
            color: white;
            border: none;
            margin-top: 15px;
            cursor: pointer;
        }
        .error {
            color: red;
            font-size: 14px;
        }
        .success {
            color: green;
            font-size: 16px;
            margin-top: 10px;
        }
    </style>
</head>

<body>

<form method="post">

    <h2>User Registration</h2>

    <label>Name</label>
    <input type="text" name="user_name" value="<?php echo $name; ?>">
    <div class="error"><?php echo $nameErr; ?></div>

    <label>Email</label>
    <input type="text" name="email_address" value="<?php echo $email; ?>">
    <div class="error"><?php echo $emailErr; ?></div>

    <label>Password</label>
    <input type="password" name="password">
    <div class="error"><?php echo $passwordErr; ?></div>

    <label>Confirm Password</label>
    <input type="password" name="confirm_password">
    <div class="error"><?php echo $confirmPasswordErr; ?></div>

    <?php if (!empty($successMessage)) { ?>
        <div class="success"><?php echo $successMessage; ?></div>
    <?php } ?>

    <?php if (!empty($errorMessage)) { ?>
        <div class="error"><?php echo $errorMessage; ?></div>
    <?php } ?>

    <input type="submit" value="Register">

</form>

</body>
</html>
