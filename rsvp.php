<?php
$success = false;
$error = false;
$errorMsg = '';

// --- NEW HELPER FUNCTION ---
function getAzureEnv($key) {
    // Try $_ENV first (Most reliable on Azure)
    if (isset($_ENV[$key])) return $_ENV[$key];
    
    // Try Server global
    if (isset($_SERVER[$key])) return $_SERVER[$key];
    
    // Try standard getenv
    $val = getenv($key);
    if ($val !== false && $val !== '') return $val;

    return null;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // 1. Capture Inputs
    $name    = trim($_POST['name'] ?? '');
    $email   = trim($_POST['email'] ?? '');
    $address = trim($_POST['address'] ?? '');
    $city    = trim($_POST['city'] ?? '');
    $state   = trim($_POST['state'] ?? '');
    $zip     = trim($_POST['zip'] ?? '');

    // 2. Validate
    if ($name === '' || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $error = true;
        $errorMsg = 'Please enter a valid name and email.';
    } else {
        // 3. Connect
        if (file_exists('db_connect.php')) {
            require_once 'db_connect.php';
        } else {
            $error = true;
            $errorMsg = 'Server error: Missing database connector.';
        }

        if (!$error) {
            // --- USE THE NEW FUNCTION HERE ---
            $host = getAzureEnv('COSMOS_ENDPOINT');
            $key = getAzureEnv('COSMOS_KEY');
            
            $database = 'WeddingDB'; 
            $container = 'Guests';

            // Debug Logic
            if (!$host || !$key) {
                $error = true;
                $errorMsg = 'Database configuration error. Please check App Service settings.';
            } else {
                $cosmos = new CosmosDB($host, $key, $database, $container);
                
                $guestData = [
                    'name'    => $name,
                    'email'   => $email,
                    'address' => $address,
                    'city'    => $city,
                    'state'   => $state,
                    'zip'     => $zip,
                    'date'    => date('Y-m-d H:i:s')
                ];

                $result = $cosmos->createDocument($guestData);

                if ($result['code'] >= 200 && $result['code'] < 300) {
                    $success = true;
                } else {
                    $error = true;
                    $errorMsg = 'Could not save RSVP. Error Code: ' . $result['code'];
                }
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