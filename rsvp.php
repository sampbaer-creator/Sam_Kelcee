<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

$success = false;
$errorMsg = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    $name    = trim($_POST['name'] ?? '');
    $email   = trim($_POST['email'] ?? '');
    $address = trim($_POST['address'] ?? '');
    $city    = trim($_POST['city'] ?? '');
    $state   = trim($_POST['state'] ?? '');
    $zip     = trim($_POST['zip'] ?? '');

    if ($name === '' || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $errorMsg = 'Validation failed';
    } else {

        $storageDir = $_SERVER['HOME'] . '/site/storage';
        $filename   = $storageDir . '/wedding_guest_list_2026.csv';

        if (!is_dir($storageDir)) {
            if (!mkdir($storageDir, 0777, true)) {
                $errorMsg = 'Failed to create storage directory';
            }
        }

        if ($errorMsg === '') {
            $file = fopen($filename, 'a');

            if ($file === false) {
                $errorMsg = 'Failed to open file for writing: ' . $filename;
            } else {
                if (filesize($filename) === 0) {
                    fputcsv($file, ['Date','Name','Email','Address','City','State','Zip']);
                }

                fputcsv($file, [
                    date('Y-m-d H:i:s'),
                    $name,
                    $email,
                    $address,
                    $city,
                    $state,
                    $zip
                ]);

                fclose($file);
                $success = true;
            }
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="utf-8">
<title>RSVP Debug</title>
<style>
body{font-family:sans-serif;background:#fafafa;padding:40px}
.ok{background:#d1fae5;border:1px solid #10b981;padding:12px}
.err{background:#fee2e2;border:1px solid #ef4444;padding:12px}
</style>
</head>
<body>

<h2>RSVP Debug Page</h2>

<?php if ($success): ?>
<div class="ok">✅ SUCCESS — CSV written correctly</div>
<?php endif; ?>

<?php if ($errorMsg): ?>
<div class="err">❌ ERROR: <?= htmlspecialchars($errorMsg) ?></div>
<?php endif; ?>

<form method="POST">
<input name="name" placeholder="Name" required><br><br>
<input name="email" placeholder="Email" required><br><br>
<input name="address" placeholder="Address" required><br><br>
<input name="city" placeholder="City" required><br><br>
<input name="state" placeholder="State" required><br><br>
<input name="zip" placeholder="Zip" required><br><br>
<button type="submit">Submit</button>
</form>

</body>
</html>
