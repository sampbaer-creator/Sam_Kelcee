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

        // ✅ Azure writable location (already exists)
        $filename = $_SERVER['HOME'] . '/site/storage/wedding_guest_list_2026.csv';

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
<meta charset="utf-8" />
<meta name="viewport" content="width=device-width, initial-scale=1" />
<title>RSVP — Samuel & Kelcee's Wedding</title>

<script src="https://cdn.tailwindcss.com?plugins=forms,typography,aspect-ratio"></script>
<link href="https://fonts.googleapis.com/css2?family=Playfair+Display:wght@400;700&family=Quicksand:wght@400;600&display=swap" rel="stylesheet">

<style>
:root { --dusty-blue:#A8C3D1; --sage-green:#B7C9A9; --neutral:#FAF9F7; }
body{background-color:var(--neutral);}
.btn-accent{background:linear-gradient(90deg,var(--sage-green),var(--dusty-blue));color:white;border-radius:999px;padding:.6rem 1.4rem;font-weight:500;}
.card{background:white;padding:1.75rem;border-radius:1rem;border:1px solid rgba(0,0,0,.07);}
</style>
</head>

<body class="antialiased text-gray-800 font-sans">

<main class="max-w-2xl mx-auto px-4 py-10 space-y-6">

<h1 class="text-3xl font-display" style="color: var(--dusty-blue)">Request Invitation</h1>

<?php if ($success): ?>
<div class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded">
<strong>Success!</strong> Your information was saved.
</div>
<?php endif; ?>

<?php if ($error): ?>
<div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded">
<strong>Error.</strong> Please try again.
</div>
<?php endif; ?>

<form method="POST" class="card space-y-3">
<input name="name" placeholder="Full Name" required class="w-full border rounded p-2">
<input name="email" type="email" placeholder="Email" required class="w-full border rounded p-2">
<input name="address" placeholder="Address" required class="w-full border rounded p-2">
<input name="city" placeholder="City" required class="w-full border rounded p-2">
<input name="state" placeholder="State" required class="w-full border rounded p-2">
<input name="zip" placeholder="Zip" required class="w-full border rounded p-2">
<button type="submit" class="btn-accent mt-2">Submit</button>
</form>

</main>
</body>
</html>
