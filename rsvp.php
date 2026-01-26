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
    .btn-accent{background:linear-gradient(90deg,var(--sage-green),var(--dusty-blue));color:white;border-radius:999px;padding:.6rem 1.4rem;font-weight:500;box-shadow:0 2px 8px rgba(0,0,0,.08);transition:.2s;border:none;cursor:pointer;}
    .btn-accent:hover{transform:translateY(-1px);box-shadow:0 4px 16px rgba(0,0,0,.12);opacity:.95;}
    .card{background:white;padding:1.75rem;border-radius:1rem;border:1px solid rgba(0,0,0,.07);box-shadow:0 6px 18px rgba(0,0,0,.04);}
  </style>
</head>
<body class="antialiased text-gray-800 font-sans">

<header class="bg-white/80 sticky top-0 z-50 shadow-sm backdrop-blur">
  <div class="max-w-5xl mx-auto px-6 py-4 flex justify-between items-center">
    <a href="index.html" class="font-display text-2xl" style="color: var(--dusty-blue)">Samuel & Kelcee Baer</a>
    <nav class="hidden md:flex space-x-4 text-sm font-semibold">
      <a href="index.html" class="hover:text-[var(--sage-green)]" style="color: var(--dusty-blue)">Home</a>
      <a href="rsvp.php" class="hover:text-[var(--sage-green)]">Address</a>
      <a href="registry.html" class="hover:text-[var(--sage-green)]">Registry</a>
      <a href="event-details.html" class="hover:text-[var(--sage-green)]">Event Details</a>
    </nav>
  </div>
</header>

<main class="max-w-2xl mx-auto px-4 py-10 space-y-8">
  <h1 class="text-3xl font-display mb-2" style="color: var(--dusty-blue)">Request Invitation</h1>

  <?php if(isset($_GET['status']) && $_GET['status'] == 'success'): ?>
    <div class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded relative">
      <strong class="font-bold">Sent!</strong> We've received your info. Thank you!
    </div>
  <?php endif; ?>

  <p>We would love for you to come celebrate with us! Please let us know your information below.</p>

  <form class="card space-y-4" action="save_invite.php" method="POST">
    <div class="grid md:grid-cols-2 gap-4">
      <label class="block">
        <span class="text-sm font-semibold">Full name</span>
        <input name="name" required class="mt-1 block w-full rounded-md border-gray-200 shadow-sm focus:ring-[var(--sage-green)]" />
      </label>
      <label class="block">
        <span class="text-sm font-semibold">Email</span>
        <input name="email" type="email" required class="mt-1 block w-full rounded-md border-gray-200 shadow-sm focus:ring-[var(--sage-green)]" />
      </label>
      <label class="block md:col-span-2">
        <span class="text-sm font-semibold">Address</span>
        <input name="address" required class="mt-1 block w-full rounded-md border-gray-200 shadow-sm focus:ring-[var(--sage-green)]" />
      </label>
      <div class="grid md:grid-cols-3 gap-4 md:col-span-2">
        <label class="block">
          <span class="text-sm font-semibold">City</span>
          <input name="city" required class="mt-1 block w-full rounded-md border-gray-200 shadow-sm focus:ring-[var(--sage-green)]" />
        </label>
        <label class="block">
          <span class="text-sm font-semibold">State</span>
          <input name="state" required class="mt-1 block w-full rounded-md border-gray-200 shadow-sm focus:ring-[var(--sage-green)]" />
        </label>
        <label class="block">
          <span class="text-sm font-semibold">Zip Code / Postal Code</span>
          <input name="zip" required pattern="[A-Za-z0-9\s-]{3,10}" class="mt-1 block w-full rounded-md border-gray-200 shadow-sm focus:ring-[var(--sage-green)]" />
        </label>
      </div>
    </div>
    <button type="submit" class="btn-accent mt-4">Submit Info</button>
  </form>
</main>

<footer class="bg-white border-t mt-16 py-6 text-center text-sm text-gray-500">
  © 2026 Samuel & Kelcee Baer — All Rights Reserved
</footer>
</body>
</html>
