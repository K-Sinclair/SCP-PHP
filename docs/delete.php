<?php
// Enable error reporting (for development only)
error_reporting(E_ALL);
ini_set('display_errors', 1);

include "connection.php";

$error = "";

// Check if SCP ID is provided
if (!isset($_GET['scp'])) {
    $error = "No SCP ID provided.";
} else {
    $item_number = $_GET['scp'];
    // Delete SCP from database
    $stmt = $connection->prepare("DELETE FROM scp WHERE item_number = ?");
    if (!$stmt) {
        $error = "Prepare failed: " . $connection->error;
    } else {
        $stmt->bind_param("s", $item_number);
        if ($stmt->execute()) {
            if ($stmt->affected_rows > 0) {
                header("Location: index.php?success=" . urlencode("SCP Entry deleted successfully"));
                exit;
            } else {
                $error = "SCP not found in the database.";
            }
        } else {
            $error = "Delete failed: " . $stmt->error;
        }
        $stmt->close();
    }
}
?>

<!doctype html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Delete SCP Entry</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet" crossorigin="anonymous">
</head>
<body class="container py-4">
    <h1 class="mb-4">Delete SCP Entry</h1>
    <p><a href="index.php" class="btn btn-primary mb-4">Back to index page</a></p>

    <?php if ($error): ?>
        <div class="alert alert-danger"><?php echo htmlspecialchars($error); ?></div>
    <?php else: ?>
        <div class="alert alert-success">SCP Entry deleted successfully.</div>
    <?php endif; ?>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js" crossorigin="anonymous"></script>
</body>
</html>