<?php if (!in_array($_SERVER['REMOTE_ADDR'], ['87.100.58.217', '79.19.219.29'])) http_response_code(401) and die("401 Forbidden"); ?>
<!doctype html>
<html lang="ua">
<head>
    <meta charset="UTF-8">
    <meta name="viewport"
          content="width=device-width, user-scalable=no, initial-scale=1.0, maximum-scale=1.0, minimum-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <title>Зареєстровані користувачі та їхні телефони</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.2.0-beta1/dist/css/bootstrap.min.css" rel="stylesheet"
          integrity="sha384-0evHe/X+R7YkIZDRvuzKMRqM+OrBnVFBL6DOitfPri4tjfHxaWutUpFmBp4vmVor" crossorigin="anonymous">
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.2.0-beta1/dist/js/bootstrap.bundle.min.js"
            integrity="sha384-pprn3073KE6tl6bjs2QrFaJGz5/SUsLqktiwsUTF55Jfv3qYSDhgCecCxMW52nD2"
            crossorigin="anonymous"></script>
</head>
<body>
<div class="container">
    <?php
    $knownUsers = json_decode(file_get_contents(__DIR__ . '/../tmp/naubade-users.json'), true);
    $countUsers = count($knownUsers);
    $index = 1;
    $knownViberUsers = json_decode(file_get_contents(__DIR__ . '/../tmp/viber-naubade-users.json'), true);
    $countViberUsers = count($knownViberUsers);
    $indexViber = 1;
    ?>
    <h1>Зареєстровані користувачі, в т.ч. ті, що залишили телефони для зв'язку</h1>
    <h2>Telegram <span class="badge bg-primary"><?=$countUsers?></span>:</h2>
    <table class="table table-sm table-responsive">
        <tr>
            <th>#</th>
            <th>Ім'я</th>
            <th>Прізвище</th>
            <th>Телефон</th>
        </tr>
        <?php foreach ($knownUsers as $user) {
            ?>
            <tr>
                <td><?= $index ?></td>
                <td><?= $user['first_name'] ?></td>
                <td><?= $user['last_name'] ?></td>
                <td>+<?= $user['phone_number'] ?? '-' ?></td>
            </tr>
            <?php
            $index++;
        }

        if (0 === $countUsers) {
            ?>
            <tr>
                <td colspan="3">Жоден користувач не зареєстрований</td>
            </tr>
            <?php
        }
        ?>
    </table>
    <h2>Viber <span class="badge bg-primary"><?=$countViberUsers?></span>:</h2>
    <table class="table table-sm table-responsive">
        <tr>
            <th>#</th>
            <th>Аватар</th>
            <th>Ім'я</th>
            <th>Телефон</th>
        </tr>
        <?php foreach ($knownViberUsers as $user) {
            ?>
            <tr>
                <td><?= $indexViber ?></td>
                <td><?= empty($user['avatar']) ? "" : "<a href='{$user['avatar']}'><img src='{$user['avatar']}' alt='{$user['name']}_avatar' width='25' height='25'></a>" ?></td>
                <td><?= $user['name'] ?></td>
                <td>+<?= $user['phone_number'] ?? '-' ?></td>
            </tr>
            <?php
            $indexViber++;
        }

        if (0 === $countViberUsers) {
            ?>
            <tr>
                <td colspan="3">Жоден користувач не зареєстрований</td>
            </tr>
            <?php
        }
        ?>
    </table>
</div>
</body>
</html>

