<?php
function cosmos_config(): array {
    $config = [
        'key' => getenv('COSMOS_DB_KEY') ?: '',
        'host' => getenv('COSMOS_DB_HOST') ?: 'https://kelceesam.documents.azure.com:443/',
        'db' => getenv('COSMOS_DB_NAME') ?: 'WeddingDB',
        'coll' => getenv('COSMOS_DB_COLLECTION') ?: 'Guests',
    ];

    $missing = [];
    if ($config['key'] === '') {
        $missing[] = 'COSMOS_DB_KEY';
    }

    return [$config, $missing];
}

function h($value): string {
    return htmlspecialchars((string) $value, ENT_QUOTES, 'UTF-8');
}

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
            "x-ms-documentdb-isquery: True",
            "x-ms-documentdb-query-enablecrosspartition: True"
        ];

        $cleanHost = parse_url($this->host, PHP_URL_HOST);
        $url = "https://" . $cleanHost . "/dbs/{$this->db}/colls/{$this->coll}/$rtype";

        $ch = curl_init($url);
        curl_setopt($ch, CURLOPT_CUSTOMREQUEST, $verb);
        curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, true);
        if ($sql) {
            curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($sql));
        }

        $res = curl_exec($ch);
        $curlError = curl_error($ch);
        curl_close($ch);

        if ($res === false) {
            return ['error' => $curlError ?: 'Request failed'];
        }

        return json_decode($res, true) ?: [];
    }

    public function getGuests() {
        $sql = ["query" => "SELECT * FROM c"];
        $rid = "dbs/{$this->db}/colls/{$this->coll}";
        return $this->request('POST', $rid, 'docs', $sql);
    }
}

$guests = [];
$configError = '';
[$config, $missingConfig] = cosmos_config();

if (!empty($missingConfig)) {
    $configError = 'Guest list is waiting for the Cosmos DB key to be configured in Azure App Settings.';
} else {
    $cosmos = new CosmosDB($config['host'], $config['key'], $config['db'], $config['coll']);
    $response = $cosmos->getGuests();

    if (isset($response['error'])) {
        $configError = $response['error'];
    } else {
        $guests = $response['Documents'] ?? [];
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="utf-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1" />
  <title>Guest List - Samuel &amp; Kelcee</title>
  <script src="https://cdn.tailwindcss.com?plugins=forms,typography,aspect-ratio"></script>
  <link rel="preconnect" href="https://fonts.googleapis.com">
  <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
  <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;600;700;800;900&family=Playfair+Display:wght@700&display=swap" rel="stylesheet">
  <link rel="stylesheet" href="css/styles.css?v=clock1">
</head>
<body>
  <header class="site-header">
    <div class="site-nav">
      <a href="index.html" class="brand">Samuel &amp; Kelcee</a>
      <nav class="nav-links" aria-label="Primary navigation">
        <a href="index.html">Home</a>
        <a href="event-details.html">Event Details</a>
        <a href="registry.html">Registry</a>
        <a href="rsvp.php">Request Invitation</a>
      </nav>
      <button id="menu-btn" class="menu-button" type="button" aria-label="Open menu" aria-controls="mobile-menu" aria-expanded="false">
        <span class="menu-icon" aria-hidden="true"></span>
      </button>
    </div>
  </header>

  <div id="menu-overlay" class="menu-overlay"></div>
  <nav id="mobile-menu" class="mobile-menu" aria-label="Mobile navigation">
    <a href="index.html">Home</a>
    <a href="event-details.html">Event Details</a>
    <a href="registry.html">Registry</a>
    <a href="rsvp.php">Request Invitation</a>
  </nav>

  <main class="page-shell">
    <section class="page-hero fade-in">
      <p class="eyebrow">Admin</p>
      <h1 class="font-display">Guest list</h1>
      <p class="lede mt-5">Total invitation requests: <strong><?= count($guests) ?></strong></p>
    </section>

    <?php if ($configError !== ''): ?>
      <div class="notice error mb-6 fade-in"><?= h($configError) ?></div>
    <?php endif; ?>

    <section class="table-card fade-in mb-20">
      <div class="overflow-x-auto">
        <table class="w-full text-left">
          <thead class="border-b bg-[#f5f1ea] text-xs font-bold uppercase tracking-wider text-[var(--muted)]">
            <tr>
              <th class="p-4">Name</th>
              <th class="p-4">Email</th>
              <th class="p-4">Address</th>
              <th class="p-4">City / State</th>
              <th class="p-4">Country</th>
            </tr>
          </thead>
          <tbody class="divide-y divide-gray-100">
            <?php if (empty($guests)): ?>
              <tr>
                <td colspan="5" class="p-8 text-center text-gray-500">
                  No guests found.
                </td>
              </tr>
            <?php else: ?>
              <?php foreach ($guests as $g): ?>
                <tr class="transition hover:bg-gray-50">
                  <td class="p-4 font-semibold text-gray-900"><?= h($g['name'] ?? 'Unknown') ?></td>
                  <td class="p-4 text-sm text-gray-600"><?= h($g['email'] ?? '-') ?></td>
                  <td class="p-4 text-sm text-gray-600"><?= h($g['address'] ?? '-') ?></td>
                  <td class="p-4 text-sm text-gray-600">
                    <?= h($g['city'] ?? '') ?><?= !empty($g['city']) && !empty($g['state']) ? ', ' : '' ?><?= h($g['state'] ?? '') ?>
                    <span class="mt-1 block text-xs text-gray-400"><?= h($g['zip'] ?? '') ?></span>
                  </td>
                  <td class="p-4 text-sm font-semibold text-gray-700"><?= h($g['country'] ?? '') ?></td>
                </tr>
              <?php endforeach; ?>
            <?php endif; ?>
          </tbody>
        </table>
      </div>
    </section>
  </main>

  <footer class="site-footer">
    &copy; 2026 Samuel &amp; Kelcee Baer. All rights reserved.
  </footer>

  <script src="js/site.js?v=clock1"></script>
</body>
</html>
