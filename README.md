# Stickers Collector Project
The concept is to create a website that allows people to own digital albums, and let them collect daily stickers packs which they can use to fill them im. They can swap these stickers with friends and build up their collection! Some silly concept, as usual.

For now, it will be restricted to one of my game by default, that is one album. But I would like to expand so that any creator can create their albums and allow people to start collecting stickers for those.

This website is planned to be hosted as a subdomain on [onacid.net](https://onacid.net) in the future.

> [!IMPORTANT]  
> Just like any other projects, I make these to learn and figure out new web-development tricks during my free time. That means the code is not error free, and there are most likely tons of other ways of doing it. So if you have any suggestions or want to contribute in a way, please do share them!

## Website features
Here is a list of non-exhaustive features that the website is currentl cable of:
- User account creation process with email code verification.

## Other utility features
Some other bits and bobs that are not necessarily content related but more **utilitary features** that I've added to the site and that I'm particulary excited with, worth highlighting in this project:
- Email sending with proper SMTP
- Designed advanced and modular input fields (text inputs, checkboxes), with optional icons, descriptions, and is capable of displaying well integrated error messages. Easy to fetch the field's properties such as name, type, values for later usage in forms.
- New ajax queries script handling. Uses `fetch()`, robustly handles successes and failures that can be provided by the user, handles JSON conversion (and possible failures occuring in backend).
 
> [!NOTE]  
> These features are cool only because it's either the first time I tried implementing them or because I've upgraded their code/logic/concept since previous implementations.

## Setup
Runs on a basic Apache server, with PHP (v7.4.33) and MySQL. I use [MAMP](https://www.mamp.info/en/mamp) for that matter because it's been the easiest and most hustle free environment.

### MySQL db (to be added)
Import the `backend/sql/booklet.sql` file, which contains the empty tables for the database.

### `keys.php` file
Additionally you should setup the following variablse in `backend/keys.php` which isn't included in the repository.

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

Fonts are from their respective creators.

---
> [!IMPORTANT]  
> This "Stickers Collector" project, including all code, assets, documentation, and other materials, are my property, [@TruddyTheDuddi](https://github.com/TruddyTheDuddi)
> 
> This project is shared for educational and collaborative purposes. Unauthorized distribution, or commercial use is prohibited without explicit permission. I reserve the right to use this project for commercial purposes.
> 
> For inquiries or permissions, please contact me.
