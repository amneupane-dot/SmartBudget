
<?php
session_start();

// Check if the user is logged in
if (!isset($_SESSION["user_id"])) {
    header("Location: login.php");
    exit;
}

// Get the logged-in user's name
$full_name = $_SESSION["full_name"];
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <title>Dashboard - SmartBudget</title>
    <link rel="stylesheet" href="style.css">
</head>

<body>

<div class="container">

    <h1>SmartBudget</h1>

    <h2>
        Welcome,
        <?php echo htmlspecialchars(
            $full_name,
            ENT_QUOTES,
            "UTF-8"
        ); ?>!
    </h2>

    <p>You are successfully logged in.</p>

    <p>Welcome to your personal budget dashboard.</p>
    <p>
    <a href="add_transaction.php">
        Add Income or Expense
    </a>
</p>

<p>
    <a href="transactions.php">
        View Transaction History
    </a>
</p>


    <a class="logout-link" href="logout.php">
        Logout
    </a>

</div>

</body>
</html>
