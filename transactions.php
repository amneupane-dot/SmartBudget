
<?php
session_start();
require_once "db.php";

// Check if the user is logged in
if (!isset($_SESSION["user_id"])) {
    header("Location: login.php");
    exit;
}

// Get the logged-in user's ID
$user_id = $_SESSION["user_id"];

// Get this user's transactions from MySQL
$sql = "SELECT
            t.transaction_id,
            t.amount,
            t.description,
            t.transaction_date,
            c.category_name,
            c.category_type
        FROM transactions t
        INNER JOIN categories c
            ON t.category_id = c.category_id
        WHERE t.user_id = ?
        ORDER BY
            t.transaction_date DESC,
            t.transaction_id DESC";

$stmt = $pdo->prepare($sql);
$stmt->execute([$user_id]);

$transactions = $stmt->fetchAll();
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport"
          content="width=device-width, initial-scale=1.0">

    <title>Transaction History - SmartBudget</title>

    <link rel="stylesheet" href="style.css">
</head>

<body>

<div class="container">

    <h1>SmartBudget</h1>

    <h2>Transaction History</h2>

    <p>Here are all your saved transactions.</p>

    <?php if (count($transactions) == 0): ?>

        <p>You have no transactions yet.</p>

    <?php else: ?>

        <div class="table-wrapper">

            <table class="transaction-table">

                <thead>
                    <tr>
                        <th>Date</th>
                        <th>Type</th>
                        <th>Category</th>
                        <th>Description</th>
                        <th>Amount</th>
                    </tr>
                </thead>

                <tbody>

                <?php foreach ($transactions as $transaction): ?>

                    <tr>

                        <td>
                            <?php
                            echo htmlspecialchars(
                                $transaction["transaction_date"]
                            );
                            ?>
                        </td>

                        <td>
                            <?php
                            echo htmlspecialchars(
                                ucfirst($transaction["category_type"])
                            );
                            ?>
                        </td>

                        <td>
                            <?php
                            echo htmlspecialchars(
                                $transaction["category_name"]
                            );
                            ?>
                        </td>

                        <td>
                            <?php
                            echo htmlspecialchars(
                                $transaction["description"] ?? ""
                            );
                            ?>
                        </td>

                        <td class="<?php
                            echo $transaction["category_type"]
                                == "income"
                                ? "income-amount"
                                : "expense-amount";
                        ?>">

                            <?php
                            echo $transaction["category_type"]
                                == "income" ? "+" : "-";

                            echo "$" . number_format(
                                (float)$transaction["amount"],
                                2
                            );
                            ?>

                        </td>

                    </tr>

                <?php endforeach; ?>

                </tbody>

            </table>

        </div>

    <?php endif; ?>

    <p>
        <a href="add_transaction.php">
            Add New Transaction
        </a>
    </p>

    <p>
        <a href="dashboard.php">
            Back to Dashboard
        </a>
    </p>

</div>

</body>
</html>
