Our Wedding — Azure Web App
This is a dynamic wedding website hosted on Azure. It uses PHP to save RSVPs directly to an Azure Cosmos DB database.

Getting Started
Configure Keys: Open rsvp.php and guestlist.php and paste your Azure Cosmos DB Primary Key at the top.

Deploy: Push this folder to your GitHub repository connected to your Azure Web App.

Features
Pages: index.html, rsvp.php (form), guestlist.php (admin view), registry.html.

Database: RSVPs are saved instantly to Azure Cosmos DB (WeddingDB).

Admin: guestlist.php shows a live table of everyone who has responded.

Countdown: Live countdown timer on the home page.

Deploy
This project is designed to run on an Azure Web App (Linux/PHP).

It deploys automatically via GitHub Actions when you push to your repository.

Notes
Database Setup: Requires an Azure Cosmos DB with database WeddingDB and container Guests.

Partition Key: The container must use /email as the partition key.

Security: The guestlist.php page is public; for a real event, consider adding a password check.