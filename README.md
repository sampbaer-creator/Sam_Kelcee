# Our Wedding - Azure Web App

Dynamic wedding website hosted on Microsoft Azure. The public pages are HTML/CSS, and the invitation request form uses PHP to save guest contact details to Azure Cosmos DB.

## Pages

- `index.html` - Home
- `event-details.html` - Ceremony and reception locations
- `registry.html` - Registry links
- `rsvp.php` - Invitation request form
- `guestlist.php` - Admin guest list view

## Azure App Settings

Do not commit Cosmos DB keys to the repository. Configure these values in the Azure Web App application settings:

- `COSMOS_DB_KEY`
- `COSMOS_DB_HOST`
- `COSMOS_DB_NAME`
- `COSMOS_DB_COLLECTION`

The current deployed app uses:

- Database: `WeddingDB`
- Collection: `Guests`

If a Cosmos DB key was ever committed, rotate that key in Azure before relying on the repository cleanup.

## Deployment

The GitHub Actions workflow deploys the repository root to the Azure Web App named `KelceeSam` when changes are pushed to `main`.

## Tech Stack

- Hosting: Azure Web App, PHP 8.2+
- Database: Azure Cosmos DB for NoSQL
- Frontend: HTML, Tailwind CDN, shared CSS in `css/styles.css`
