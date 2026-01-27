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

        $timestamp = date('Y-m-d H:i:s');
        $csvPath = '/home/site/wwwroot/wedding_guest_list_2026.csv';

        $fileExists = file_exists($csvPath);
        $file = fopen($csvPath, 'a');

        if ($file !== false) {

            if (!$fileExists) {
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
                $timestamp,
                $name,
                $email,
                $address,
                $city,
                $state,
                $zip
            ]);

            fclose($file);

            // Email both of you
            $to = 'sampbaer@gmail.com, kelcee5young@gmail.com';
            $subject = "New Wedding RSVP — $name";
            $message =
                "New RSVP Submission\n\n" .
                "Name: $name\n" .
                "Email: $email\n" .
                "Address: $address\n" .
                "City: $city\n" .
                "State: $state\n" .
                "Zip: $zip\n\n" .
                "Submitted: $timestamp";

            $headers =
                "From: RSVP <no-reply@" . $_SERVER['HTTP_HOST'] . ">\r\n" .
                "Reply-To: $email\r\n";

            @mail($to, $subject, $message, $headers);

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
    :root {
      --dusty-blue:#A8C3D1;
      --sage-green:#B7C9A9;
      --neutral:#FAF9F7;
    }

    body { background-color: var(--neutral); }

    .btn-accent {
      background: linear-gradient(90deg, var(--sage-green), var(--dusty-blue));
      color: white;
      border-radius: 999px;
      padding: 0.6rem 1.4rem;
      font-weight: 500;
      box-shadow: 0 2px 8px rgba(0,0,0,.08);
      transition: 0.2s;
    }

    .btn-accent:hover {
      transform: translateY(-1px);
      box-shadow: 0 4px 16px rgba(0,0,0,.12);
      opacity: .95;
    }

    .card {
      background: white;
      padding: 1.75rem;
      border-radius: 1rem;
      border: 1px solid rgba(0,0,0,.07);
      box-shadow: 0 6px 18px rgba(0,0,0,.04);
    }
  </style>
</head>

<body class="antialiased text-gray-800 font-sans">

<header class="bg-white/80 sticky top-0 z-50 shadow-sm backdrop-blur">
  <div class="max-w-5xl mx-auto px-6 py-4 flex justify-between items-center">
    <a href="index.html" class="font-display text-2xl" style="color: var(--dusty-blue)">Samuel & Kelcee Baer</a>
    <nav class="hidden md:flex space-x-4 text-sm font-semibold">
      <a href="index.html" style="color: var(--dusty-blue)">Home</a>
      <a href="rsvp.php" class="hover:text-[var(--sage-green)]">Address</a>
      <a href="registry.html" class="hover:text-[var(--sage-green)]">Registry</a>
      <a href="event-details.html" class="hover:text-[var(--sage-green)]">Event Details</a>
    </nav>
  </div>
</header>

<main class="max-w-2xl mx-auto px-4 py-10 space-y-6">

  <h1 class="text-3xl font-display" style="color: var(--dusty-blue)">Request Invitation</h1>

  <?php if ($success): ?>
    <div class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded">
      <strong>Success!</strong> Your information has been received.
    </div>
  <?php endif; ?>

  <?php if ($error): ?>
    <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded">
      <strong>Error.</strong> Please try again.
    </div>
  <?php endif; ?>

  <p>We would love for you to celebrate with us! Please share your information below.</p>

  <form method="POST" class="card space-y-4">
    <input name="name" required placeholder="Full Name" class="w-full rounded border p-2">
    <input name="email" type="email" required placeholder="Email" class="w-full rounded border p-2">
    <input name="address" required placeholder="Address" class="w-full rounded border p-2">
    <input name="city" required placeholder="City" class="w-full rounded border p-2">
    <input name="state" required placeholder="State" class="w-full rounded border p-2">
    <input name="zip" required placeholder="Zip Code" class="w-full rounded border p-2">

    <button type="submit" class="btn-accent mt-2">Submit</button>
  </form>

</main>

<footer class="bg-white border-t mt-16 py-6 text-center text-sm text-gray-500">
  © 2026 Samuel & Kelcee Baer — All Rights Reserved
</footer>

</body>
</html>
