# Our Wedding - Azure Web App

Dynamic wedding website hosted on Microsoft Azure. The public pages are HTML/CSS, and the invitation request form uses PHP to save guest contact details to MySQL.

## Pages

- `index.html` - Home
- `event-details.html` - Ceremony and reception locations
- `registry.html` - Registry links
- `rsvp.php` - Invitation request form
- `guestlist.php` - Admin guest list view

## MySQL Settings

Do not commit database passwords to the repository. Configure these values locally or in the Azure Web App application settings:

- `MYSQL_HOST`
- `MYSQL_PORT`
- `MYSQL_DATABASE`
- `MYSQL_USER`
- `MYSQL_PASSWORD`
- `MYSQL_SSL_CA` optional path to a CA certificate if your Azure MySQL server requires SSL

Default local database settings:

- Host: `127.0.0.1`
- Port: `3306`
- Database: `sam_kelcee`
- User: `root`
- Password: empty string

Create the local database and table with:

```sql
SOURCE schema.sql;
```

For Azure Database for MySQL Flexible Server, use the server hostname for `MYSQL_HOST`, usually ending in `.mysql.database.azure.com`. If your MySQL username is not the Microsoft Entra admin account, Azure often expects `MYSQL_USER` in the form `username` or `username@server-name` depending on how the server was created.

## Deployment

The GitHub Actions workflow deploys the repository root to the Azure Web App named `KelceeSam` when changes are pushed to `main`.

## Azure MySQL Migration Checklist

1. Create an Azure Database for MySQL Flexible Server.
2. Create the `sam_kelcee` database and run `schema.sql`.
3. In the Azure Portal, open App Services > `KelceeSam` > Environment variables.
4. Add `MYSQL_HOST`, `MYSQL_PORT`, `MYSQL_DATABASE`, `MYSQL_USER`, and `MYSQL_PASSWORD`.
5. Save the settings and restart the App Service.
6. Push these code changes to `main` so GitHub Actions deploys the MySQL version.
7. After confirming `rsvp.php` works, remove unused Cosmos DB app settings from `KelceeSam`.

## Tech Stack

- Hosting: Azure Web App, PHP 8.2+
- Database: MySQL
- Frontend: HTML, Tailwind CDN, shared CSS in `css/styles.css`
