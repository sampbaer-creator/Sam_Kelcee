<?php
// --- CONFIGURATION ---
// 1. Paste your Primary Key between the quotes below
$my_key  = 'ezQFDCdIGw1gvKIlceXfnymph21fhb7gxP1EcsFqOCnzxkf9DtGp9yuKfvZfaZy3hKjSJhClPGSXACDbj1DlrQ=='; 

// 2. Configuration for Azure
$my_host = 'https://kelceesam.documents.azure.com:443/'; 
$my_db   = 'WeddingDB';
$my_col  = 'Guests';

// --- COSMOS DB CLASS (Do not edit) ---
class CosmosDB {
    private $host, $key, $db, $coll;
    public function __construct($host, $key, $db, $coll) {
        $this->host = $host;
        $this->key = $key;
        $this->db = $db;
        $this->coll = $coll;
    }
    
    private function request($verb, $rid, $rtype, $data = null, $pk = null) {
        $date = gmdate('D, d M Y H:i:s T');
        $keyDecoded = base64_decode($this->key);
        
        $sigString = strtolower($verb) . "\n" . 
                     strtolower($rtype) . "\n" . 
                     $rid . "\n" . 
                     strtolower($date) . "\n" . 
                     "" . "\n";
                     
        $sig = base64_encode(hash_hmac('sha256', $sigString, $keyDecoded, true));
        $auth = urlencode("type=master&ver=1.0&sig=$sig");
        
        $headers = [
            "Authorization: $auth",
            "x-ms-date: $date",
            "x-ms-version: 2018-12-31",
            "Content-Type: application/json"
        ];

        // Add Partition Key Header
        if ($pk !== null) {
            $headers[] = "x-ms-documentdb-partitionkey: " . json_encode([$pk]);
        }
        
        $cleanHost = parse_url($this->host, PHP_URL_HOST);
        $url = "https://" . $cleanHost . "/dbs/{$this->db}/colls/{$this->coll}/$rtype";
        
        $ch = curl_init($url);
        curl_setopt($ch, CURLOPT_CUSTOMREQUEST, $verb);
        curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, true); 
        if ($data) curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));
        
        $res = curl_exec($ch);
        $code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        $curlErr = curl_error($ch);
        curl_close($ch);
        
        return ['code' => $code, 'data' => json_decode($res, true), 'curl_err' => $curlErr];
    }
    
    public function createDocument($data) {
        if (!isset($data['id'])) $data['id'] = uniqid(); 
        $rid = "dbs/{$this->db}/colls/{$this->coll}";
        $partitionKey = $data['email'] ?? null;
        return $this->request('POST', $rid, 'docs', $data, $partitionKey);
    }
}

// --- FORM HANDLING ---
$success = false; $error = false; $errorMsg = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = trim($_POST['name'] ?? '');
    $email = trim($_POST['email'] ?? '');
    
    if ($name === '' || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $error = true; $errorMsg = 'Please enter a valid name and email.';
    } else {
        $cosmos = new CosmosDB($my_host, $my_key, $my_db, $my_col);
        
        $guestData = [
            'name' => $name, 'email' => $email,
            'address' => $_POST['address'] ?? '',
            'city' => $_POST['city'] ?? '',
            'state' => $_POST['state'] ?? '',
            'zip' => $_POST['zip'] ?? '',
            'date' => date('Y-m-d H:i:s')
        ];
        
        $result = $cosmos->createDocument($guestData);
        
        if ($result['code'] >= 200 && $result['code'] < 300) {
            $success = true;
        } else {
            $error = true;
            $errorMsg = 'Error ' . $result['code'] . ': ' . ($result['data']['message'] ?? 'Unknown Error');
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="utf-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1" />
  <title>Address — Samuel & Kelcee</title>

  <script src="https://cdn.tailwindcss.com?plugins=forms,typography,aspect-ratio"></script>

  <link href="https://fonts.googleapis.com/css2?family=Playfair+Display:wght@400;700&family=Quicksand:wght@400;600&display=swap" rel="stylesheet">

  <style>
    :root {
      --dusty-blue: #A8C3D1;
      --sage-green: #B7C9A9;
      --blush-pink: #F3CFC6;
      --neutral: #FAF9F7;
    }

    body { background-color: var(--neutral); }

    .btn-accent {
      background: linear-gradient(90deg, var(--sage-green), var(--dusty-blue));
      color: white;
      border-radius: 999px;
      padding: 0.6rem 1.4rem;
      font-weight: 500;
      box-shadow: 0 2px 8px rgba(0,0,0,0.08);
      transition: 0.2s ease-in-out;
      display: inline-block;
      text-decoration: none;
      cursor: pointer;
      border: none;
    }
    .btn-accent:hover {
      transform: translateY(-1px);
      box-shadow: 0 4px 16px rgba(0,0,0,0.12);
      opacity: 0.95;
    }

    .card {
      background: white;
      padding: 1.75rem;
      border-radius: 1rem;
      border: 1px solid rgba(0,0,0,0.07);
      box-shadow: 0 6px 18px rgba(0,0,0,0.04);
    }

    .fade-in { opacity: 0; transform: translateY(10px); transition: opacity 0.6s ease, transform 0.6s ease; }
    .is-visible { opacity: 1; transform: none; }

    #menu-overlay { position: fixed; inset: 0; background-color: rgba(0,0,0,0.4); z-index: 30; display: none; }
    #mobile-menu { position: fixed; top:0; left:0; height:100%; width:250px; background:white; z-index:40; transform:translateX(-100%); transition: transform 0.3s ease-in-out; padding:2rem 1.5rem; display:flex; flex-direction:column; gap:1rem; }
    #mobile-menu.open { transform: translateX(0); }
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
      <button id="menu-btn" class="md:hidden text-2xl font-bold focus:outline-none" style="color: var(--dusty-blue)">☰</button>
    </div>
  </header>

  <div id="menu-overlay"></div>

  <nav id="mobile-menu" class="md:hidden">
    <a href="index.html" class="hover:text-[var(--sage-green)] text-lg font-semibold" style="color: var(--dusty-blue)">Home</a>
    <a href="rsvp.php" class="hover:text-[var(--sage-green)] text-lg font-semibold">Address</a>
    <a href="registry.html" class="hover:text-[var(--sage-green)] text-lg font-semibold">Registry</a>
    <a href="event-details.html" class="hover:text-[var(--sage-green)] text-lg font-semibold">Event Details</a>
  </nav>

  <main class="max-w-2xl mx-auto px-4 py-12 space-y-8 fade-in">
    
    <div class="text-center">
        <h1 class="text-3xl font-display mb-2" style="color: var(--dusty-blue)">Request Invitation</h1>
        <p class="text-gray-600">Please provide your details below.</p>
    </div>

    <?php if ($success): ?>
    <div class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded fade-in is-visible">
      <strong>Sent!</strong> We’ve received your information. Thank you!
    </div>
    <?php endif; ?>

    <?php if ($error): ?>
    <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded break-words fade-in is-visible">
      <strong>Error:</strong> <?= htmlspecialchars($errorMsg) ?>
    </div>
    <?php endif; ?>

    <form method="POST" class="card space-y-4">
      <div>
        <label class="block text-sm font-medium text-gray-700 mb-1">Full Name</label>
        <input name="name" required placeholder="Jane Doe" class="w-full rounded border-gray-200 p-2 focus:ring-[var(--sage-green)] focus:border-[var(--sage-green)]">
      </div>
      
      <div>
        <label class="block text-sm font-medium text-gray-700 mb-1">Email</label>
        <input name="email" type="email" required placeholder="jane@example.com" class="w-full rounded border-gray-200 p-2 focus:ring-[var(--sage-green)] focus:border-[var(--sage-green)]">
      </div>

      <div>
        <label class="block text-sm font-medium text-gray-700 mb-1">Mailing Address</label>
        <input name="address" placeholder="123 Wedding Lane" class="w-full rounded border-gray-200 p-2 focus:ring-[var(--sage-green)] focus:border-[var(--sage-green)]">
      </div>

      <div class="grid grid-cols-3 gap-3">
        <div>
           <label class="block text-sm font-medium text-gray-700 mb-1">City</label>
           <input name="city" placeholder="City" class="w-full rounded border-gray-200 p-2 focus:ring-[var(--sage-green)] focus:border-[var(--sage-green)]">
        </div>
        <div>
           <label class="block text-sm font-medium text-gray-700 mb-1">State</label>
           <input name="state" placeholder="State" class="w-full rounded border-gray-200 p-2 focus:ring-[var(--sage-green)] focus:border-[var(--sage-green)]">
        </div>
        <div>
           <label class="block text-sm font-medium text-gray-700 mb-1">Zip</label>
           <input name="zip" placeholder="Zip" class="w-full rounded border-gray-200 p-2 focus:ring-[var(--sage-green)] focus:border-[var(--sage-green)]">
        </div>
      </div>

      <div class="pt-2 text-center">
        <button class="btn-accent text-lg px-8">Submit Info</button>
      </div>
    </form>

  </main>

  <footer class="bg-white border-t mt-16 py-6 text-center text-sm text-gray-500">
    © 2026 Samuel & Kelcee Baer — All Rights Reserved
  </footer>

  <script>
    const btn = document.getElementById('menu-btn');
    const menu = document.getElementById('mobile-menu');
    const overlay = document.getElementById('menu-overlay');
    btn.addEventListener('click', () => {
      menu.classList.toggle('open');
      overlay.style.display = menu.classList.contains('open') ? 'block' : 'none';
    });
    overlay.addEventListener('click', () => {
      menu.classList.remove('open');
      overlay.style.display = 'none';
    });
    document.addEventListener('DOMContentLoaded', () => {
      document.querySelectorAll('.fade-in').forEach(el => el.classList.add('is-visible'));
    });
  </script>
</body>
</html>