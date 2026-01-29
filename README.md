# Our Wedding — Azure Web App

This is a dynamic wedding website hosted on **Microsoft Azure**. It uses **PHP** to save RSVP data instantly to an **Azure Cosmos DB** database.

### 🚀 Getting Started

1.  **Configure Keys:**
    Open `rsvp.php` and `guestlist.php` and paste your **Azure Cosmos DB Primary Key** at the very top where it says `$my_key`.

2.  **Deploy:**
    Push this entire folder to your **GitHub repository** connected to your Azure Web App.

---

### ✨ Features

* **Pages:** `index.html` (Home), `rsvp.php` (Form), `guestlist.php` (Admin), `registry.html`.
* **Database:** RSVPs are saved instantly to **Azure Cosmos DB** (`WeddingDB`).
* **Admin View:** `guestlist.php` displays a **live table** of everyone who has responded, including their address and country.
* **Countdown:** A live JavaScript countdown timer on the home page.

---

### ⚙️ Tech Stack & Requirements

* **Hosting:** Azure Web App (Linux / PHP 8.2+)
* **Database:** Azure Cosmos DB (NoSQL)
* **Language:** PHP (Backend), HTML/Tailwind CSS (Frontend)

**Database Configuration:**
* **Database ID:** `WeddingDB`
* **Container ID:** `Guests`
* **Partition Key:** `/email`

---

### ⚠️ Important Notes

* **Security:** The `guestlist.php` page is currently **public**. For a real event, you should add password protection or delete the file after the project is graded.
* **Cross-Partition Queries:** The guest list page is specially configured to search **all email partitions** to show the full list of guests.