
<?php
require_once "db.php";

$message = "";
$full_name = "";
$email = "";

if ($_SERVER["REQUEST_METHOD"] == "POST") {

    $full_name = trim($_POST["full_name"] ?? "");
    $email = trim($_POST["email"] ?? "");
    $password = $_POST["password"] ?? "";
    $confirm_password = $_POST["confirm_password"] ?? "";

    // Check for empty fields
    if (
        $full_name == "" ||
        $email == "" ||
        $password == "" ||
        $confirm_password == ""
    ) {
        $message = "Please fill in all fields.";

    } elseif (strlen($full_name) > 100) {

        $message = "Your name is too long.";

    } elseif (
        strlen($email) > 150 ||
        !filter_var($email, FILTER_VALIDATE_EMAIL)
    ) {

        $message = "Please enter a valid email address.";

    } elseif (strlen($password) < 8) {

        $message = "Password must be at least 8 characters.";

    } elseif ($password != $confirm_password) {

        $message = "Passwords do not match.";

    } else {

        // Check whether the email is already registered
        $sql = "SELECT user_id FROM users WHERE email = ?";
        $stmt = $pdo->prepare($sql);
        $stmt->execute([$email]);

        if ($stmt->fetch()) {

            $message = "This email is already registered.";

        } else {

            // Protect the password before saving
            $hashed_password = password_hash(
                $password,
                PASSWORD_DEFAULT
            );

            // Save the new user
            $sql = "INSERT INTO users
                    (full_name, email, password)
                    VALUES (?, ?, ?)";

            $stmt = $pdo->prepare($sql);

            try {

                $stmt->execute([
                    $full_name,
                    $email,
                    $hashed_password
                ]);

                header("Location: register.php?success=1");
                exit;

            } catch (PDOException $e) {

                // Handle duplicate email if another
                // request registered it first
                if ($e->getCode() == "23000") {
                    $message = "This email is already registered.";
                } else {
                    error_log($e->getMessage());
                    $message = "Registration failed. Please try again.";
                }
            }
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <title>Register - SmartBudget</title>

    <link rel="stylesheet" href="style.css">
</head>

<body>

<div class="container">

    <h1>SmartBudget</h1>
    <h2>Create Account</h2>

    <p>Start managing your money today.</p>

    <?php if (isset($_GET["success"])): ?>

        <p class="success">
            Account created successfully!
        </p>

    <?php endif; ?>

    <?php if ($message != ""): ?>

        <p class="error">
            <?php echo htmlspecialchars(
                $message,
                ENT_QUOTES,
                "UTF-8"
            ); ?>
        </p>

    <?php endif; ?>

    <form action="register.php" method="POST">

        <label for="full_name">Full Name</label>

        <input
            type="text"
            id="full_name"
            name="full_name"
            maxlength="100"
            value="<?php echo htmlspecialchars(
                $full_name,
                ENT_QUOTES,
                'UTF-8'
            ); ?>"
            required
        >

        <label for="email">Email Address</label>

        <input
            type="email"
            id="email"
            name="email"
            maxlength="150"
            value="<?php echo htmlspecialchars(
                $email,
                ENT_QUOTES,
                'UTF-8'
            ); ?>"
            required
        >

        <label for="password">Password</label>

        <input
            type="password"
            id="password"
            name="password"
            minlength="8"
            required
        >

        <label for="confirm_password">
            Confirm Password
        </label>

        <input
            type="password"
            id="confirm_password"
            name="confirm_password"
            minlength="8"
            required
        >

        <button type="submit">
            Create Account
        </button>

    </form>

    <p class="footer-text">
        Already have an account?
        <a href="login.php">Login</a>
    </p>

</div>

</body>
</html>
