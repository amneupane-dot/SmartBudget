<?php
session_start();
require_once "db.php";

// Only logged-in users can access this page
if (!isset($_SESSION["user_id"])) {
    header("Location: login.php");
    exit;
}

$user_id = $_SESSION["user_id"];
$message = "";
$error = "";

// Get the transaction categories

// Get the selected transaction type
$selected_type = $_GET["type"] ?? "expense";

// Only allow income or expense
if ($selected_type != "income" && $selected_type != "expense") {
    $selected_type = "expense";
}

// Get categories for the selected type
$sql = "SELECT * FROM categories
        WHERE category_type = ?
        ORDER BY category_name";

$stmt = $pdo->prepare($sql);
$stmt->execute([$selected_type]);

$categories = $stmt->fetchAll();


// Save the transaction when the form is submitted
if ($_SERVER["REQUEST_METHOD"] == "POST") {

    $type = $_POST["type"] ?? "";
    $category_id = $_POST["category_id"] ?? "";
    $amount = trim($_POST["amount"] ?? "");
    $description = trim($_POST["description"] ?? "");
    $date = $_POST["transaction_date"] ?? "";

    // Check that all required fields are filled in
    if (
        ($type != "income" && $type != "expense") ||
        $category_id == "" ||
        $amount == "" ||
        $date == ""
    ) {
        $error = "Please fill in all required fields.";
    } elseif (
        !is_numeric($amount) ||
        !is_finite((float)$amount) ||
        (float)$amount <= 0 ||
        (float)$amount > 99999999.99 ||
        !preg_match('/^\d+(\.\d{1,2})?$/', $amount)
    ) {
        $error = "Enter a valid positive amount with up to 2 decimal places.";
    } else {

        // Check the date format
        $date_object = DateTime::createFromFormat("!Y-m-d", $date);

        if (
            !$date_object ||
            $date_object->format("Y-m-d") != $date
        ) {
            $error = "Please enter a valid date.";
        } else {

            // Check that the selected category matches the type
            $sql = "SELECT category_id FROM categories
                    WHERE category_id = ?
                    AND category_type = ?";

            $stmt = $pdo->prepare($sql);
            $stmt->execute([$category_id, $type]);

            $valid_category = $stmt->fetch();

            if (!$valid_category) {
                $error = "Please select a valid category.";
            } else {

                // Insert transaction into MySQL
                $sql = "INSERT INTO transactions
                        (user_id, category_id, amount,
                         description, transaction_date)
                        VALUES (?, ?, ?, ?, ?)";

                $stmt = $pdo->prepare($sql);

                $stmt->execute([
                    $user_id,
                    $category_id,
                    $amount,
                    $description,
                    $date
                ]);

                // Redirect to avoid saving twice on refresh
                header("Location: add_transaction.php?success=1");
                exit;
            }
        }
    }
}

if (isset($_GET["success"]) && $_GET["success"] == "1") {
    $message = "Transaction added successfully!";
}
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport"
          content="width=device-width, initial-scale=1.0">

    <title>Add Transaction - SmartBudget</title>

    <link rel="stylesheet" href="style.css">
</head>

<body>

<div class="container">

    <h1>SmartBudget</h1>

    <h2>Add Transaction</h2>

    <p>Record your income or expenses below.</p>

    <?php if ($message != ""): ?>
        <p class="success">
            <?php echo htmlspecialchars($message); ?>
        </p>
    <?php endif; ?>

    <?php if ($error != ""): ?>
        <p class="error">
            <?php echo htmlspecialchars($error); ?>
        </p>
    <?php endif; ?>

    <form method="POST">

<label for="type">Transaction Type</label>

<select name="type" id="type" required>
    <option value="<?php echo htmlspecialchars($selected_type); ?>">
        <?php echo ucfirst($selected_type); ?>
    </option>
</select>

<p>
    <a href="add_transaction.php?type=income">Income</a>
    |
    <a href="add_transaction.php?type=expense">Expense</a>
</p>

        <label for="category_id">Category</label>

        <select name="category_id" id="category_id" required>
            <option value="">Select Category</option>

            <?php foreach ($categories as $category): ?>

                <option
                    value="<?php echo $category["category_id"]; ?>"
                    data-type="<?php echo htmlspecialchars(
                        $category["category_type"]
                    ); ?>"
                >
                    <?php echo htmlspecialchars(
                        ucfirst($category["category_type"]) .
                        " - " .
                        $category["category_name"]
                    ); ?>
                </option>

            <?php endforeach; ?>

        </select>

        <label for="amount">Amount ($)</label>

        <input
            type="number"
            name="amount"
            id="amount"
            min="0.01"
            max="99999999.99"
            step="0.01"
            placeholder="Example: 50.00"
            required
        >

        <label for="description">Description (Optional)</label>

        <input
            type="text"
            name="description"
            id="description"
            maxlength="255"
            placeholder="Example: Groceries"
        >

        <label for="transaction_date">Date</label>

        <input
            type="date"
            name="transaction_date"
            id="transaction_date"
            value="<?php echo date('Y-m-d'); ?>"
            required
        >

        <button type="submit">
            Save Transaction
        </button>

    </form>

    <p>
        <a href="dashboard.php">
            Back to Dashboard
        </a>
    </p>

</div>

</body>
</html>
