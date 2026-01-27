Our Wedding Website — Azure PHP App

This is a PHP-based wedding website hosted on Azure App Service (Linux, PHP 8.2).
It uses Tailwind CSS via CDN for styling and a server-side PHP RSVP system that saves submissions to a CSV file and sends email notifications.

🌐 Live Site

URL:

https://kelceesam-agemdaagffethha2.westus3-01.azurewebsites.net

✨ Features

Responsive wedding website styled with Tailwind CSS

Pages

index.html — Home + countdown timer

rsvp.php — RSVP / address collection form

registry.html — Registry & Venmo links

event-details.html — Ceremony & reception details

RSVP form

Submits via PHP (save_invite.php)

Saves guest data to wedding_guest_list_2026.csv

Redirects back with a success message

Email notifications

Sends RSVP notifications to the couple

Sends a confirmation email to the guest (via PHP mail())

🛠 Tech Stack

Frontend: HTML, Tailwind CSS (CDN)

Backend: PHP 8.2

Hosting: Azure App Service (Linux)

Deployment: GitHub → Azure Deployment Center

Storage: CSV file in Azure wwwroot

📂 Project Structure
.
├── css/
│   └── styles.css
├── images/
│   ├── wedding_1.jpg
│   ├── wedding_2.jpg
│   ├── wedding_3.jpg
│   ├── wedding_4.jpg
│   └── wedding_5.jpg
├── index.html
├── rsvp.php
├── save_invite.php
├── registry.html
├── event-details.html
├── wedding_guest_list_2026.csv   (created automatically)
└── .htaccess

🚀 Deployment

This site is deployed using Azure App Service with GitHub integration.

Flow:

Code is pushed to GitHub

Azure automatically pulls the repo

PHP files are executed by Azure (not static hosting)

⚠️ GitHub Pages is disabled — it does not support PHP.

📝 Notes

File names are case-sensitive (Azure Linux).

Form actions use relative paths (required for Azure PHP).

CSV file access is restricted via .htaccess.

For production-grade email delivery, consider replacing PHP mail() with SendGrid (Azure-native).

🔧 Future Improvements (Optional)

Admin dashboard to view/download RSVPs

RSVP count display on homepage

SendGrid email integration

Authentication for admin pages

Database storage (Azure SQL or Table Storage)
