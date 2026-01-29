<?php
// --- CONFIGURATION ---
// Paste your Key here again
$my_key  = 'ezQFDCdIGw1gvKIlceXfnymph21fhb7gxP1EcsFqOCnzxkf9DtGp9yuKfvZfaZy3hKjSJhClPGSXACDbj1DlrQ=='; 

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
    private function request($verb, $rid, $rtype, $data = null, $pk = null) {
        $date = gmdate('D, d M Y H:i:s T');
        $keyDecoded = base64_decode($this->key);
        $sigString = strtolower($verb) . "\n" . strtolower($rtype) . "\n" . $rid . "\n" . strtolower($date) . "\n\n";
        $sig = base64_encode(hash_hmac('sha256', $sigString, $keyDecoded, true));
        $auth = urlencode("type=master&ver=1.0&sig=$sig");
        $headers = ["Authorization: $auth", "x-ms-date: $date", "x-ms-version: 2018-12-31", "Content-Type: application/json"];
        if ($pk !== null) $headers[] = "x-ms-documentdb-partitionkey: " . json_encode([$pk]);
        
        $url = "https://" . parse_url($this->host, PHP_URL_HOST) . "/dbs/{$this->db}/colls/{$this->coll}/$rtype";
        $ch = curl_init($url);
        curl_setopt($ch, CURLOPT_CUSTOMREQUEST, $verb);
        curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, true); 
        if ($data) curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));
        $res = curl_exec($ch);
        $code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);
        return $code;
    }
    public function createDocument($data) {
        if (!isset($data['id'])) $data['id'] = uniqid(); 
        $rid = "dbs/{$this->db}/colls/{$this->coll}";
        return $this->request('POST', $rid, 'docs', $data, $data['email'] ?? null);
    }
}

$msg = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_FILES['jsonFile'])) {
    $file = $_FILES['jsonFile']['tmp_name'];
    $content = file_get_contents($file);
    $data = json_decode($content, true);

    if (is_array($data)) {
        $cosmos = new CosmosDB($my_host, $my_key, $my_db, $my_col);
        $count = 0;
        
        // Handle both single object and array of objects
        if (isset($data['email'])) { $data = [$data]; } 

        foreach ($data as $guest) {
            // Add a timestamp if missing
            if (!isset($guest['date'])) $guest['date'] = date('Y-m-d H:i:s');
            
            $code = $cosmos->createDocument($guest);
            if ($code >= 200 && $code < 300) $count++;
        }
        $msg = "Success! Uploaded $count guests.";
    } else {
        $msg = "Error: Invalid JSON file.";
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="utf-8" />
<title>Bulk Upload Guests</title>
<script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-gray-50 p-10 font-sans">
    <div class="max-w-md mx-auto bg-white shadow-lg rounded-lg p-6">
        <h1 class="text-2xl font-bold text-blue-900 mb-4">Upload JSON Guest List</h1>
        
        <?php if ($msg): ?>
            <div class="bg-green-100 text-green-700 p-3 rounded mb-4"><?= htmlspecialchars($msg) ?></div>
        <?php endif; ?>

        <form method="POST" enctype="multipart/form-data" class="space-y-4">
            <div class="border-2 border-dashed border-gray-300 p-6 text-center rounded">
                <input type="file" name="jsonFile" accept=".json" required class="block w-full text-sm text-gray-500 file:mr-4 file:py-2 file:px-4 file:rounded-full file:border-0 file:text-sm file:font-semibold file:bg-blue-50 file:text-blue-700 hover:file:bg-blue-100"/>
            </div>
            <button class="w-full bg-blue-600 text-white rounded py-2 font-bold hover:bg-blue-700">Upload Guests</button>
        </form>
    </div>
</body>
</html>