<?php
$usersFile = __DIR__ . DIRECTORY_SEPARATOR . 'users.txt';

function findUser($file, string $loginName): ?array
{
    rewind($file);
    while (($row = fgetcsv($file)) !== false) {
        if (count($row) >= 3 && $row[0] === $loginName) {
            return $row;
        }
    }
    return null;
}

function registerUser(string $filePath, string $loginName, string $password): void
{
    if ($loginName === '' || $password === '') {
        echo "Username and password cannot be empty.\n";
        return;
    }

    $file = fopen($filePath, 'c+');
    if ($file === false) {
        echo "Could not open the user data file.\n";
        return;
    }
    if (!flock($file, LOCK_EX)) {
        fclose($file);
        echo "Could not lock the user data file.\n";
        return;
    }
    if (findUser($file, $loginName) !== null) {
        flock($file, LOCK_UN);
        fclose($file);
        echo "This username is already registered.\n";
        return;
    }

    try {
        // A new secure random salt is created for every registered user.
        $salt = bin2hex(random_bytes(16));
    } catch (Throwable $error) {
        flock($file, LOCK_UN);
        fclose($file);
        echo "Could not generate a secure salt.\n";
        return;
    }

    // The original password is never written to the file.
    $passwordHash = hash('sha256', $password . $salt);
    fseek($file, 0, SEEK_END);
    $saved = fputcsv($file, [$loginName, $salt, $passwordHash]);
    fflush($file);
    flock($file, LOCK_UN);
    fclose($file);

    echo $saved === false ? "Could not save the new user.\n" : "Registration successful.\n";
}

function loginUser(string $filePath, string $loginName, string $password): void
{
    if ($loginName === '' || $password === '') {
        echo "Username and password cannot be empty.\n";
        return;
    }

    $file = fopen($filePath, 'r');
    if ($file === false) {
        echo "Login failed. Username or password is incorrect.\n";
        return;
    }
    $user = findUser($file, $loginName);
    fclose($file);

    if ($user === null) {
        echo "Login failed. Username or password is incorrect.\n";
        return;
    }
    $enteredHash = hash('sha256', $password . $user[1]);
    echo hash_equals($user[2], $enteredHash)
        ? "Login successful.\n"
        : "Login failed. Username or password is incorrect.\n";
}

if (PHP_SAPI === 'cli') {
    function readLineValue(string $prompt): string
    {
        echo $prompt;
        $line = fgets(STDIN);
        return $line === false ? '' : rtrim($line, "\r\n");
    }

    echo "Password Hashing Demo (SHA-256)\n";
    while (true) {
        echo "\n1. Register\n2. Log in\n0. Exit\n";
        $choice = readLineValue('Choose an option: ');
        if ($choice === '1') {
            registerUser($usersFile, trim(readLineValue('Login name: ')), readLineValue('Password: '));
        } elseif ($choice === '2') {
            loginUser($usersFile, trim(readLineValue('Login name: ')), readLineValue('Password: '));
        } elseif ($choice === '0') {
            echo "Goodbye.\n";
            break;
        } else {
            echo "Invalid option. Please choose 1, 2, or 0.\n";
        }
    }
    exit;
}

$message = '';
$messageType = 'info';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';
    $loginName = trim($_POST['login_name'] ?? '');
    $password = $_POST['password'] ?? '';
    ob_start();
    if ($action === 'register') {
        registerUser($usersFile, $loginName, $password);
    } elseif ($action === 'login') {
        loginUser($usersFile, $loginName, $password);
    } else {
        echo "Please choose registration or login.\n";
    }
    $message = trim(ob_get_clean());
    $messageType = str_contains($message, 'successful') ? 'success' : 'error';
}
?>
<!doctype html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Password Hashing Demo</title>
    <style>
        * { box-sizing: border-box; }
        body { margin: 0; min-height: 100vh; background: #f2f5f9; color: #1f2937; font: 16px/1.5 Arial, sans-serif; }
        main { width: min(900px, calc(100% - 32px)); margin: 42px auto; }
        h1 { margin: 0 0 8px; color: #12345a; font-size: 30px; }
        .intro { margin: 0 0 24px; color: #526174; }
        .notice { padding: 14px 16px; margin: 0 0 20px; border-radius: 8px; font-weight: 700; }
        .success { background: #e5f7ed; color: #17663b; border: 1px solid #9bd8b4; }
        .error { background: #fff0ed; color: #9a2e1e; border: 1px solid #efb4a8; }
        .info { background: #eaf2ff; color: #214e83; border: 1px solid #b4c9e8; }
        .cards { display: grid; grid-template-columns: repeat(auto-fit, minmax(260px, 1fr)); gap: 18px; }
        section { padding: 22px; background: white; border: 1px solid #d7e0ea; border-radius: 12px; box-shadow: 0 5px 18px #19375412; }
        h2 { margin: 0 0 16px; color: #234b72; font-size: 21px; }
        label { display: block; margin: 12px 0 5px; font-weight: 700; }
        input { display: block; width: 100%; padding: 11px 12px; border: 1px solid #b7c4d2; border-radius: 6px; font: inherit; }
        button { margin-top: 17px; padding: 11px 16px; color: white; background: #245b91; border: 0; border-radius: 6px; font: inherit; font-weight: 700; cursor: pointer; }
        button:hover { background: #174776; }
        footer { margin-top: 22px; color: #637184; font-size: 14px; }
    </style>
</head>
<body>
<main>
    <h1>Password Hashing Demo</h1>
    <p class="intro">Register an account or test a login. This demonstration uses a random salt and SHA-256.</p>
    <?php if ($message !== ''): ?>
        <div class="notice <?= htmlspecialchars($messageType, ENT_QUOTES, 'UTF-8') ?>" role="status" data-testid="result">
            <?= htmlspecialchars($message, ENT_QUOTES, 'UTF-8') ?>
        </div>
    <?php endif; ?>
    <div class="cards">
        <section>
            <h2>Register</h2>
            <form method="post">
                <input type="hidden" name="action" value="register">
                <label for="register-name">Login name</label>
                <input id="register-name" name="login_name" autocomplete="username" required>
                <label for="register-password">Password</label>
                <input id="register-password" name="password" type="password" autocomplete="new-password" required>
                <button type="submit">Register</button>
            </form>
        </section>
        <section>
            <h2>Log in</h2>
            <form method="post">
                <input type="hidden" name="action" value="login">
                <label for="login-name">Login name</label>
                <input id="login-name" name="login_name" autocomplete="username" required>
                <label for="login-password">Password</label>
                <input id="login-password" name="password" type="password" autocomplete="current-password" required>
                <button type="submit">Log in</button>
            </form>
        </section>
    </div>
    <footer>For class demonstration only. User records are stored in a text file as login name, salt, and password hash.</footer>
</main>
</body>
</html>
