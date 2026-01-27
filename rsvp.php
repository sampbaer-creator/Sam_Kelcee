<?php
// ============================
// HANDLE FORM SUBMISSION
// ============================
$success = false;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    $name    = trim($_POST['name'] ?? '');
    $email   = trim($_POST['email'] ?? '');
    $address = trim($_POST['address'] ?? '');
    $city    = trim($_POST['city'] ?? '');
    $state   = trim($_POST['state'] ?? '');
    $zip     = trim($_POST['zip'] ?? '');

    if ($name !== '' && filter_var($email, FILTER_VALIDATE_EMAIL)) {

        $timestamp = date('Y-m-d H:i:s');

        // Azure-safe writable location
        $filename = $_SERVER['HOME'] . '/site/wwwroot/wedding_guest_list_2026.csv';

        $file_exists = file_exists($filename);
        $file = fopen($filename, 'a');

        if (!$file_exists) {
            fputcsv($file, ['Date Submitted','Full Name','Email','Address','City','State','Zip']);
        }

        fputcsv($file, [$timestamp,$name,$email,$address,$city,$state,$zip]);
        fclose($file);

        // Email notification
        $headers  = "From: updates@kelceesam-agemdaagffethha2.westus3-01.azurewebsites.net\r\n";
        $headers .= "Reply-To: $email\r\n";

        mail(
            "sampbaer@gmail.com, kelcee5young@gmail.com",
            "Wedding Invite Request: $name",
            "Name: $name\nEmail: $email\nAddress: $address\nCity: $city\nState: $state\nZip: $zip\n\nSubmitted: $timestamp",
            $headers
        );

        mail(
            $email,
            "We've received your RSVP request!",
            "Hi $name,\n\nThank you for requesting an invitation!\n\n– Samuel & Kelcee",
            $headers
        );

        $success = true;
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
.btn-accent{background:linear-gradient(90deg,var(--sage-green),var(--dusty-blue));color:white;border-radius:999px;padding:.6rem 1.4rem;font-weight:500;box-shadow:0 2px 8px rgba(0,0,0,.08);}
.card{background:white;padding:1.75rem;border-radius:1rem;border:1px solid rgba(0,0,0,.07);box-shadow:0 6px 18px rgba(0,0,0,.04);}
</style>
</head>

<body class="antialiased text-gray-800 font-sans">

<main class="max-w-2xl mx-auto px-4 py-10 space-y-8">

<h1 class="text-3xl font-display mb-2" style="color: var(--dusty-blue)">Request Invitation</h1>

<?php if ($success): ?>
<div class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded">
<strong>Sent!</strong> We've received your info. Thank you!
</div>
<?php endif; ?>

<form method="POST" class="card space-y-4">
<input name="name" placeholder="Full Name" required class="w-full rounded border p-2">
<input name="email" type="email" placeholder="Email" required class="w-full rounded border p-2">
<input name="address" placeholder="Address" required class="w-full rounded border p-2">
<input name="city" placeholder="City" required class="w-full rounded border p-2">
<input name="state" placeholder="State" required class="w-full rounded border p-2">
<input name="zip" placeholder="Zip" required class="w-full rounded border p-2">
<button class="btn-accent mt-4" type="submit">Submit</button>
</form>

</main>
</body>
</html>
