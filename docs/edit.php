<?php
// Enable error reporting (for development only)
error_reporting(E_ALL);
ini_set('display_errors', 1);

include "connection.php";

$error = "";
$scp = null;

// Hardcoded entries (to check if the SCP is editable)
$hardcoded_entries = [
    "002" => true, "003" => true, "004" => true, "005" => true,
    "006" => true, "007" => true, "008" => true, "009" => true
];

// Check if SCP ID is provided
if (!isset($_GET['scp'])) {
    $error = "No SCP ID provided.";
} else {
    $item_number = $_GET['scp'];
    // Check if the SCP is hardcoded
    if (isset($hardcoded_entries[$item_number])) {
        $error = "This SCP entry is hardcoded and cannot be edited.";
    } else {
        // Fetch SCP from database
        $stmt = $connection->prepare("SELECT * FROM scp WHERE item_number = ?");
        if (!$stmt) {
            $error = "Prepare failed: " . $connection->error;
        } else {
            $stmt->bind_param("s", $item_number);
            $stmt->execute();
            $result = $stmt->get_result();
            $scp = $result->fetch_assoc();
            $stmt->close();

            if (!$scp) {
                $error = "SCP not found in the database.";
            }
        }
    }
}

// Handle form submission
if (isset($_POST['update']) && !$error) {
    $item_number = trim($_POST['item_number'] ?? '');
    $object_class = trim($_POST['object_class'] ?? '');
    $containment_procedures = trim($_POST['containment_procedures'] ?? '');
    $description = trim($_POST['description'] ?? '');
    $image = trim($_POST['image'] ?? '');

    // Required fields validation
    if (empty($item_number) || empty($object_class) || empty($containment_procedures) || empty($description)) {
        $error = "Please fill in all required fields marked with *.";
    } else {
        // Update SCP in database
        $stmt = $connection->prepare("UPDATE scp SET item_number = ?, object_class = ?, containment_procedures = ?, description = ?, image = ? WHERE item_number = ?");
        if (!$stmt) {
            $error = "Prepare failed: " . $connection->error;
        } else {
            $stmt->bind_param("ssssss", $item_number, $object_class, $containment_procedures, $description, $image, $_GET['scp']);
            if ($stmt->execute()) {
                header("Location: index.php?success=" . urlencode("SCP Entry updated successfully"));
                exit;
            } else {
                $error = "Update failed: " . $stmt->error;
            }
            $stmt->close();
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1" />
    <title>Edit SCP Entry</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.6/dist/css/bootstrap.min.css" rel="stylesheet" crossorigin="anonymous">
    <style>
        body {
            background-image: url('images/SCP-900.jpeg');
            background-size: cover;
            background-attachment: fixed;
            background-position: center;
            color: white;
            font-family: Arial, sans-serif;
            margin: 0;
            padding: 0;
        }
        main {
            padding: 40px;
            margin-top: 40px;
            background-color: rgba(0, 0, 0, 0.7);
            border-radius: 10px;
            max-width: 800px;
            margin-left: auto;
            margin-right: auto;
            display: flex;
            flex-direction: column;
            align-items: center;
        }
        main h1 {
            font-size: 28px;
            margin-bottom: 20px;
            color: white;
        }
        .form-container {
            width: 100%;
            max-width: 600px;
        }
        .button {
            padding: 10px 20px;
            border-radius: 5px;
            cursor: pointer;
            transition: filter 0.3s ease;
        }
        .button:hover {
            filter: brightness(85%);
        }
        .btn-forestgreen {
            background-color: #228B22;
            border-color: #228B22;
            color: white;
        }
        .btn-forestgreen:hover,
        .btn-forestgreen:focus {
            background-color: #1c6b1c;
            border-color: #1c6b1c;
            color: white;
        }
        @media (max-width: 768px) {
            main {
                padding: 20px;
            }
        }
    </style>
</head>
<body>
    <main>
        <h1 class="mb-4">Edit SCP Entry</h1>
        <p><a href="index.php" class="btn btn-primary mb-4">Back to index page</a></p>

        <?php if ($error): ?>
            <div class="alert alert-danger"><?php echo htmlspecialchars($error); ?></div>
        <?php elseif (!$scp): ?>
            <div class="alert alert-danger">SCP not found.</div>
        <?php else: ?>
            <div class="form-container">
                <form method="post" action="edit.php?scp=<?php echo htmlspecialchars($_GET['scp']); ?>" class="border rounded p-4 shadow">
                    <div class="mb-3">
                        <label for="item_number" class="form-label">SCP ID <span class="text-danger">*</span></label>
                        <input type="text" id="item_number" name="item_number" class="form-control" placeholder="Enter SCP ID (e.g. SCP-173)" required
                            value="<?php echo htmlspecialchars($scp['item_number']); ?>">
                    </div>
                    <div class="mb-3">
                        <label for="object_class" class="form-label">Object Class <span class="text-danger">*</span></label>
                        <input type="text" id="object_class" name="object_class" class="form-control" placeholder="Enter Object Class" required
                            value="<?php echo htmlspecialchars($scp['object_class']); ?>">
                    </div>
                    <div class="mb-3">
                        <label for="containment_procedures" class="form-label">Containment Procedures <span class="text-danger">*</span></label>
                        <textarea id="containment_procedures" name="containment_procedures" class="form-control" rows="4" placeholder="Enter Containment Procedures" required><?php echo htmlspecialchars($scp['containment_procedures']); ?></textarea>
                    </div>
                    <div class="mb-3">
                        <label for="description" class="form-label">Description <span class="text-danger">*</span></label>
                        <textarea id="description" name="description" class="form-control" rows="4" placeholder="Enter Description" required><?php echo htmlspecialchars($scp['description']); ?></textarea>
                    </div>
                    <div class="mb-3">
                        <label for="image" class="form-label">Image URL</label>
                        <input type="url" id="image" name="image" class="form-control" placeholder="Enter Image URL"
                            value="<?php echo htmlspecialchars($scp['image']); ?>">
                    </div>
                    <button type="submit" name="update" class="btn btn-forestgreen button">Update SCP Entry</button>
                </form>
            </div>
        <?php endif; ?>
    </main>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.6/dist/js/bootstrap.bundle.min.js" crossorigin="anonymous"></script>
</body>
</html>