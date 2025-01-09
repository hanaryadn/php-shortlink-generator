<?php
// Database connection
$host = 'localhost';
$dbname = 'db_short';
$user = 'root';
$password = '';

try {
    $pdo = new PDO("mysql:host=$host;dbname=$dbname", $user, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException $e) {
    die("Database connection failed: " . $e->getMessage());
}

// Generate short code
function generateShortCode($length = 6) {
    return substr(str_shuffle("abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789"), 0, $length);
}

// Handle URL shortening
$short_code = null;
$error_message = null;
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['original_url'])) {
    $original_url = trim($_POST['original_url']);
    $custom_code = isset($_POST['custom_code']) ? trim($_POST['custom_code']) : null;

    // Validate URL
    if (!filter_var($original_url, FILTER_VALIDATE_URL)) {
        $error_message = "Invalid URL format.";
    } else {
        // Check if custom code is provided and validate it
        if ($custom_code) {
            if (!preg_match('/^[a-zA-Z0-9_-]+$/', $custom_code)) {
                $error_message = "Custom code can only contain letters, numbers, dashes, and underscores.";
            } else {
                // Check if custom code already exists
                $stmt = $pdo->prepare("SELECT id FROM short_links WHERE short_code = :short_code");
                $stmt->execute(['short_code' => $custom_code]);
                if ($stmt->rowCount() > 0) {
                    $error_message = "Custom code is already in use!";
                } else {
                    $short_code = $custom_code;
                }
            }
        }

        // If no custom code or no error, generate a unique short code
        if (!$short_code && !$error_message) {
            do {
                $short_code = generateShortCode();
                $stmt = $pdo->prepare("SELECT id FROM short_links WHERE short_code = :short_code");
                $stmt->execute(['short_code' => $short_code]);
            } while ($stmt->rowCount() > 0);
        }

        // Insert into database if no errors
        if (!$error_message) {
            $stmt = $pdo->prepare("INSERT INTO short_links (short_code, original_url) VALUES (:short_code, :original_url)");
            $stmt->execute(['short_code' => $short_code, 'original_url' => $original_url]);
        }
    }
}

// Handle redirection
if (isset($_GET['code'])) {
    $short_code = $_GET['code'];

    $stmt = $pdo->prepare("SELECT original_url FROM short_links WHERE short_code = :short_code");
    $stmt->execute(['short_code' => $short_code]);
    $result = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($result) {
        header("Location: " . $result['original_url']);
        exit;
    } else {
        die("URL not found.");
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <title>Kurz - Shorten your Link</title>
    <!-- required -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/animate.css/4.1.1/animate.min.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
    <link rel="stylesheet" href="assets/style.css">
    <!-- favicon -->
    <link rel="icon" type="image/x-icon" href="assets/favicon.ico">
</head>
<body class="bg-dark">
    <div class="custom-header animate__heartBeat">
        <div class="text-center text-light mt-2 display-3">
            <span style="color: #08c0bc;">KURZ</span>
        </div>
        <h5 class="text-center">
            <p class="text-light" style="text-decoration: underline dotted;">
                Shorten <span style="color: #08c0bc;">Your Link</span>
            </p>
        </h5>
    </div>

    <div class="container mt-2">
        <div class="card custom-card p-4 shadow-lg mx-auto animate__animated animate__fadeInUp" style="max-width: 500px;">
            <div class="card-body">
                <form method="POST">
                    <div class="mb-3">
                        <label for="original_url" class="form-label">Enter URL:</label>
                        <input type="url" id="original_url" name="original_url" class="form-control shadow" placeholder="Enter your loooooooong URL" required>
                    </div>
                    <div class="mb-3">
                        <label for="custom_code" class="form-label">Custom Short Code (optional):</label>
                        <input type="text" id="custom_code" name="custom_code" class="form-control shadow" placeholder="https://example.com/your-code">
                    </div>
                    <div class="d-grid">
                        <button type="submit" class="btn btn-short">Shorten <i class="bi bi-scissors"></i></button>
                    </div>
                </form>
                <!-- result -->
                <div id="loading" class="text-center mt-3" style="display: none;">
                    <div class="spinner-border text-primary" role="status">
                        <span class="visually-hidden">Please wait ...</span>
                    </div>
                </div>
                <?php if ($error_message): ?>
                    <div class="alert alert-danger mt-4 text-center">
                        <?= htmlspecialchars($error_message) ?>
                    </div>
                <?php elseif ($short_code): ?>
                    <div class="alert alert-success mt-4 text-center">
                        Done, Here's your New URL: <br>
                        <div class="form-control" style="border: dashed;" readonly>
                            <a href="http://localhost/<?= $short_code ?>" target="_blank" id="shortlink">http://localhost/<?= $short_code ?></a>
                        </div>
                        <button class="btn btn-sm btn-copy mt-2" id="copyButton"><i class="bi bi-copy"></i> Copy</button>
                    </div>
                <?php endif; ?>
            </div>
            <hr>
            <div class="mx-auto">
                <a href="https://github.com/hanaryadn" target="_blank" style="text-decoration: none;"><span class="small text-dark"><i class="bi bi-github"></i> hanary.adn</span></a>
            </div>
        </div>
    </div>
    <!-- JS -->
    <script>
        document.querySelector('form').addEventListener('submit', function() {
            document.getElementById('loading').style.display = 'block';
        });

        document.getElementById('copyButton')?.addEventListener('click', function() {
            const shortlink = document.getElementById('shortlink').href;
            navigator.clipboard.writeText(shortlink).then(() => {
                alert('Shortlink copied to clipboard!');
            }).catch(err => {
                alert('Failed to copy: ' + err);
            });
        });
    </script>
</body>
</html>
