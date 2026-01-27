<?php
$success = false;
$error = false;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    $name    = trim($_POST['name'] ?? '');
    $email   = trim($_POST['email'] ?? '');
    $address = trim($_POST['address'] ?? '');
    $city    = trim($_POST['city'] ?? '');
    $state   = trim($_POST['state'] ?? '');
    $zip     = trim($_POST['zip'] ?? '');

    if ($name !== '' && filter_var($email, FILTER_VALIDATE_EMAIL)) {

        $timestamp = date('Y-m-d H:i:s');

        // ONE fixed CSV location
        $csvPath = '/home/site/wwwroot/wedding_guest_list_2026.csv';

        $fileExists = file_exists($csvPath);
        $file = fopen($csvPath, 'a');

        if ($file !== false) {

            // Add header only once
            if (!$fileExists) {
                fputcsv($file, [
                    'Date Submitted',
                    'Full Name',
                    'Email',
                    'Address',
                    'City',
                    'State',
                    'Zip'
                ]);
            }

            // ALWAYS append
            fputcsv($file, [
                $timestamp,
                $name,
                $email,
                $address,
                $city,
                $state,
                $zip
            ]);

            fclose($file);

            // Email BOTH of you
            $to = 'sampbaer@gmail.com, kelcee5young@gmail.com';
            $subject = "New Wedding RSVP — $name";
            $message =
                "New RSVP Submission\n\n" .
                "Name: $name\n" .
                "Email: $email\n" .
                "Address: $address\n" .
                "City: $city\n" .
                "State: $state\n" .
                "Zip: $zip\n\n" .
                "Submitted: $timestamp";

            $headers =
                "From: RSVP <no-reply@" . $_SERVER['HTTP_HOST'] . ">\r\n" .
                "Reply-To: $email\r\n";

            @mail($to, $subject, $message, $headers);

            $success = true;
        } else {
            $error = true;
        }
    } else {
        $error = true;
    }
}
?>


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
