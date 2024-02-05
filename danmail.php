<?php
define("SMTP_CONFIGURED_EMAIL", "user@example.com");
define("SMTP_CONFIGURED_SUBJECT", "DanMail: SMTP CONFIGURED");
define("SMTP_CONFIGURED_MESSAGE", "SMTP CONFIGURED FOR DanMail");
define("INPUT_PASSWORD", "danmail");

function sanitizeAll($data) {
    return is_array($data) ? array_map('sanitizeAll', $data) : htmlspecialchars(trim($data), ENT_QUOTES, 'UTF-8');
}

function isConfigured() {
    return @mail(SMTP_CONFIGURED_EMAIL, SMTP_CONFIGURED_SUBJECT, SMTP_CONFIGURED_MESSAGE);
}

function send($to, $subject, $message, $from, $count) {
    if (!isConfigured()) {
        echo '<script>alert("Server SMTP mail not configured");</script>';
        return;
    }

    $headers = "From: $from\r\nReply-To: $from\r\nContent-type: text/html; charset=UTF-8\r\n";
    $success = $failure = 0;

    foreach (explode(',', $to) as $recipient) {
        for ($i = 0; $i < $count; $i++) {
            mail($recipient, $subject, $message, $headers) ? $success++ : $failure++;
        }
    }

    $result = $success ? "$success email(s) sent. " : '';
    $result .= $failure ? "$failure email(s) failed to send." : '';

    if ($result) {
        echo '<script>alert("' . $result . '");</script>';
    }
}

if (!isConfigured()) {
    echo '<script>alert("Server SMTP mail not configured");</script>';
    exit;
}

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $to = sanitizeAll($_POST["to"]);
    $subject = sanitizeAll($_POST["subject"]);
    $message = sanitizeAll($_POST["message"]);
    $from = filter_var(sanitizeAll($_POST["from"]), FILTER_VALIDATE_EMAIL);
    $count = max(1, intval($_POST["send_count"]));
    $enteredPassword = $_POST['password'] ?? '';

    if ($enteredPassword === INPUT_PASSWORD) {
        send($to, $subject, $message, $from, $count);
    } else {
        $passwordInvalid = true;
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>DanMail v1.1 - Danii Saahir</title>
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }

        body {
            background: #000;
            color: #00FF00;
            display: flex;
            align-items: center;
            justify-content: center;
            height: 100vh;
            margin: 0;
            font-family: 'Courier New', Courier, monospace;
        }

        form {
            max-width: 400px;
            background: #111;
            padding: 20px;
            border-radius: 8px;
            box-shadow: 0 0 20px rgba(0, 255, 0, 0.2);
        }

        h1 {
            text-align: center;
            color: #00FF00;
            margin-bottom: 20px;
            font-size: 24px;
        }

        label, p {
            font-size: 14px;
            color: #00FF00;
            margin-bottom: 8px;
            display: block;
        }

        a { color: #00FF00; text-decoration: underline; word-wrap: break-word; }

        input, textarea, input[type="password"] {
            width: 100%;
            padding: 10px;
            font-size: 14px;
            color: #00FF00;
            background: #000;
            border: 1px solid #00FF00;
            border-radius: 5px;
            margin-bottom: 20px;
            outline: none;
            transition: border-color 0.3s ease-in-out;
        }

        input[type="submit"] {
            background: #00FF00;
            color: #000;
            cursor: pointer;
            border: none;
            border-radius: 5px;
            padding: 10px 20px;
            font-size: 16px;
            display: flex;
            align-items: center;
            justify-content: center;
            transition: background 0.2s ease-in-out, transform 0.2s ease-in-out;
        }

        input[type="submit"]:hover {
            background: #008000;
            transform: scale(1.05);
        }

        .error { color: #FF0000; font-weight: bold; margin-top: 10px; }

        input[type="password"] { background: #000; color: #00FF00; border: 1px solid #00FF00; }
    </style>
</head>
<body>
    <form method="post">
        <h1>DanMail v1.1 Author:<a href="https://github.com/danii-saahir" target="_blank">Danii Saahir</a></h1>
        <?php if ($passwordInvalid): ?>
            <p class="error">Invalid password. Please try again.</p>
        <?php endif; ?>
        <label for="from">From</label>
        <input type="email" id="from" name="from" placeholder="anything@example.com" required>
        <label for="to">To</label>
        <input type="text" id="to" name="to" placeholder="recipient1@example.com,recipient2@example.com" required>
        <label for="subject">Subject</label>
        <input type="text" id="subject" name="subject" placeholder="Email subject" required>
        <label for="message">Message</label>
        <textarea id="message" name="message" rows="6" placeholder="Input message" required></textarea>
        <label for="send_count">Send Count</label>
        <input type="number" id="send_count" name="send_count" value="<?= SEND_COUNT ?>" min="1" required>
        <label for="password">Password</label>
        <input type="password" id="password" name="password" placeholder="Enter your password" required>
        <input type="submit" value="Send">
    </form>
</body>
</html>
