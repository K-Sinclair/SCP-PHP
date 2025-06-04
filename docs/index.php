<?php
// Enable error reporting (for development only)
error_reporting(E_ALL);
ini_set('display_errors', 0); // Disable display to prevent exposing errors
ini_set('log_errors', 1);
ini_set('error_log', 'error_log.txt'); // Log errors to file

// Initialize error and success messages
$error = '';
$success = $_GET['success'] ?? '';

// Include database connection with error handling
if (!file_exists('connection.php')) {
    $error = 'Database connection file (connection.php) is missing.';
    file_put_contents('error_log.txt', date('Y-m-d H:i:s') . " [ERROR] connection.php missing\n", FILE_APPEND);
} else {
    include 'connection.php';
    if (!isset($connection) || !$connection) {
        $error = 'Failed to establish database connection.';
        file_put_contents('error_log.txt', date('Y-m-d H:i:s') . " [ERROR] Database connection failed\n", FILE_APPEND);
    }
}

// Fetch SCP entries from the database
$scp_entries = [];
$database_ids = [];
if (!$error) {
    $query = "SELECT * FROM scp ORDER BY item_number ASC";
    $result = $connection->query($query);

    if ($result === false) {
        $error = "Query failed: " . $connection->error;
        file_put_contents('error_log.txt', date('Y-m-d H:i:s') . " [ERROR] Query failed: " . $connection->error . "\n", FILE_APPEND);
    } elseif ($result) {
        while ($row = $result->fetch_assoc()) {
            $scp_entries[$row['item_number']] = [
                'title' => htmlspecialchars($row['item_number']),
                'image' => htmlspecialchars($row['image'] ?? 'images/default_scp.jpg'),
                'description' => htmlspecialchars($row['description']),
                'content' => '
                    <p><strong>Object Class:</strong> ' . htmlspecialchars($row['object_class']) . '</p>
                    <p><strong>Special Containment Procedures:</strong> ' . htmlspecialchars($row['containment_procedures']) . '</p>
                    <p><strong>Description:</strong> ' . htmlspecialchars($row['description']) . '</p>',
                'source' => 'database'
            ];
            $database_ids[] = $row['item_number'];
        }
        $result->free();
    }
}

// Load hardcoded entries from JSON file
$hardcoded_file = 'hardcoded_entries.json';
$hardcoded_entries = [];
if (!file_exists($hardcoded_file)) {
    $hardcoded_entries = [
        "002" => [
            "title" => "SCP-002",
            "image" => "images/800px-SCP002.jpg",
            "description" => "A large fleshy spherical object resembling a mass of organic tissue.",
            "content" => "
                <p><strong>Object Class:</strong> Euclid</p>
                <p><strong>Description:</strong> SCP-002 is a large, fleshy, spherical object of unknown origin. The exterior is similar to human tissue and contains an extensive network of veins and arteries.</p>
                <p>SCP-002 is capable of assimilating other objects and humans, incorporating them into its structure.</p>
                <p><strong>Special Containment Procedures:</strong> SCP-002 is contained in a sealed chamber at Site-19 with reinforced walls.</p>",
            "source" => "hardcoded"
        ],
        "003" => [
            "title" => "SCP-003",
            "image" => "images/SCP3003_MBoard.jpg",
            "description" => "A sentient motherboard capable of self-repair and generating signals.",
            "content" => "
                <p><strong>Object Class:</strong> Euclid</p>
                <p><strong>Description:</strong> SCP-003 appears as a standard motherboard but is capable of self-repair and sending anomalous signals.</p>
                <p>It exhibits rudimentary sentience and can influence electronic devices connected to it.</p>
                <p><strong>Special Containment Procedures:</strong> SCP-003 is stored in a Faraday cage and monitored continuously.</p>",
            "source" => "hardcoded"
        ],
        "004" => [
            "title" => "SCP-004",
            "image" => "images/SCP004_door.jpg",
            "description" => "A large wooden barn door and a set of twelve rusted keys with mysterious powers.",
            "content" => '
                <h3>Item #: SCP-004</h3>
                <p><strong>Object Class:</strong> Euclid</p>
                <h3>Special Containment Procedures:</h3>
                <p>
                    SCP-004 consists of a large wooden barn door (SCP-004-1) and a set of twelve rusted keys (SCP-004-2 through SCP-004-13).
                    The door is the entrance to an abandoned factory located in [DATA EXPUNGED]. Proper procedures must be followed when handling SCP-004-2 through SCP-004-13.
                    These items are not permitted to be moved off-site unless accompanied by two Level 4 security personnel.
                    Unauthorized removal of keys from the testing area is grounds for immediate termination.
                </p>
                <p>Level 1 clearance is required for basic access to SCP-004-1, while Level 4 clearance is required for use of SCP-004-2 to -13.</p>
                <h3>Description:</h3>
                <p id="description-text">
                    SCP-004 consists of the aforementioned wooden door, which seems to lead to a different dimension or spatial anomaly.
                    Attempts to open the door with any key other than SCP-004-7 or SCP-004-12 result in the dismemberment or complete disappearance of those who attempt to pass through.
                    When SCP-004-7 is used, the individual experiences an inexplicable vast space inside, larger than the structure it connects to.
                    Personnel who interact with SCP-004-12 often return in a catatonic state or exhibit severe psychological distress.
                </p>
                <h3>Chronological History:</h3>
                <p>07/02/1949: A group of juveniles discovered the door and a set of keys, leading to the disappearance of one of their members (SCP-004-CAS01). Upon investigation, it was determined that opening the door resulted in violent consequences.</p>
                <p>07/04/1949: After experimentation, it was found that only two keys, SCP-004-7 and SCP-004-12, allowed safe entry through the door. All other tests resulted in dismemberment or vanishing without a trace.</p>
                <p>Testing continued throughout the 1950s, with further exploration into the space-time anomalies and alternate dimensions connected to SCP-004.
                    The door remains a dangerous anomaly, with further research ongoing.
                </p>',
            "source" => "hardcoded"
        ],
        "005" => [
            "title" => "SCP-005",
            "image" => "images/SCP-005.jpg",
            "description" => "A key capable of opening any lock.",
            "content" => "
                <p><strong>Object Class:</strong> Safe</p>
                <p><strong>Description:</strong> SCP-005 is a small, ornate key that can open any lock regardless of the type or mechanism.</p>
                <p>It appears to adapt its shape to the lock it is used on.</p>
                <p><strong>Special Containment Procedures:</strong> SCP-005 is kept in a locked safe in the Secure Storage wing.</p>",
            "source" => "hardcoded"
        ],
        "006" => [
            "title" => "SCP-006",
            "image" => "images/SCP006_spring.jpg",
            "description" => "A small spring whose water has rejuvenating properties.",
            "content" => "
                <p><strong>Object Class:</strong> Euclid</p>
                <p><strong>Description:</strong> SCP-006 is a small spring with water that can heal and rejuvenate living organisms.</p>
                <p>Water consumption grants rapid healing and temporarily reverses aging effects.</p>
                <p><strong>Special Containment Procedures:</strong> SCP-006 is contained within a secured area with controlled access to prevent misuse.</p>",
            "source" => "hardcoded"
        ],
        "007" => [
            "title" => "SCP-007",
            "image" => "images/SCP-007_planet.jpg",
            "description" => "A small planet contained within a human abdomen.",
            "content" => "
                <p><strong>Object Class:</strong> Safe</p>
                <p><strong>Description:</strong> SCP-007 appears to be a miniature planet located inside the abdominal cavity of a human subject.</p>
                <p>The planet exhibits a full ecosystem and exhibits day-night cycles.</p>
                <p><strong>Special Containment Procedures:</strong> The host is monitored and kept under medical care at all times.</p>",
            "source" => "hardcoded"
        ],
        "008" => [
            "title" => "SCP-008",
            "image" => "images/SCP-008_Plague.jpg",
            "description" => "A contagious prion disease causing zombification.",
            "content" => "
                <p><strong>Object Class:</strong> Keter</p>
                <p><strong>Description:</strong> SCP-008 is a prion disease that transforms infected humans into aggressive zombie-like entities.</p>
                <p>It spreads rapidly through bodily fluids and has caused several outbreaks.</p>
                <p><strong>Special Containment Procedures:</strong> Infected individuals are to be quarantined or terminated to prevent spread.</p>",
            "source" => "hardcoded"
        ],
        "009" => [
            "title" => "SCP-009",
            "image" => "images/SCP009_Ice.jpg",
            "description" => "A substance resembling ice that is red and highly dangerous.",
            "content" => "
                <p><strong>Object Class:</strong> Euclid</p>
                <p><strong>Description:</strong> SCP-009 is a red ice-like substance that freezes at approximately 100°C and causes rapid freezing of organic tissue upon contact.</p>
                <p>It appears to be water-based but exhibits anomalous thermodynamic properties.</p>
                <p><strong>Special Containment Procedures:</strong> SCP-009 is stored in a refrigerated containment unit maintained at temperatures below -100°C to prevent sublimation and spread.</p>",
            "source" => "hardcoded"
        ],
    ];
    if (is_writable(dirname($hardcoded_file)) && file_put_contents($hardcoded_file, json_encode($hardcoded_entries, JSON_PRETTY_PRINT)) === false) {
        $error = 'Failed to write to hardcoded_entries.json.';
        file_put_contents('error_log.txt', date('Y-m-d H:i:s') . " [ERROR] Failed to write to $hardcoded_file\n", FILE_APPEND);
    }
} else {
    $json_content = file_get_contents($hardcoded_file);
    $hardcoded_entries = json_decode($json_content, true);
    if (json_last_error() !== JSON_ERROR_NONE) {
        $error = 'Failed to parse hardcoded_entries.json: ' . json_last_error_msg();
        file_put_contents('error_log.txt', date('Y-m-d H:i:s') . " [ERROR] JSON parse error: " . json_last_error_msg() . "\n", FILE_APPEND);
        $hardcoded_entries = [];
    }
}

// Merge entries: Prioritize database entries over hardcoded ones
$all_entries = $hardcoded_entries;
foreach ($scp_entries as $id => $entry) {
    $all_entries[$id] = $entry;
}

// Create a unique list of SCP entries for display
$unique_scp_entries = $all_entries;

// Determine which SCP is requested
$selected_scp = $_GET['scp'] ?? null;
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>SCP Foundation Catalog</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-QWTKZyjpPEjISv5WaRU9OFeRpok6YctnYmDr5pNlyT2bRjXh0JMhjY6hW+ALEwIH" crossorigin="anonymous">
    <link href="https://fonts.googleapis.com/css2?family=Special+Elite&display=swap" rel="stylesheet">
    <style>
        body {
            background-image: url('images/SCP-087.png');
            background-size: cover;
            background-attachment: fixed;
            background-position: center;
            color: white;
            font-family: Arial, sans-serif;
            margin: 0;
            padding: 0;
        }
        .scp-slogan {
            text-align: center;
            padding: 15px 0;
            background-color: rgba(0, 0, 0, 0.9);
            color: white;
            font-size: 2rem;
            font-family: 'Special Elite', cursive;
            font-weight: bold;
            text-transform: uppercase;
            letter-spacing: 2px;
            text-shadow:
                2px 2px 0px black,
                -2px -2px 0px black,
                2px -2px 0px black,
                -2px 2px 0px black;
        }
        nav {
            background-color: black;
            padding: 0;
            display: flex;
            align-items: center;
            justify-content: center; /* Center content horizontally */
            width: 100%;
            position: relative; /* Needed for absolute positioning of dropdown */
        }
        .logo {
            margin-right: 20px; /* Space between logo and nav links on larger screens */
        }
        .logo img {
            width: 60px;
            height: auto;
        }
        nav ul {
            list-style: none;
            display: flex;
            padding: 0;
            margin: 0;
            max-height: 70vh; /* Set max height for dropdown */
            overflow-y: auto; /* Enable vertical scrolling */
            scrollbar-width: thin; /* Firefox: slimmer scrollbar */
            scrollbar-color: #FFD700 #000; /* Firefox: scrollbar colors */
        }
        nav ul::-webkit-scrollbar {
            width: 8px; /* Webkit: slimmer scrollbar width */
        }
        nav ul::-webkit-scrollbar-track {
            background: #000; /* Webkit: scrollbar track color */
        }
        nav ul::-webkit-scrollbar-thumb {
            background: #FFD700; /* Webkit: scrollbar thumb color */
            border-radius: 4px; /* Webkit: rounded scrollbar thumb */
        }
        nav ul::-webkit-scrollbar-thumb:hover {
            background: #ccac00; /* Webkit: darker on hover */
        }
        nav ul li {
            margin: 0 10px;
        }
        nav ul li a {
            color: white;
            text-decoration: none;
            font-weight: bold;
            font-size: 16px;
            white-space: nowrap;
            padding: 10px;
            display: block;
        }
        nav ul li a:hover,
        nav ul li a.active {
            text-decoration: underline;
            color: #FFD700;
        }
        .hamburger {
            display: none;
            font-size: 30px;
            cursor: pointer;
            color: white;
            padding: 10px 15px;
            position: absolute; /* Position relative to nav */
            right: 15px; /* Align to the right */
            top: 50%; /* Center vertically */
            transform: translateY(-50%); /* Adjust for perfect vertical centering */
            z-index: 1001; /* Ensure it's above other elements if needed */
        }
        header {
            text-align: center;
            margin-top: 20px;
        }
        header h1 {
            font-size: 36px;
            color: white;
            text-shadow:
                2px 2px 0px black,
                -2px -2px 0px black,
                2px -2px 0px black,
                -2px 2px 0px black;
        }
        main {
            padding: 40px;
            margin-top: 40px;
            background-color: rgba(0, 0, 0, 0.7);
            border-radius: 10px;
            max-width: 1200px;
            margin-left: auto;
            margin-right: auto;
            display: flex;
            flex-direction: column;
            align-items: center;
        }
        main h2 {
            font-size: 28px;
            margin-bottom: 20px;
            color: white;
        }
        main p {
            font-size: 18px;
            color: white;
            line-height: 1.6;
        }
        main strong {
            color: #FFD700;
        }
        .scp-container {
            display: grid;
            grid-template-columns: repeat(4, 1fr);
            gap: 20px;
            margin-top: 40px;
            background-color: black;
            padding: 40px;
            border-radius: 10px;
            max-width: 90vw;
            min-width: 1100px;
            margin-left: auto;
            margin-right: auto;
            justify-items: center;
            align-items: stretch;
        }
        .scp-item {
            background-color: rgba(255, 255, 245, 0.1);
            padding: 20px;
            border-radius: 10px;
            text-align: center;
            color: white;
            box-shadow: 0px 4px 8px rgba(0, 0, 0, 0.5);
            display: flex;
            flex-direction: column;
            align-items: center;
            width: 230px;
            min-height: 300px;
            justify-content: space-between;
        }
        .scp-item img {
            width: 190px;
            height: 140px;
            object-fit: cover;
            border-radius: 10px;
        }
        .scp-item h3 {
            flex-grow: 1;
            min-height: 3.5em;
            display: flex;
            align-items: center;
            justify-content: center;
            text-align: center;
            padding: 0 10px;
            margin: 10px 0;
        }
        .scp-item a.read-btn {
            display: inline-block;
            padding: 10px 20px;
            background-color: black;
            color: white;
            text-decoration: none;
            font-size: 18px;
            border-radius: 10px;
            transition: background-color 0.3s;
            margin-bottom: 10px;
        }
        .scp-item a.read-btn:hover {
            background-color: #444;
        }
        .action-buttons {
            display: flex;
            gap: 5px;
            margin-top: 10px;
            font-size: 14px;
        }
        .action-buttons a {
            padding: 5px 10px;
            font-size: 14px;
        }
        .action-buttons .separator {
            margin: 0 5px;
            color: white;
        }
        .delete-confirm {
            display: none;
            background-color: rgba(255, 0, 0, 0.2);
            padding: 10px;
            border-radius: 5px;
            margin-top: 10px;
            text-align: center;
            width: 100%;
            animation: fadeIn 0.3s ease-in;
        }
        .delete-confirm.show {
            display: block;
        }
        .delete-confirm a {
            padding: 5px 10px;
            margin: 0 5px;
            font-size: 14px;
        }
        @keyframes fadeIn {
            from { opacity: 0; transform: translateY(-10px); }
            to { opacity: 1; transform: translateY(0); }
        }
        @keyframes fadeOut {
            from { opacity: 1; }
            to { opacity: 0; }
        }
        .alert.fade-out {
            animation: fadeOut 1s ease-out forwards;
            animation-delay: 2s;
        }
        /* Mobile adjustments for navigation and layout */
        @media (max-width: 768px) {
            .hamburger {
                display: block;
            }
            nav {
                justify-content: space-between; /* Allow logo and hamburger to spread */
                padding: 0 15px; /* Add some padding on sides */
            }
            .logo {
                margin-right: 0; /* Remove margin on mobile */
                text-align: center;
                flex-grow: 1; /* Allow logo to take available space */
            }
            nav ul {
                display: none;
                flex-direction: column; /* Vertical dropdown */
                position: absolute;
                background-color: black;
                top: 60px; /* Position below the nav bar */
                left: 0;
                width: 100%;
                padding: 10px 0;
                z-index: 1000;
                align-items: center; /* Center items */
                max-height: 70vh; /* Ensure max-height applies on mobile */
                overflow-y: auto; /* Enable scrolling on mobile */
            }
            nav ul.show {
                display: flex;
            }
            nav ul li {
                margin: 10px 0; /* More spacing for touch */
                text-align: center;
            }
            nav ul li a {
                font-size: 16px; /* Larger for mobile */
                padding: 15px; /* Bigger touch area */
            }
            .scp-slogan {
                font-size: 1.5rem;
                padding: 10px 0;
            }

            /* Main content adjustments for mobile */
            .scp-container {
                grid-template-columns: 1fr; /* Single column for smaller mobiles */
                padding: 20px;
                min-width: unset; /* Remove min-width for mobile */
                max-width: 90%; /* Adjust max-width to give some padding */
                margin-left: auto; /* Center the container */
                margin-right: auto; /* Center the container */
                justify-items: center; /* Center items within the grid cell */
                align-items: center; /* Vertically center items within the grid cell if needed, though not strictly required with single column */
            }
            .scp-item {
                width: 100%; /* Make SCP items take full width */
                max-width: 280px; /* Limit max width for a cleaner look on larger phones */
                margin: 0 auto; /* Center individual items */
            }
            main {
                padding: 20px;
            }
        }
        /* Further adjust for very small screens if needed, though 768px might cover most mobiles */
        @media (max-width: 480px) {
            .scp-item {
                max-width: unset; /* Remove max-width to allow full width for very small screens */
            }
            .scp-slogan {
                font-size: 1rem;
                padding: 8px 0;
            }
        }

        /* Adjustments for tablets (between mobile and desktop) */
        @media (min-width: 769px) and (max-width: 1024px) {
            .scp-container {
                grid-template-columns: repeat(2, 1fr); /* 2 items per row for tablets */
                min-width: unset;
                max-width: 700px; /* Adjust max-width for tablets */
                margin-left: auto;
                margin-right: auto;
                justify-items: center; /* Center items within the grid cell */
            }
            .scp-item {
                width: auto; /* Allow items to adjust based on grid */
            }
        }

        footer {
            text-align: center;
            padding: 20px;
            background-color: black;
            color: white;
            margin-top: 40px;
        }
    </style>
</head>
<body>
    <div class="scp-slogan">Secure. Contain. Protect.</div>
    <nav>
        <div class="logo">
            <img src="images/SCP-logo.png" alt="SCP Foundation Logo">
        </div>
        <div class="hamburger" onclick="toggleMenu()">☰</div>
        <ul id="nav-links">
            <li>
                <a href="index.php" class="<?php echo basename($_SERVER['PHP_SELF']) === 'index.php' && $selected_scp === null ? 'active' : ''; ?>">
                    SCP Archive
                </a>
            </li>
            <?php foreach ($unique_scp_entries as $id => $scp): ?>
                <li>
                    <a href="index.php?scp=<?php echo htmlspecialchars($id); ?>" class="<?php echo $selected_scp === $id ? 'active' : ''; ?>">
                        <?php echo htmlspecialchars($scp['title']); ?>
                    </a>
                </li>
            <?php endforeach; ?>
            <li>
                <a href="create.php" class="<?php echo basename($_SERVER['PHP_SELF']) === 'create.php' ? 'active' : ''; ?>">
                    Add New Entry
                </a>
            </li>
        </ul>
        <script>
            function toggleMenu() {
                var nav = document.getElementById("nav-links");
                nav.classList.toggle("show");
            }
        </script>
    </nav>

    <header>
        <h1>SCP Foundation Catalog</h1>
    </header>

    <main>
        <?php if ($success): ?>
            <div class="alert alert-success fade-out"><?php echo htmlspecialchars($success); ?></div>
        <?php endif; ?>

        <?php if ($error): ?>
            <div class="alert alert-danger fade-out"><?php echo htmlspecialchars($error); ?> Please contact the administrator.</div>
        <?php endif; ?>

        <?php if ($selected_scp && isset($unique_scp_entries[$selected_scp])): ?>
            <h2><?php echo htmlspecialchars($unique_scp_entries[$selected_scp]['title']); ?></h2>
            <img src="<?php echo htmlspecialchars($unique_scp_entries[$selected_scp]['image']); ?>" alt="<?php echo htmlspecialchars($unique_scp_entries[$selected_scp]['title']); ?>" style="max-width: 400px; border-radius: 10px; margin-bottom: 20px;">
            <div id="scp-content" style="max-width: 700px; text-align: left;">
                <?php echo $unique_scp_entries[$selected_scp]['content']; ?>
            </div>
            <div class="action-buttons mt-3">
                <a href="edit.php?scp=<?php echo htmlspecialchars($selected_scp); ?>" class="btn btn-warning btn-sm">Edit</a>
                <span class="separator">||</span>
                <a href="#" class="btn btn-danger btn-sm delete-btn" data-scp="<?php echo htmlspecialchars($selected_scp); ?>" data-title="<?php echo htmlspecialchars($unique_scp_entries[$selected_scp]['title']); ?>">Delete</a>
            </div>
            <div class="delete-confirm" id="confirm-<?php echo htmlspecialchars($selected_scp); ?>">
                <p>Are you sure you want to delete <?php echo htmlspecialchars($unique_scp_entries[$selected_scp]['title']); ?>?</p>
                <a href="delete.php?scp=<?php echo htmlspecialchars($selected_scp); ?>" class="btn btn-danger btn-sm">Yes, Delete</a>
                <a href="#" class="btn btn-secondary btn-sm cancel-delete">Cancel</a>
            </div>
            <button onclick="readText()" class="btn btn-primary mt-3">🔊 Listen to description</button>
            <script>
                function readText() {
                    const contentDiv = document.getElementById('scp-content');
                    const text = contentDiv.innerText || contentDiv.textContent;
                    if ('speechSynthesis' in window) {
                        const utterance = new SpeechSynthesisUtterance(text);
                        utterance.lang = 'en-US';
                        window.speechSynthesis.cancel();
                        window.speechSynthesis.speak(utterance);
                    } else {
                        alert('Sorry, your browser does not support speech synthesis.');
                    }
                }
            </script>
        <?php else: ?>
            <h2>Featured SCP Entries</h2>
            <div class="scp-container" id="scp-container">
                <?php foreach ($unique_scp_entries as $id => $scp): ?>
                    <div class="scp-item">
                        <img src="<?php echo htmlspecialchars($scp['image']); ?>" alt="<?php echo htmlspecialchars($scp['title']); ?>">
                        <h3><?php echo htmlspecialchars($scp['title']); ?></h3>
                        <a href="index.php?scp=<?php echo htmlspecialchars($id); ?>" class="read-btn">Read</a>
                    </div>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>
    </main>

    <footer>
        <p>© 2025 SCP Foundation Catalog. All rights reserved.</p>
    </footer>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js" integrity="sha384-YvpcrYf0tY3lHB60NNkmXc5s9fDVZLESaAA55NDzOxhy9GkcIdslK1eN7N6jIeHz" crossorigin="anonymous"></script>
    <script>
        document.querySelectorAll('.delete-btn').forEach(button => {
            button.addEventListener('click', function(e) {
                e.preventDefault();
                document.querySelectorAll('.delete-confirm').forEach(div => div.classList.remove('show'));
                const scpId = this.getAttribute('data-scp');
                const confirmDiv = document.getElementById('confirm-' + scpId);
                confirmDiv.classList.add('show');
            });
        });

        document.querySelectorAll('.cancel-delete').forEach(button => {
            button.addEventListener('click', function(e) {
                e.preventDefault();
                const confirmDiv = this.closest('.delete-confirm');
                confirmDiv.classList.remove('show');
            });
        });

        document.querySelectorAll('.alert.fade-out').forEach(alert => {
            alert.addEventListener('animationend', function() {
                this.style.display = 'none';
            });
        });
    </script>
</body>
</html>