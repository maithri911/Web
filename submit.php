<?php
// submit.php
declare(strict_types=1);
header('Content-Type: text/html; charset=utf-8');

// If someone visits this page directly, send them back to the form:
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: index.html');
    exit;
}

// --- Collect + sanitize raw POST values ---
$name   = isset($_POST['name'])   ? trim((string)$_POST['name'])   : '';
$email  = isset($_POST['email'])  ? trim((string)$_POST['email'])  : '';
$phone  = isset($_POST['phone'])  ? trim((string)$_POST['phone'])  : '';
$gender = isset($_POST['gender']) ? trim((string)$_POST['gender']) : null;
$course = isset($_POST['course']) ? trim((string)$_POST['course']) : null;

// --- Basic server-side validation ---
$errors = [];
if ($name === '') {
    $errors[] = 'Name is required.';
}
if ($email === '' || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
    $errors[] = 'A valid email is required.';
}
if ($phone === '') {
    $errors[] = 'Phone is required.';
}

if (!empty($errors)) {
    // Show errors and stop
    echo '<!doctype html><html><head><meta charset="utf-8"><link rel="stylesheet" href="style.css"></head><body>';
    echo '<div class="container"><h2>Validation error</h2><ul>';
    foreach ($errors as $e) {
        echo '<li>' . htmlspecialchars($e) . '</li>';
    }
    echo '</ul><p><a href="index.html">Back to form</a></p></div></body></html>';
    exit;
}

// --- DB configuration (XAMPP defaults) ---
$host = '127.0.0.1';
$db   = 'web_assignments_db';
$user = 'root';
$pass = '';           // XAMPP default for root is empty
$charset = 'utf8mb4';

$dsn = "mysql:host=$host;dbname=$db;charset=$charset";
$options = [
    PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
];

try {
    $pdo = new PDO($dsn, $user, $pass, $options);
} catch (PDOException $e) {
    // DB connection failed
    echo '<!doctype html><html><head><meta charset="utf-8"><link rel="stylesheet" href="style.css"></head><body>';
    echo '<div class="container"><h2>Database error</h2>';
    echo '<p>Could not connect to database. Please check your DB settings.</p>';
    // Show message for development; remove or hide in production
    echo '<pre>' . htmlspecialchars($e->getMessage()) . '</pre>';
    echo '<p><a href="index.html">Back to form</a></p></div></body></html>';
    exit;
}

// --- Insert into DB using prepared statement ---
try {
    $sql = "INSERT INTO registrations (name, email, phone, gender, course)
            VALUES (:name, :email, :phone, :gender, :course)";
    $stmt = $pdo->prepare($sql);
    $stmt->execute([
        ':name'   => $name,
        ':email'  => $email,
        ':phone'  => $phone,
        ':gender' => $gender,
        ':course' => $course,
    ]);
    $insertId = (int)$pdo->lastInsertId();

    // Prepare safe output
    $safe = fn($v) => htmlspecialchars($v ?: '—', ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');

    // Show success + formatted display (uses your style.css)
    echo '<!doctype html><html><head><meta charset="utf-8"><link rel="stylesheet" href="style.css"></head><body>';
    echo '<div class="container">';
    echo '<h2>Registration successful!</h2>';
    echo '<p><strong>Record ID:</strong> ' . $insertId . '</p>';
    echo '<p><strong>Name:</strong> ' . $safe($name) . '</p>';
    echo '<p><strong>Email:</strong> ' . $safe($email) . '</p>';
    echo '<p><strong>Phone:</strong> ' . $safe($phone) . '</p>';
    echo '<p><strong>Gender:</strong> ' . $safe($gender) . '</p>';
    echo '<p><strong>Course:</strong> ' . $safe($course) . '</p>';
    echo '<p><a href="index.html">Register another</a></p>';
    echo '</div></body></html>';

} catch (Exception $e) {
    // Insert failed
    echo '<!doctype html><html><head><meta charset="utf-8"><link rel="stylesheet" href="style.css"></head><body>';
    echo '<div class="container"><h2>Insert failed</h2>';
    echo '<p>Could not save your registration. Please try again later.</p>';
    // Helpful debug message for development
    echo '<pre>' . htmlspecialchars($e->getMessage()) . '</pre>';
    echo '<p><a href="index.html">Back to form</a></p></div></body></html>';
    exit;
}