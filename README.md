# Stickers Collector Project
The concept is to create a website that allows people to own digital albums, and let them collect daily stickers packs which they can fill them in with. They can swap these stickers with friends and build up their collection! Some silly concept, as usual.

For now, it will be restricted to one of my game by default, that is one album on Pyro-Illusion. But I would like to expand so that any creator can create their albums and allow people to start collecting stickers for those.

This website is planned to be hosted as a subdomain on [onacid.net](https://onacid.net) in the future.

> [!IMPORTANT]  
> Just like any other projects, I make these to learn and figure out new web-development tricks during my free time, but also to have a fun project to work on. This means that I'm not a professional, and I'm not necessarily following the best practices. Any feedback is welcome, however!

## Website features
Here is a list of non-exhaustive features that the website is currently cable of:
- Main landing page with hero section, website description.
- User account creation process with email verification, user can login.

## Other utility features
Some other bits and bobs that are not necessarily content related but more of **utily features** that I've added to the site and that I'm particulary excited with, worth highlighting in this project:
- Users class that allows to easily manage users, fetch their data, check for permission levels.
- Added sub-page navigation features, which can handle any nav bars, update the URL hash and change the page accordingly. It also handles the browser's back/forward buttons. Can be given custom callbacks to execute when a sub-page is changed.
- Proper API in the backend `backend/api/` that avoid shoving everything in single php files. Instead it rather calls classes and function from other areas that would handle the heavy-lifting logic, while API files would handle the "high-level" logic. This allows to have a modular, consistent accross all API calls, and organized backend with as little duplicate code as possible. (Auth related files yet to be cleaned though)
- Email sending with proper SMTP.
- Designed advanced and modular input fields (text inputs, checkboxes), with optional icons, descriptions, and is capable of displaying well integrated error messages. Easy to fetch the field's properties such as name, type, values for later usage in forms.
- New ajax queries script handling. Uses `fetch()`, robustly handles successe and error cases that can be customized by the user with callbacks, handles JSON conversion (and possibly failures occuring in the backend).
 
> [!NOTE]  
> These features are cool only because it's either the first time I tried implementing them or because I've upgraded their code/logic/concept since previous implementations.

## Setup
Would you like to run it locally for yourself? The project runs in a basic Apache environments, with PHP (v7.4.33) and MySQL. I use [MAMP](https://www.mamp.info/en/mamp) for that matter because it's been the easiest and most hustle free environment.

### MySQL db (to be added)
Import the `backend/sql/booklet.sql` file, which contains the empty tables for the database.

### `keys.php` file
Additionally you should setup the following variables in `backend/keys.php` which isn't included in the repository.

| Variable | Description |
|----------|-------------|
|`DB_SERVERNAME`|Server hosting the database (eg. localhost)|
|`DB_USERNAME`|Database login username|
|`DB_PASSWORD`|Database login password|
|`DB_NAME`|Name of the database|
|`SMTP_HOST`|Server providing the SMTP|
|`SMTP_PORT`|Server's port (I use 465)|
|`SMTP_USERNAME`|Registered email as sender|
|`SMTP_PASSWORD`|Password for sender|
|`DOC_ROOT`|Root folder for paths (eg. `$_SERVER["DOCUMENT_ROOT"]."/ROOT_FOLDER/"`)|

## Other acknowledgments
Thanks for anyone supporting me during the making of this project!

Code for sending email is handled by [PHPMailer](https://github.com/PHPMailer/PHPMailer).

Fonts are from their respective creators, and are granted for personal use.

---
> [!IMPORTANT]  
> This "Stickers Collector" project, including all code, assets, documentation, and other materials, are made by me, [@TruddyTheDuddi](https://github.com/TruddyTheDuddi). While I don't mind you using it for your own personal use or for learning purposes, please do not use it for commercial purposes, nor host it as your own website.
