<?php
// --- CONFIGURATION ---
// 1. Paste your Key here one last time
$my_key  = 'ezQFDCdIGw1gvKIlceXfnymph21fhb7gxP1EcsFqOCnzxkf9DtGp9yuKfvZfaZy3hKjSJhClPGSXACDbj1DlrQ=='; 

// 2. Settings
$my_host = 'https://kelceesam.documents.azure.com:443/'; 
$my_db   = 'WeddingDB';
$my_col  = 'Guests';

// --- COSMOS DB CLASS ---
class CosmosDB {
    private $host, $key, $db, $coll;
    public function __construct($host, $key, $db, $coll) {
        $this->host = $host;
        $this->key = $key;
        $this->db = $db;
        $this->coll = $coll;
    }
    
    private function request($verb, $rid, $rtype, $sql = null) {
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
            "Content-Type: application/query+json",
            "x-ms-documentdb-isquery: True" // Required for SQL queries
        ];

        $cleanHost = parse_url($this->host, PHP_URL_HOST);
        $url = "https://" . $cleanHost . "/dbs/{$this->db}/colls/{$this->coll}/$rtype";
        
        $ch = curl_init($url);
        curl_setopt($ch, CURLOPT_CUSTOMREQUEST, $verb);
        curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, true); 
        if ($sql) curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($sql));
        
        $res = curl_exec($ch);
        curl_close($ch);
        
        return json_decode($res, true);
    }
    
    public function getGuests() {
        // SQL Query to select everyone
        $sql = ["query" => "SELECT * FROM c"];
        $rid = "dbs/{$this->db}/colls/{$this->coll}";
        return $this->request('POST', $rid, 'docs', $sql);
    }
}

// Fetch the data
$cosmos = new CosmosDB($my_host, $my_key, $my_db, $my_col);
$response = $cosmos->getGuests();
$guests = $response['Documents'] ?? [];
?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="utf-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1" />
  <title>Guest List — Samuel & Kelcee</title>

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

    .card {
      background: white;
      border-radius: 1rem;
      border: 1px solid rgba(0,0,0,0.07);
      box-shadow: 0 6px 18px rgba(0,0,0,0.04);
      overflow: hidden;
    }

    .fade-in { opacity: 0; transform: translateY(10px); transition: opacity 0.6s ease, transform 0.6s ease; }
    .is-visible { opacity: 1; transform: none; }

    /* Mobile Menu */
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

  <main class="max-w-4xl mx-auto px-4 py-12 space-y-8 fade-in">
    
    <div class="flex justify-between items-end mb-6">
        <div>
            <h1 class="text-3xl font-display" style="color: var(--dusty-blue)">Guest List</h1>
            <p class="text-gray-600">Total RSVPs: <strong><?= count($guests) ?></strong></p>
        </div>
        <a href="upload_json.php" class="text-sm font-semibold text-[var(--sage-green)] hover:underline">Bulk Upload</a>
    </div>

    <div class="card">
        <div class="overflow-x-auto">
            <table class="w-full text-left">
                <thead class="bg-[var(--neutral)] border-b text-[var(--dusty-blue)] font-display uppercase text-xs tracking-wider">
                    <tr>
                        <th class="p-4 font-bold">Name</th>
                        <th class="p-4 font-bold">Email</th>
                        <th class="p-4 font-bold">Address</th>
                        <th class="p-4 font-bold">City/State</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100">
                    <?php if (empty($guests)): ?>
                        <tr>
                            <td colspan="4" class="p-8 text-center text-gray-500 italic">
                                No guests found. <a href="rsvp.php" class="text-blue-500 underline">Add one?</a>
                            </td>
                        </tr>
                    <?php else: ?>
                        <?php foreach ($guests as $g): ?>
                        <tr class="hover:bg-gray-50 transition">
                            <td class="p-4 font-semibold text-gray-800">
                                <?= htmlspecialchars($g['name'] ?? 'Unknown') ?>
                            </td>
                            <td class="p-4 text-gray-600 text-sm">
                                <?= htmlspecialchars($g['email'] ?? '-') ?>
                            </td>
                            <td class="p-4 text-gray-600 text-sm">
                                <?= htmlspecialchars($g['address'] ?? '-') ?>
                            </td>
                            <td class="p-4 text-gray-600 text-sm">
                                <?= htmlspecialchars($g['city'] ?? '') ?>, <?= htmlspecialchars($g['state'] ?? '') ?>
                                <span class="text-xs text-gray-400 block mt-1"><?= htmlspecialchars($g['zip'] ?? '') ?></span>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>

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