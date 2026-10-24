# Password Hashing Demo in PHP

This project is a simple Software Security assignment about storing and checking passwords with PHP. It uses a random salt, SHA-256, and a text file.

## Run with XAMPP

1. Start Apache from the XAMPP Control Panel.
2. Open `http://localhost/nabhan/Password_Hashing_PHP_Assignment/password_hashing.php` in a browser.
3. Register a user, then try logging in with the correct or an incorrect password.

## How it works

For each new user, the program creates a random 16-byte salt with `random_bytes(16)`. It joins the password and salt, calculates a SHA-256 hash, and saves the username, salt, and hash as a row in `users.txt`. The original password is not saved. During login, the program uses the saved salt to hash the entered password again and checks it against the stored hash.

The application was tested locally in XAMPP with a new registration, a correct-password login, an incorrect-password login, and an unregistered username.
