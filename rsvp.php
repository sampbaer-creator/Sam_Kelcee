<?php
// --- CONFIGURATION ---
// 1. Paste your Primary Key between the quotes below
$my_key  = 'ezQFDCdIGw1gvKIlceXfnymph21fhb7gxP1EcsFqOCnzxkf9DtGp9yuKfvZfaZy3hKjSJhClPGSXACDbj1DlrQ=='; 

// 2. We set these for you based on your screenshot
$my_host = 'https://kelceesam.documents.azure.com:443/'; 
$my_db   = 'WeddingDB';
$my_col  = 'Guests';
// ---------------------

class CosmosDB {
    private $host, $key, $db, $coll;
    public function __construct($host, $key, $db, $coll) {
        $this->host = $host;
        $this->key = $key;
        $this->db = $db;
        $this->coll = $coll;
    }
    
    // Updated request function to accept Partition Key ($pk)
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

        // --- THE FIX: ADD PARTITION KEY HEADER ---
        if ($pk !== null) {
            // Partition keys must be sent as a JSON array ["email@test.com"]
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
        
        // Pass the 'email' as the partition key because you set /email in Azure
        $partitionKey = $data['email'] ?? null;
        
        return $this->request('POST', $rid, 'docs', $data, $partitionKey);
    }
}

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
            if ($result['code'] == 400) $errorMsg .= ' (Partition Key Mismatch - Ensure email is valid)';
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="utf-8" /><meta name="viewport" content="width=device-width, initial-scale=1" />
<title>RSVP — Samuel & Kelcee</title>
<script src="https://cdn.tailwindcss.com?plugins=forms,typography"></script>
</head>
<body class="bg-gray-50 text-gray-800 font-sans">
<header class="bg-white/80 sticky top-0 z-50 shadow-sm backdrop-blur">
  <div class="max-w-5xl mx-auto px-6 py-4 flex justify-between items-center">
    <a href="index.html" class="text-2xl font-bold text-blue-900">Samuel & Kelcee</a>
    <nav class="hidden md:flex space-x-4 text-sm font-semibold">
      <a href="index.html">Home</a>
      <a href="rsvp.php" class="text-blue-600">Address</a>
      <a href="registry.html">Registry</a>
    </nav>
  </div>
</header>

<main class="max-w-2xl mx-auto px-4 py-12 space-y-8">
<h1 class="text-3xl font-bold text-blue-900">Request Invitation</h1>

<?php if ($success): ?>
<div class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded">
<strong>Sent!</strong> We’ve received your information. Thank you!
</div>
<?php endif; ?>

<?php if ($error): ?>
<div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded break-words">
<strong>Error:</strong> <?= htmlspecialchars($errorMsg) ?>
</div>
<?php endif; ?>

<form method="POST" class="bg-white p-6 rounded shadow space-y-4">
  <input name="name" required placeholder="Full Name" class="w-full rounded border-gray-200 p-2">
  <input name="email" type="email" required placeholder="Email" class="w-full rounded border-gray-200 p-2">
  <input name="address" placeholder="Address" class="w-full rounded border-gray-200 p-2">
  <div class="grid grid-cols-3 gap-3">
    <input name="city" placeholder="City" class="rounded border-gray-200 p-2">
    <input name="state" placeholder="State" class="rounded border-gray-200 p-2">
    <input name="zip" placeholder="Zip" class="rounded border-gray-200 p-2">
  </div>
  <button class="bg-blue-600 text-white rounded-full px-6 py-2 hover:bg-blue-700">Submit Info</button>
</form>
</main>
</body>
</html>