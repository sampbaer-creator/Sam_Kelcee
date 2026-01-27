<?php
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $name    = htmlspecialchars($_POST['name']);
    $email   = htmlspecialchars($_POST['email']);
    $address = htmlspecialchars($_POST['address']);
    $city    = htmlspecialchars($_POST['city']);
    $state   = htmlspecialchars($_POST['state']);
    $zip     = htmlspecialchars($_POST['zip']);
    $timestamp = date('Y-m-d H:i:s');

    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        die("Invalid email address.");
    }

    // Use writable folder on Azure
    $filename = $_SERVER['HOME'] . "/site/wwwroot/wedding_guest_list_2026.csv";

    $file_exists = file_exists($filename);
    $file = fopen($filename, "a");
    if (!$file_exists) {
        fputcsv($file, ['Date Submitted', 'Full Name', 'Email', 'Address', 'City', 'State', 'Zip']);
    }
    fputcsv($file, [$timestamp, $name, $email, $address, $city, $state, $zip]);
    fclose($file);

    // Send notification email
    $to = "sampbaer@gmail.com, kelcee5young@gmail.com";
    $subject = "Wedding Invite Request: $name";
    $message = "New invitation request:\n\n"
             . "Name: $name\nEmail: $email\nAddress: $address\nCity: $city\nState: $state\nZip: $zip\n\nTimestamp: $timestamp";
    $headers = "From: updates@kelceesam-agemdaagffethha2.westus3-01.azurewebsites.net\r\n";
    $headers .= "Reply-To: $email\r\n";
    $headers .= "X-Mailer: PHP/" . phpversion();
    mail($to, $subject, $message, $headers);

    // Optional confirmation to guest
    mail($email, "We've received your RSVP request!", 
        "Hi $name,\n\nThank you for requesting an invitation! We'll be in touch soon.\n\n– Samuel & Kelcee",
        "From: updates@kelceesam-agemdaagffethha2.westus3-01.azurewebsites.net");

  // redirect back to form with success flag
header("Location: /rsvp.php?status=success");
exit();

}
?>
