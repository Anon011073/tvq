# 📺 TV Episode Tracker

A web-based application to track your favorite TV series and movies using the TMDB API.

## 🚀 Features
- Track TV shows and movies.
- Personalized watchlist and favourites.
- Episode calendar.
- Integrated video player.
- **New:** Secure login and registration system.

## 🛠️ Installation & Setup

To get the TV Tracker script running on your server, follow these steps:

### 1. Requirements
- A web server (like Apache or Nginx).
- PHP 7.4 or higher.
- SQLite3 PHP extension enabled.

### 2. Database Initialization (No phpMyAdmin Needed)
**Important:** This script uses **SQLite**, not MySQL. 

**What this means for you:**
- You **do not** need to create a database in phpMyAdmin or cPanel.
- You **do not** need a database username or password for the server.
- The entire database is stored in a single file: `api/users.db`.

To set everything up, simply navigate to the `setup.php` file in your browser:

`http://your-domain.com/setup.php`

This will:
- Automatically create the `api/users.db` file.
- Set up the user tables.
- Create the default admin account.

**⚠️ Security Note:** After successfully running the setup and logging in, please delete the `setup.php` file from your server.

### 3. Default Login
Once setup is complete, you can log in using the following credentials:
- **Username:** `admin`
- **Password:** `password123`

You can also register a new account on the registration page.

### 4. API Key
The script currently uses a built-in TMDB API key. If you wish to use your own, you can update it in:
- `api/tmdb.php`
- `js/watch.js`
- `js/movie.js`

## 📂 Project Structure
- `index.php`: Homepage with trending and popular shows.
- `calendar.php`: Upcoming episodes for your tracked shows.
- `login.php` / `register.php`: User authentication.
- `api/`: PHP scripts for database connection and TMDB API proxy.
- `js/`: Frontend logic for various pages.
- `css/`: Application styles.

---
Built with ❤️ for TV lovers.
