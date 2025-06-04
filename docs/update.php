<?php
// Enable error reporting (for development only)
error_reporting(E_ALL);
ini_set('display_errors', 1);

include "connection.php";

// Check if the form was submitted
if (isset($_POST['update'])) {
    // Validate required fields
    if (empty($_POST['id']) || empty($_POST['item_number']) || empty($_POST['object_class'])) {
        header("Location: index.php?error=" . urlencode("ID, Item Number, and Object Class are required."));
        exit;
    }

    // Prepare the update statement
    $stmt = $connection->prepare("UPDATE scp_subjects SET item_number=?, object_class=?, procedures=?, description=?, reference_link=? WHERE id=?");
    
    if (!$stmt) {
        header("Location: index.php?error=" . urlencode("Error preparing statement: " . $connection->error));
        exit;
    }

    // Bind parameters
    $stmt->bind_param(
        "sssssi",
        $_POST['item_number'],
        $_POST['object_class'],
        $_POST['procedures'],
        $_POST['description'],
        $_POST['reference_link'],
        $_POST['id']
    );

    // Execute the update
    if ($stmt->execute()) {
        $itemNumber = htmlspecialchars($_POST['item_number']);
        header("Location: index.php?success=" . urlencode("SCP $itemNumber Updated Successfully"));
    } else {
        header("Location: index.php?error=" . urlencode("Error updating SCP subject: " . $stmt->error));
    }

    $stmt->close();
} else {
    // If not a POST request
    header("Location: index.php");
}
exit;
?>
