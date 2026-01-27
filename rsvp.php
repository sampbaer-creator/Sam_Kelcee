<?php
$success = false;
$error = false;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    $name    = trim($_POST['name'] ?? '');
    $email   = trim($_POST['email'] ?? '');
    $address = trim($_POST['address'] ?? '');
    $city    = trim($_POST['city'] ?? '');
    $state   = trim($_POST['state'] ?? '');
    $zip     = trim($_POST['zip'] ?? '');

    if ($name !== '' && filter_var($email, FILTER_VALIDATE_EMAIL)) {

        // ✅ ALWAYS writable on Azure Linux
        $filename = sys_get_temp_dir() . '/wedding_guest_list_2026.csv';

        $file_exists = file_exists($filename);
        $file = fopen($filename, 'a');

        if ($file !== false) {

            if (!$file_exists) {
                fputcsv($file, [
                    'Date Submitted',
                    'Full Name',
                    'Email',
                    'Address',
                    'City',
                    'State',
                    'Zip'
                ]);
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
        } else {
            $error = true;
        }

    } else {
        $error = true;
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="utf-8">
<title>RSVP</title>
<script src="https://cdn.tailwindcss.com"></script>
<style>
body{background:#faf9f7;font-family:sans-serif}
.card{background:#fff;padding:1.5rem;border-radius:1rem}
.btn{background:#A8C3D1;color:#fff;padding:.6rem 1.4rem;border-radius:999px}
</style>
</head>
<body>

<main class="max-w-xl mx-auto p-8 space-y-4">

<h1 class="text-3xl">Request Invitation</h1>

<?php if ($success): ?>
<div class="bg-green-100 border border-green-400 text-green-700 p-3 rounded">
✅ Saved successfully
</div>
<?php endif; ?>

<?php if ($error): ?>
<div class="bg-red-100 border border-red-400 text-red-700 p-3 rounded">
❌ Error — please try again
</div>
<?php endif; ?>

<form method="POST" class="card space-y-2">
<input name="name" placeholder="Name" required class="w-full border p-2 rounded">
<input name="email" placeholder="Email" required class="w-full border p-2 rounded">
<input name="address" placeholder="Address" required class="w-full border p-2 rounded">
<input name="city" placeholder="City" required class="w-full border p-2 rounded">
<input name="state" placeholder="State" required class="w-full border p-2 rounded">
<input name="zip" placeholder="Zip" required class="w-full border p-2 rounded">
<button class="btn mt-2">Submit</button>
</form>

</main>
</body>
</html>
