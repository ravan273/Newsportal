# NewsPortal (PHP + MySQL)

## Requirements

- XAMPP (Apache + MySQL + PHP)
- phpMyAdmin 

yeti ta tha hola 

## Setup

1. Start **Apache** and **MySQL** from XAMPP
2. Open phpMyAdmin: `http://localhost/phpmyadmin`
3. Import SQL: `database/install.sql` (v2 schema: users + moderation).
4. (Optional) Import news: `database/seed_news.sql`
5. Visit site: `http://localhost/newsportal/`
6. Admin login: `http://localhost/newsportal/admin/login.php`

## Project structure yesto hunxa 

- `index.php`: homepage (featured + Nepal + world)
- `news.php`: single news page
- `search.php`: search news
- `country.php`: filter by country
- `category.php`: filter by category
- `admin/`: admin panel (login + dashboard + CRUD)
- `assets/uploads/`: uploaded images
- `app/`: DB + helpers + auth

## Notes

Update DB credentials and base URL in `app/config.php` if needed.

and sometime learn yourself for better future .


