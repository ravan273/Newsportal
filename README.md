# NewsPortal (PHP + MySQL)

Nepal + World news portal with search and an admin panel (CRUD).

## Requirements

- XAMPP (Apache + MySQL + PHP)
- phpMyAdmin

## Setup

1. Start **Apache** and **MySQL** from XAMPP Control Panel.
2. Open phpMyAdmin: `http://localhost/phpmyadmin`
3. Import SQL: `database/install.sql` (v2 schema: users + moderation).
4. (Optional) Import demo news: `database/seed_news.sql`
5. Visit site: `http://localhost/newsportal/`
6. Admin login: `http://localhost/newsportal/admin/login.php`

## Project structure

- `index.php`: homepage (featured + Nepal + world)
- `news.php`: single news page
- `search.php`: search news
- `country.php`: filter by country
- `category.php`: filter by category
- `admin/`: admin panel (login + dashboard + CRUD)
- `assets/uploads/`: uploaded images
- `app/`: DB + helpers + auth

## Notes

- Update DB credentials and base URL in `app/config.php` if needed.
- Uploaded images are stored in `assets/uploads/YYYY/MM/`.

