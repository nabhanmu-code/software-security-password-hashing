# Password Hashing Demo in PHP

A small Software Security assignment demonstrating password hashing with PHP, a random salt, SHA-256, and a text file.

## Run with XAMPP

1. Start Apache from the XAMPP Control Panel.
2. Open `http://localhost/nabhan/Password_Hashing_PHP_Assignment/password_hashing.php` in a browser.
3. Register a user, then try logging in with the correct or an incorrect password.

The program stores the login name, a random 16-byte salt in hexadecimal, and SHA-256(password + salt) as a CSV row in `users.txt`. It never stores the original password. The text file is excluded from Git so account records are not uploaded.

The same PHP file can also run in the command line with `C:\xampp\php\php.exe password_hashing.php`.

## Verification

The application was tested through the local XAMPP browser with a new registration, correct-password login, incorrect-password login, and an unregistered username. The detailed results are in `test-results.txt`; the real screenshots are in `screenshots/` and embedded in `Password_Hashing_Report.docx`.

## GitHub

Repository: https://github.com/nabhanmu-code/software-security-password-hashing
