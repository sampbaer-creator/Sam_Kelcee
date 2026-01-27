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

    if ($name && filter_var($email, FILTER_VALIDATE_EMAIL)) {

        $timestamp = date('Y-m-d H:i:s');

        // ✅ SAFE, WRITABLE LOCATION
        $filename = '/home/site/wwwroot/wedding_guest_list_2026.csv';

        $fileExists = file_exists($filename);
        $fp = fopen($filename, 'a');

        if ($fp) {
            if (!$fileExists) {
                fputcsv($fp, ['Date Submitted','Name','Email','Address','City','State','Zip']);
            }

            fputcsv($fp, [$timestamp,$name,$email,$address,$city,$state,$zip]);
            fclose($fp);

            // ✅ EMAIL BOTH OF YOU
            $to = "sampbaer@gmail.com, kelcee5young@gmail.com";
            $subject = "New Wedding RSVP Submission";
            $message =
                "New RSVP received:\n\n" .
                "Name: $name\n" .
                "Email: $email\n" .
                "Address: $address\n" .
                "City: $city\n" .
                "State: $state\n" .
                "Zip: $zip\n\n" .
                "Submitted: $timestamp";

            $headers = "From: rsvp@kelceesam.azurewebsites.net\r\n";
            $headers .= "Reply-To: $email\r\n";

            mail($to, $subject, $message, $headers);

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
<meta name="viewport" content="width=device-width, initial-scale=1">
<title>RSVP — Samuel & Kelcee</title>

<script src="https://cdn.tailwindcss.com?plugins=forms"></script>
<link href="https://fonts.googleapis.com/css2?family=Playfair+Display:wght@400;700&family=Quicksand:wght@400;600&display=swap" rel="stylesheet">

<style>
:root { --dusty-blue:#A8C3D1; --sage-green:#B7C9A9; --neutral:#FAF9F7; }
body { background-color: var(--neutral); }
.btn-accent {
  background: linear-gradient(90deg,var(--sage-green),var(--dusty-blue));
  color:white; border-radius:999px; padding:.6rem 1.4rem; font-weight:500;
}
.card {
  background:white; padding:1.75rem; border-radius:1rem;
  box-shadow:0 6px 18px rgba(0,0,0,.04);
}
</style>
</head>

<body class="font-sans text-gray-800">

<main class="max-w-2xl mx-auto px-4 py-12 space-y-6">

<h1 class="text-3xl font-display" style="color:var(--dusty-blue)">Request Invitation</h1>

<?php if ($success): ?>
<div class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded">
✅ Thank you! Your information has been received.
</div>
<?php endif; ?>

<?php if ($error): ?>
<div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded">
❌ Something went wrong. Please try again.
</div>
<?php endif; ?>

<form method="POST" class="card space-y-4">
<input name="name" required placeholder="Full Name" class="w-full p-2 border rounded">
<input name="email" type="email" required placeholder="Email" class="w-full p-2 border rounded">
<input name="address" required placeholder="Address" class="w-full p-2 border rounded">
<input name="city" required placeholder="City" class="w-full p-2 border rounded">
<input name="state" required placeholder="State" class="w-full p-2 border rounded">
<input name="zip" required placeholder="Zip Code" class="w-full p-2 border rounded">

<button type="submit" class="btn-accent">Submit</button>
</form>

</main>
</body>
</html>
