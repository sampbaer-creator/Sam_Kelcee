<?php
// --- HARDCODED CREDENTIALS (THE GUARANTEED FIX) ---
// Go to Azure Cosmos DB -> Keys -> Copy URI and PRIMARY KEY
$my_host = 'https://kelceesam.documents.azure.com:443/'; 
$my_key  = 'ezQFDCdIGw1gvKIlceXfnymph21fhb7gxP1EcsFqOCnzxkf9DtGp9yuKfvZfaZy3hKjSJhClPGSXACDbj1DlrQ==';
$my_db   = 'WeddingDB';
$my_col  = 'Guests';
// --------------------------------------------------

class CosmosDB {
    private $host, $key, $db, $coll;
    public function __construct($host, $key, $db, $coll) {
        $this->host = $host;
        $this->key = $key;
        $this->db = $db;
        $this->coll = $coll;
    }
    private function request($verb, $rid, $rtype, $data = null) {
        $date = gmdate('D, d M Y H:i:s T');
        $keyDecoded = base64_decode($this->key);
        $sigString = strtolower("$verb\n$rtype\n$rid\n$date\n\n");
        $sig = base64_encode(hash_hmac('sha256', $sigString, $keyDecoded, true));
        $auth = urlencode("type=master&ver=1.0&sig=$sig");
        $headers = ["Authorization: $auth", "x-ms-date: $date", "x-ms-version: 2018-12-31", "Content-Type: application/json"];
        $url = "https://" . parse_url($this->host, PHP_URL_HOST) . "/dbs/{$this->db}/colls/{$this->coll}/$rtype";
        $ch = curl_init($url);
        curl_setopt($ch, CURLOPT_CUSTOMREQUEST, $verb);
        curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        if ($data) curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));
        $res = curl_exec($ch);
        $code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);
        return ['code' => $code, 'data' => json_decode($res, true)];
    }
    public function createDocument($data) {
        if (!isset($data['id'])) $data['id'] = uniqid(); 
        return $this->request('POST', "dbs/{$this->db}/colls/{$this->coll}", 'docs', $data);
    }
}

$success = false; $error = false; $errorMsg = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = trim($_POST['name'] ?? '');
    $email = trim($_POST['email'] ?? '');
    // Simple validation
    if ($name === '' || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $error = true; $errorMsg = 'Please enter a valid name and email.';
    } else {
        // CONNECT
        $cosmos = new CosmosDB($my_host, $my_key, $my_db, $my_col);
        $guestData = [
            'name' => $name, 'email' => $email,
            'address' => $_POST['address'] ?? '', 'city' => $_POST['city'] ?? '',
            'state' => $_POST['state'] ?? '', 'zip' => $_POST['zip'] ?? '',
            'date' => date('Y-m-d H:i:s')
        ];
        
        $result = $cosmos->createDocument($guestData);
        
        if ($result['code'] >= 200 && $result['code'] < 300) {
            $success = true;
        } else {
            $error = true;
            // SHOW THE EXACT ERROR CODE
            $errorMsg = 'Error Code: ' . $result['code'];
            if ($result['code'] == 404) $errorMsg .= ' (Database not found - Did you create it?)';
            if ($result['code'] == 401) $errorMsg .= ' (Keys are wrong)';
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
<main class="max-w-2xl mx-auto px-4 py-12 space-y-8">
<h1 class="text-3xl font-bold text-blue-900">Request Invitation</h1>

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