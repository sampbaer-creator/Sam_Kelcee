<?php
function cosmos_config(): array {
    $config = [
        'key' => getenv('COSMOS_DB_KEY') ?: '',
        'host' => getenv('COSMOS_DB_HOST') ?: '',
        'db' => getenv('COSMOS_DB_NAME') ?: '',
        'coll' => getenv('COSMOS_DB_COLLECTION') ?: '',
    ];

    $missing = [];
    foreach ($config as $name => $value) {
        if ($value === '') {
            $missing[] = $name;
        }
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
        if ($data) {
            curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));
        }

        $res = curl_exec($ch);
        $code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        $curlError = curl_error($ch);
        curl_close($ch);

        if ($res === false) {
            return ['code' => 0, 'data' => ['message' => $curlError ?: 'Request failed']];
        }

        return ['code' => $code, 'data' => json_decode($res, true)];
    }

    public function createDocument($data) {
        if (!isset($data['id'])) {
            $data['id'] = uniqid();
        }
        $rid = "dbs/{$this->db}/colls/{$this->coll}";
        return $this->request('POST', $rid, 'docs', $data, $data['email'] ?? null);
    }
}

$success = false;
$error = false;
$errorMsg = '';
$form = [
    'name' => '',
    'email' => '',
    'address' => '',
    'city' => '',
    'state' => '',
    'zip' => '',
    'country' => '',
];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    foreach ($form as $field => $_) {
        $form[$field] = trim($_POST[$field] ?? '');
    }

    if ($form['name'] === '' || !filter_var($form['email'], FILTER_VALIDATE_EMAIL)) {
        $error = true;
        $errorMsg = 'Please enter a valid name and email.';
    } else {
        [$config, $missingConfig] = cosmos_config();

        if (!empty($missingConfig)) {
            $error = true;
            $errorMsg = 'The invitation form is missing server configuration. Please set the Azure Cosmos DB app settings.';
        } else {
            $cosmos = new CosmosDB($config['host'], $config['key'], $config['db'], $config['coll']);
            $guestData = [
                'name' => $form['name'],
                'email' => $form['email'],
                'address' => $form['address'],
                'city' => $form['city'],
                'state' => $form['state'],
                'zip' => $form['zip'],
                'country' => $form['country'],
                'date' => date('Y-m-d H:i:s')
            ];

            $result = $cosmos->createDocument($guestData);

            if ($result['code'] >= 200 && $result['code'] < 300) {
                $success = true;
                foreach ($form as $field => $_) {
                    $form[$field] = '';
                }
            } else {
                $error = true;
                $errorMsg = 'Error ' . $result['code'] . ': ' . ($result['data']['message'] ?? 'Unknown Error');
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
  <title>Request Invitation - Samuel &amp; Kelcee</title>
  <script src="https://cdn.tailwindcss.com?plugins=forms,typography,aspect-ratio"></script>
  <link rel="preconnect" href="https://fonts.googleapis.com">
  <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
  <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;600;700;800;900&family=Playfair+Display:wght@700&display=swap" rel="stylesheet">
  <link rel="stylesheet" href="css/styles.css">
</head>
<body>
  <header class="site-header">
    <div class="site-nav">
      <a href="index.html" class="brand">Samuel &amp; Kelcee</a>
      <nav class="nav-links" aria-label="Primary navigation">
        <a href="index.html">Home</a>
        <a href="event-details.html">Event Details</a>
        <a href="registry.html">Registry</a>
        <a href="rsvp.php" class="active">Request Invitation</a>
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
      <p class="eyebrow">Invitation</p>
      <h1 class="font-display">Send us your details.</h1>
      <p class="lede max-w-3xl mt-5">
        Share your name, email, and mailing address so we can keep invitation details organized.
      </p>
    </section>

    <section class="form-shell">
      <aside class="card soft fade-in">
        <h2 class="font-display text-3xl">What this is for</h2>
        <p class="mt-3">This form collects invitation contact information. We will use it only for wedding updates and mailing details.</p>
        <p class="mt-5 text-sm text-[var(--muted)]">Fields marked with an asterisk are required.</p>
      </aside>

      <div class="fade-in">
        <?php if ($success): ?>
          <div class="notice success mb-4">
            Sent. We received your information. Thank you.
          </div>
        <?php endif; ?>

        <?php if ($error): ?>
          <div class="notice error mb-4">
            <?= h($errorMsg) ?>
          </div>
        <?php endif; ?>

        <form method="POST" class="form-card field-group" novalidate>
          <div>
            <label for="name">Full name *</label>
            <input id="name" name="name" required autocomplete="name" placeholder="Jane Doe" value="<?= h($form['name']) ?>">
          </div>

          <div>
            <label for="email">Email *</label>
            <input id="email" name="email" type="email" required autocomplete="email" placeholder="jane@example.com" value="<?= h($form['email']) ?>">
          </div>

          <div>
            <label for="address">Mailing address</label>
            <input id="address" name="address" autocomplete="street-address" placeholder="123 Wedding Lane" value="<?= h($form['address']) ?>">
          </div>

          <div class="field-grid">
            <div>
              <label for="city">City</label>
              <input id="city" name="city" autocomplete="address-level2" placeholder="City" value="<?= h($form['city']) ?>">
            </div>
            <div>
              <label for="state">State / region</label>
              <input id="state" name="state" autocomplete="address-level1" placeholder="State or province" value="<?= h($form['state']) ?>">
            </div>
          </div>

          <div class="field-grid">
            <div>
              <label for="zip">Zip / postal code</label>
              <input id="zip" name="zip" autocomplete="postal-code" placeholder="Zip or postal code" value="<?= h($form['zip']) ?>">
            </div>
            <div>
              <label for="country">Country</label>
              <input id="country" name="country" autocomplete="country-name" placeholder="Country" value="<?= h($form['country']) ?>">
            </div>
          </div>

          <button class="btn-primary border-0 cursor-pointer mt-2" type="submit">Submit Information</button>
        </form>
      </div>
    </section>
  </main>

  <footer class="site-footer">
    &copy; 2026 Samuel &amp; Kelcee Baer. All rights reserved.
  </footer>

  <script src="js/site.js"></script>
</body>
</html>
