<?php
// Enable error reporting (for development only)
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Include database connection
include "connection.php";

// Initialize error variable
$error = "";

// Check if the database connection is successful
if (!$connection) {
    $error = "Database connection failed: " . mysqli_connect_error();
} elseif (isset($_POST['create'])) {
    // Trim inputs
    $item_number = trim($_POST['item_number'] ?? '');
    $object_class = trim($_POST['object_class'] ?? '');
    $containment_procedures = trim($_POST['containment_procedures'] ?? '');
    $description = trim($_POST['description'] ?? '');
    $image = trim($_POST['image'] ?? '');

    // Required fields validation
    if (empty($item_number) || empty($object_class) || empty($containment_procedures) || empty($description)) {
        $error = "Please fill in all required fields marked with *.";
    } else {
        // Prepare SQL statement
        $stmt = $connection->prepare("INSERT INTO scp (item_number, object_class, containment_procedures, description, image) VALUES (?, ?, ?, ?, ?)");
        if (!$stmt) {
            $error = "Prepare failed: " . $connection->error;
        } else {
            $stmt->bind_param("sssss", $item_number, $object_class, $containment_procedures, $description, $image);
            if ($stmt->execute()) {
                // Redirect with success message
                header("Location: index.php?success=" . urlencode("SCP Entry created successfully"));
                exit;
            } else {
                $error = "Execute failed: " . $stmt->error;
                // Log error for debugging (optional)
                error_log("SQL Error: " . $stmt->error, 3, "error_log.txt");
            }
            $stmt->close();
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1" />
    <title>Add New SCP Entry</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.6/dist/css/bootstrap.min.css" rel="stylesheet" crossorigin="anonymous">
    <style>
        body {
            background-image: url('images/SCP-99.png');
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
        <h1 class="mb-4">Add New SCP Entry</h1>
        <p><a href="index.php" class="btn btn-primary mb-4">Back to index page</a></p>

        <?php if ($error): ?>
            <div class="alert alert-danger"><?php echo htmlspecialchars($error); ?></div>
        <?php endif; ?>

        <div class="form-container">
            <form method="post" action="create.php" class="border rounded p-4 shadow">
                <div class="mb-3">
                    <label for="item_number" class="form-label">SCP ID <span class="text-danger">*</span></label>
                    <input type="text" id="item_number" name="item_number" class="form-control" placeholder="Enter SCP ID (e.g. SCP-173)" required
                        value="<?php echo htmlspecialchars($_POST['item_number'] ?? ''); ?>">
                </div>
                <div class="mb-3">
                    <label for="object_class" class="form-label">Object Class <span class="text-danger">*</span></label>
                    <input type="text" id="object_class" name="object_class" class="form-control" placeholder="Enter Object Class" required
                        value="<?php echo htmlspecialchars($_POST['object_class'] ?? ''); ?>">
                </div>
                <div class="mb-3">
                    <label for="containment_procedures" class="form-label">Containment Procedures <span class="text-danger">*</span></label>
                    <textarea id="containment_procedures" name="containment_procedures" class="form-control" rows="4" placeholder="Enter Containment Procedures" required><?php echo htmlspecialchars($_POST['containment_procedures'] ?? ''); ?></textarea>
                </div>
                <div class="mb-3">
                    <label for="description" class="form-label">Description <span class="text-danger">*</span></label>
                    <textarea id="description" name="description" class="form-control" rows="4" placeholder="Enter Description" required><?php echo htmlspecialchars($_POST['description'] ?? ''); ?></textarea>
                </div>
                <div class="mb-3">
                    <label for="image" class="form-label">Image URL</label>
                    <input type="url" id="image" name="image" class="form-control" placeholder="Enter Image URL"
                        value="<?php echo htmlspecialchars($_POST['image'] ?? ''); ?>">
                </div>
                <button type="submit" name="create" class="btn btn-forestgreen button">Create New SCP Entry</button>
            </form>
        </div>
    </main>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.6/dist/js/bootstrap.bundle.min.js" crossorigin="anonymous"></script>
</body>
</html>