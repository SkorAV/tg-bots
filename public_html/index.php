<?php if($_SERVER['REMOTE_ADDR'] !== '87.100.58.217') http_response_code(401) and die("401 Forbidden"); ?>
<!doctype html>
<html lang="ua">
<head>
    <meta charset="UTF-8">
    <meta name="viewport"
          content="width=device-width, user-scalable=no, initial-scale=1.0, maximum-scale=1.0, minimum-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <title>Зареєстровані користувачі і їхні телефони</title>
</head>
<body>
<h1>Зареєстровані користувачі, що залишали телефони для зв'язку</h1>
<?php
$knownUsers = json_decode(file_get_contents(__DIR__ . '/../tmp/naubade-users.json'), true);
$usersWithPhones = false;
?>
<table>
    <tr>
        <th>Ім'я</th><th>Прізвище</th><th>Телефон</th>
    </tr>
    <?php foreach ($knownUsers as $user) {
        if (!empty($user['phone_number'])) {
            $usersWithPhones = true;
    ?>
            <tr>
                <td><?=$user['first_name']?></td>
                <td><?=$user['last_name']?></td>
                <td>+<?=$user['phone_number']?></td>
            </tr>
    <?php
        }
    }

    if (!$usersWithPhones) {
    ?>
        <tr><td colspan="3">Жоден користувач не залишив телефон</td></tr>
    <?php
    }
    ?>
</table>
</body>
</html>

