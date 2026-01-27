<?php
$success = false;
$error = false;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    require __DIR__ . '/save_invite.php';
    $success = true;
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="utf-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1" />
  <title>RSVP — Samuel & Kelcee's Wedding</title>

  <script src="https://cdn.tailwindcss.com?plugins=forms,typography"></script>
  <link href="https://fonts.googleapis.com/css2?family=Playfair+Display:wght@400;700&family=Quicksand:wght@400;600&display=swap" rel="stylesheet">

  <style>
    :root { --dusty-blue:#A8C3D1; --sage-green:#B7C9A9; --neutral:#FAF9F7; }
    body { background-color: var(--neutral); }
    .btn-accent {
      background: linear-gradient(90deg, var(--sage-green), var(--dusty-blue));
      color: white;
      border-radius: 999px;
      padding: 0.6rem 1.4rem;
      font-weight: 500;
    }
    .card {
      background: white;
      padding: 1.75rem;
      border-radius: 1rem;
      box-shadow: 0 6px 18px rgba(0,0,0,0.04);
    }
  </style>
</head>

<body class="font-sans text-gray-800">

<main class="max-w-2xl mx-auto px-4 py-12 space-y-6">

<h1 class="text-3xl font-display" style="color: var(--dusty-blue)">Request Invitation</h1>

<?php if ($success): ?>
<div class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded">
✅ Thank you! Your information has been submitted.
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
