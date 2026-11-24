# 🌍 Hidden Places Explorer

A web application designed for discovering, sharing, and cataloging Earth's most breathtaking off-the-beaten-path travel destinations—from secluded subterranean grottos to ancient dragon-blood plateaus.

Built with **HTML5, CSS3, Vanilla JavaScript, PHP 8+ (PDO), and MySQL**.

---

## 🎨 Theme & Design Philosophy
- **Colors**: Deep Forest Green (`#0f2d20`, `#1b4332`), Emerald Fern (`#2d6a4f`), Warm Parchment Cream (`#fcfbf7`, `#f4f0e6`), Pure White (`#ffffff`), and Antiqued Brass/Gold (`#d4a373`, `#e9c46a`).
- **Typography**: *Plus Jakarta Sans* for clean, modern interfaces paired with *Playfair Display* for editorial travel headings.
- **Interactions**: Interactive star picker, AJAX-powered bookmarking/favorites with dynamic toast notifications, responsive mobile drawer navigation, live filter updates, and Google Maps embeds.

---

## 🚀 Key Features

### 1. Public Explorer Experience
- **Home Sanctuary (`index.php`)**:
  - Full-bleed hero banner with quick keyword, biome category, and difficulty search.
  - Featured Secret Sanctuaries with live rating stars, difficulty badges, and AJAX favorite hearts.
  - Interactive biome category grid with mapped place counters.
  - The Explorer's Creed (conservation & leave-no-trace philosophy).
  - Recent field reviews stream from verified travelers.
- **Explore & Filter (`destinations.php`)**:
  - Multi-criteria filter sidebar: Keyword search, Biome Category radio filters, Terrain Difficulty filters (Easy, Moderate, Challenging, Expert), and Sorting (Newly Discovered, Highest Rated, Most Viewed, Title A-Z).
- **Destination Details (`destination.php`)**:
  - Full-screen panoramic hero with location pill, ratings, and instant bookmarking.
  - Spec metrics bar (Terrain Difficulty, Prime Season, Duration, Biome Realm).
  - Comprehensive narrative background, route guidance, and safety/ecological tips.
  - Interactive Google Maps embed with "Open in Google Maps" directions link.
  - Reviews & ratings section with rating breakdown, traveler reviews, and an interactive review submission form.
- **Biomes & Categories (`categories.php`)**:
  - Overview of all 6 curated geographic biomes with place counts and descriptions.
- **Contact Expedition HQ (`contact.php`)**:
  - Direct inquiry and new place suggestion form storing messages in the database with validation and CSRF protection.

### 2. User Authentication & Profile
- **Registration (`register.php`)**: Full validation, email uniqueness check, and secure password hashing (`password_hash` with `PASSWORD_BCRYPT`).
- **Authentication (`login.php`)**: Secure authentication via `password_verify`, session fixation prevention with `session_regenerate_id`, and intelligent post-login redirect.
- **Saved Places (`favorites.php`)**: Personal user wishlist with AJAX removal and responsive gem cards.
- **Explorer Profile (`profile.php`)**: Update name and bio, securely change password with verification of existing password, and browse personal review dispatches.

### 3. Administrator Control Room (`/admin/`)
- **Admin Authentication (`admin/index.php`)**: Separate secure gateway with session authentication.
- **Overview Dashboard (`admin/dashboard.php`)**: 4 real-time KPI metrics cards, quick shortcut action buttons, latest field reviews, and recent contact inquiries.
- **Destination CRUD (`admin/destinations.php`, `admin/destination-add.php`, `admin/destination-edit.php`, `admin/destination-delete.php`)**:
  - Add and edit destinations with live primary image preview, category dropdown, coordinate fields, travel tips, and featured showcase toggle.
  - Safe deletion with foreign key cascading.
- **Category Management (`admin/categories.php`)**: Create new biomes, assign Font Awesome icons, and review assigned destinations.
- **Explorer / User Moderation (`admin/users.php`)**: Manage registered travelers, inspect review and favorite counts, toggle active/inactive status, and delete accounts.
- **Review Moderation (`admin/reviews.php`)**: Moderate traveler field notes, toggle approved/pending statuses, and delete inappropriate content.
- **Inquiries Inbox (`admin/contacts.php`)**: Read traveler messages, mark as read/replied, and reply via mail client.

---

## 🗄️ Database Architecture (`hidden_places_explorer`)

| Table | Purpose | Relationships |
|---|---|---|
| `users` | Registered traveler accounts | Parent to `favourites` and `reviews` |
| `admins` | Administrative staff credentials | Independent authentication |
| `categories` | Geographic biomes & classifications | One-to-Many with `destinations` |
| `destinations` | Secret travel places & metadata | Foreign key to `categories`, Parent to `favourites` & `reviews` |
| `favourites` | User saved/bookmarked places | Many-to-Many junction (`user_id` + `destination_id`) |
| `reviews` | Traveler ratings (1-5) and field notes | Foreign keys to `destinations` and `users` |
| `contacts` | Inquiries & destination suggestions | Standalone form submissions |

*All relationships use `ON DELETE CASCADE` where appropriate to maintain referential integrity.*

---

## 📁 Folder Structure

```
hidden_places_explorer/
│
├── config/
│   ├── database.php          # PDO connection, credentials, auto-fallback notice
│   └── session.php           # Auth helpers, CSRF tokens, sanitization, flash alerts
│
├── includes/
│   ├── header.php            # Public navigation bar, user dropdown, flash alerts
│   ├── footer.php            # Footer columns, quick links, scripts
│   ├── admin_header.php      # Admin sidebar, topbar, badge notifications
│   └── admin_footer.php      # Admin footer & scripts
│
├── assets/
│   ├── css/
│   │   ├── style.css         # Public design system (Dark green, cream, white, gold)
│   │   └── admin.css         # Admin dashboard tables, sidebar, cards
│   └── js/
│       ├── main.js           # AJAX favorites, star picker, toasts, mobile menu
│       └── admin.js          # Admin search filter, image preview, confirmation modal
│
├── ajax/
│   └── toggle_favorite.php   # AJAX endpoint for adding/removing favorites
│
├── admin/
│   ├── index.php             # Admin login gate
│   ├── dashboard.php         # Admin metrics & recent activity
│   ├── destinations.php      # Destination management table
│   ├── destination-add.php   # Create destination form
│   ├── destination-edit.php  # Edit destination form
│   ├── destination-delete.php# Safe delete handler
│   ├── categories.php        # Category management
│   ├── users.php             # Explorer / user management
│   ├── reviews.php           # Field review moderation
│   ├── contacts.php          # Contact messages inbox
│   └── logout.php            # Admin session destroy
│
├── categories.php            # Public biomes catalog
├── contact.php               # Public contact & suggestion form
├── destination.php           # Destination details, maps, reviews
├── destinations.php          # Explore & multi-filter search
├── favorites.php             # User's saved destinations
├── index.php                 # Home page
├── login.php                 # User login
├── logout.php                # User logout
├── profile.php               # User profile & review history
├── register.php              # User registration
├── database.sql              # Complete database schema & seed data
└── README.md                 # Complete documentation & XAMPP guide
```

---

## ⚙️ XAMPP Setup Instructions

### Step 1: Start XAMPP Services
1. Open the **XAMPP Control Panel**.
2. Click **Start** for both **Apache** and **MySQL**.
3. Verify that both modules display a green status.

### Step 2: Import the Database
1. Open your browser and navigate to:
   ```
   http://localhost/phpmyadmin/
   ```
2. Click on the **Import** tab at the top.
3. Click **Choose File** and select `database.sql` located in this project directory:
   ```
   C:\xampp\htdocs\hidden_places_explorer\database.sql
   ```
4. Click the **Import** button at the bottom of the page.
5. phpMyAdmin will execute the script, creating the `hidden_places_explorer` database with all 7 tables and complete seed data.

### Step 3: Run the Application
Access the application in your browser at:
```
http://localhost/hidden_places_explorer/
```
*(Or if you are hosting from `http://localhost/php/`, simply navigate to that URL; the base URL auto-adapts).*

Alternatively, you can run PHP's built-in server from the project directory:
```bash
& "C:\xampp\php\php.exe" -S localhost:8000
```
Then visit `http://localhost:8000/`.

---

## 🔑 Default Credentials

### Administrator Account
- **URL**: `http://localhost/hidden_places_explorer/admin/index.php`
- **Email**: `admin@hiddenplaces.com`
- **Password**: `Admin@12345`

### Demo Explorer (User) Account
- **URL**: `http://localhost/hidden_places_explorer/login.php`
- **Email**: `elena@example.com`
- **Password**: `User@12345`
*(Or click "Join Free" to register a new account anytime).*

---

## 🔒 Security Practices Implemented
1. **Prepared Statements (PDO)**: Every database interaction utilizes parameterized PDO statements with zero SQL concatenation, preventing SQL injection vulnerabilities.
2. **Password Hashing**: Strong `password_hash()` with `PASSWORD_BCRYPT` for both users and administrators; zero plaintext passwords.
3. **Cross-Site Scripting (XSS) Prevention**: All dynamic output is escaped using `htmlspecialchars(..., ENT_QUOTES, 'UTF-8')`.
4. **Cross-Site Request Forgery (CSRF) Protection**: Cryptographically secure random tokens generated and validated on all forms and action links.
5. **Session Fixation Defense**: Sessions are regenerated upon login with `session_regenerate_id(true)` and cookie flags (`HttpOnly`, `SameSite=Lax`).
