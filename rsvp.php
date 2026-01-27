<?php
$success = false;
$error = false;
$errorMsg = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    $name    = trim($_POST['name'] ?? '');
    $email   = trim($_POST['email'] ?? '');
    $address = trim($_POST['address'] ?? '');
    $city    = trim($_POST['city'] ?? '');
    $state   = trim($_POST['state'] ?? '');
    $zip     = trim($_POST['zip'] ?? '');

    if ($name === '' || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $error = true;
        $errorMsg = 'Please enter a valid name and email.';
    } else {

        $conn = getenv('AZURE_STORAGE_CONNECTION_STRING');

        preg_match('/AccountName=([^;]+)/', $conn, $m1);
        preg_match('/AccountKey=([^;]+)/', $conn, $m2);

        $account = $m1[1] ?? null;
        $key     = $m2[1] ?? null;

        if (!$account || !$key) {
            $error = true;
            $errorMsg = 'Storage configuration error.';
        } else {

            $container = 'rsvp';
            $blob = 'wedding_guest_list_2026.csv';
            $timestamp = date('Y-m-d H:i:s');

            $row = [
                $timestamp,
                $name,
                $email,
                $address,
                $city,
                $state,
                $zip
            ];

            $csvLine = '"' . implode('","', array_map('addslashes', $row)) . '"' . "\n";
            $url = "https://$account.blob.core.windows.net/$container/$blob";

            $existing = @file_get_contents($url);
            if ($existing === false) {
                $existing = "\"Date Submitted\",\"Full Name\",\"Email\",\"Address\",\"City\",\"State\",\"Zip\"\n";
            }

            $data = $existing . $csvLine;
            $date = gmdate('D, d M Y H:i:s T');
            $len  = strlen($data);

            $stringToSign =
                "PUT\n\n\n$len\n\ntext/csv\n\n\n\n\n\n\n" .
                "x-ms-blob-type:BlockBlob\n" .
                "x-ms-date:$date\n" .
                "x-ms-version:2020-10-02\n" .
                "/$account/$container/$blob";

            $sig = base64_encode(
                hash_hmac('sha256', $stringToSign, base64_decode($key), true)
            );

            $headers = [
                "Authorization: SharedKey $account:$sig",
                "x-ms-blob-type: BlockBlob",
                "x-ms-date: $date",
                "x-ms-version: 2020-10-02",
                "Content-Type: text/csv",
                "Content-Length: $len"
            ];

            $ch = curl_init($url);
            curl_setopt_array($ch, [
                CURLOPT_CUSTOMREQUEST => 'PUT',
                CURLOPT_HTTPHEADER => $headers,
                CURLOPT_POSTFIELDS => $data,
                CURLOPT_RETURNTRANSFER => true
            ]);

            curl_exec($ch);
            $code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
            curl_close($ch);

            if ($code === 201) {
                $success = true;
            } else {
                $error = true;
                $errorMsg = 'Please try again.';
            }
        }
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
:root {
  --dusty-blue:#A8C3D1;
  --sage-green:#B7C9A9;
  --neutral:#FAF9F7;
}
body{background-color:var(--neutral);}
.btn-accent{
  background:linear-gradient(90deg,var(--sage-green),var(--dusty-blue));
  color:white;border-radius:999px;
  padding:.6rem 1.4rem;font-weight:500;
}
.card{
  background:white;padding:1.75rem;border-radius:1rem;
  border:1px solid rgba(0,0,0,.07);
  box-shadow:0 6px 18px rgba(0,0,0,.04);
}
</style>
</head>

<body class="antialiased text-gray-800 font-sans">

<header class="bg-white/80 sticky top-0 z-50 shadow-sm backdrop-blur">
  <div class="max-w-5xl mx-auto px-6 py-4 flex justify-between items-center">
    <a href="index.html" class="font-display text-2xl" style="color:var(--dusty-blue)">Samuel & Kelcee Baer</a>
    <nav class="hidden md:flex space-x-4 text-sm font-semibold">
      <a href="index.html" class="hover:text-[var(--sage-green)]">Home</a>
      <a href="rsvp.php" class="hover:text-[var(--sage-green)]">Address</a>
      <a href="registry.html" class="hover:text-[var(--sage-green)]">Registry</a>
      <a href="event-details.html" class="hover:text-[var(--sage-green)]">Event Details</a>
    </nav>
  </div>
</header>

<main class="max-w-2xl mx-auto px-4 py-12 space-y-8">

<h1 class="text-3xl font-display" style="color:var(--dusty-blue)">Request Invitation</h1>

<?php if ($success): ?>
<div class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded">
<strong>Sent!</strong> We’ve received your information. Thank you!
</div>
<?php endif; ?>

<?php if ($error): ?>
<div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded">
<strong>Error.</strong> <?= htmlspecialchars($errorMsg) ?>
</div>
<?php endif; ?>

<form method="POST" class="card space-y-4">
  <input name="name" required placeholder="Full Name" class="w-full rounded border-gray-200 p-2">
  <input name="email" type="email" required placeholder="Email" class="w-full rounded border-gray-200 p-2">
  <input name="address" placeholder="Address" class="w-full rounded border-gray-200 p-2">
  <div class="grid grid-cols-3 gap-3">
    <input name="city" placeholder="City" class="rounded border-gray-200 p-2">
    <input name="state" placeholder="State" class="rounded border-gray-200 p-2">
    <input name="zip" placeholder="Zip" class="rounded border-gray-200 p-2">
  </div>
  <button class="btn-accent mt-4">Submit Info</button>
</form>

</main>

<footer class="bg-white border-t mt-16 py-6 text-center text-sm text-gray-500">
© 2026 Samuel & Kelcee Baer — All Rights Reserved
</footer>

</body>
</html>
