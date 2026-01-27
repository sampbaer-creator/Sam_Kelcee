<?php
$success = false;
$error = false;
$errorMsg = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    $name    = trim($_POST['name'] ?? '');
    $email   = trim($_POST['email'] ?? '');
    $address = trim($_POST['address'] ?? '');
    $city    = trim($_POST['city'] ?? '');
    $state   = trim($_POST['state'] ?? '');
    $zip     = trim($_POST['zip'] ?? '');

    if ($name === '' || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $error = true;
        $errorMsg = 'Invalid name or email.';
    } else {

        $conn = getenv('AZURE_STORAGE_CONNECTION_STRING');
        if (!$conn) {
            $error = true;
            $errorMsg = 'Storage connection not found.';
        } else {

            // Parse connection string
            preg_match('/AccountName=([^;]+)/', $conn, $m1);
            preg_match('/AccountKey=([^;]+)/', $conn, $m2);

            $account = $m1[1] ?? null;
            $key     = $m2[1] ?? null;

            if (!$account || !$key) {
                $error = true;
                $errorMsg = 'Invalid storage credentials.';
            } else {

                $container = 'rsvp';
                $blob      = 'wedding_guest_list_2026.csv';
                $timestamp = date('Y-m-d H:i:s');

                $row = [
                    $timestamp,
                    $name,
                    $email,
                    $address,
                    $city,
                    $state,
                    $zip
                ];

                // Build CSV line
                $csvLine = '"' . implode('","', array_map('addslashes', $row)) . '"' . "\n";

                // Blob URL
                $url = "https://$account.blob.core.windows.net/$container/$blob";

                // Check if blob exists
                $ch = curl_init($url);
                curl_setopt_array($ch, [
                    CURLOPT_NOBODY => true,
                    CURLOPT_RETURNTRANSFER => true
                ]);
                curl_exec($ch);
                $exists = curl_getinfo($ch, CURLINFO_HTTP_CODE) === 200;
                curl_close($ch);

                // If exists, download it first
                $existing = '';
                if ($exists) {
                    $existing = file_get_contents($url);
                } else {
                    $existing = "\"Date Submitted\",\"Full Name\",\"Email\",\"Address\",\"City\",\"State\",\"Zip\"\n";
                }

                $data = $existing . $csvLine;

                // Build auth header
                $date = gmdate('D, d M Y H:i:s T');
                $len  = strlen($data);

                $stringToSign =
                    "PUT\n\n\n$len\n\ntext/csv\n\n\n\n\n\n\nx-ms-blob-type:BlockBlob\nx-ms-date:$date\nx-ms-version:2020-10-02\n/$account/$container/$blob";

                $sig = base64_encode(
                    hash_hmac('sha256', $stringToSign, base64_decode($key), true)
                );

                $headers = [
                    "Authorization: SharedKey $account:$sig",
                    "x-ms-blob-type: BlockBlob",
                    "x-ms-date: $date",
                    "x-ms-version: 2020-10-02",
                    "Content-Type: text/csv",
                    "Content-Length: $len"
                ];

                $ch = curl_init($url);
                curl_setopt_array($ch, [
                    CURLOPT_CUSTOMREQUEST => 'PUT',
                    CURLOPT_HTTPHEADER => $headers,
                    CURLOPT_POSTFIELDS => $data,
                    CURLOPT_RETURNTRANSFER => true
                ]);

                curl_exec($ch);
                $code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
                curl_close($ch);

                if ($code === 201) {
                    $success = true;
                } else {
                    $error = true;
                    $errorMsg = "Blob upload failed ($code)";
                }
            }
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<title>RSVP</title>
<script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-gray-50">

<main class="max-w-xl mx-auto p-6 space-y-4">
<h1 class="text-3xl font-bold text-blue-600">Request Invitation</h1>

<?php if ($success): ?>
<div class="bg-green-100 border border-green-400 text-green-700 p-3 rounded">
✅ RSVP saved successfully!
</div>
<?php endif; ?>

<?php if ($error): ?>
<div class="bg-red-100 border border-red-400 text-red-700 p-3 rounded">
❌ <?= htmlspecialchars($errorMsg) ?>
</div>
<?php endif; ?>

<form method="POST" class="bg-white p-4 rounded shadow space-y-2">
<input name="name" required placeholder="Full name" class="w-full border p-2 rounded">
<input name="email" required type="email" placeholder="Email" class="w-full border p-2 rounded">
<input name="address" placeholder="Address" class="w-full border p-2 rounded">
<input name="city" placeholder="City" class="w-full border p-2 rounded">
<input name="state" placeholder="State" class="w-full border p-2 rounded">
<input name="zip" placeholder="Zip" class="w-full border p-2 rounded">
<button class="bg-blue-600 text-white px-4 py-2 rounded">Submit</button>
</form>
</main>

</body>
</html>
