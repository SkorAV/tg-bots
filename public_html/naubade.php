<?php
require_once __DIR__ . '/autoloader.php';

$logger = new FileLogger(__DIR__ . '/../tmp/naubade.log');

try {
    $db = DBAccess::connect('localhost', 'u374561970_andrii', '#Jx+u52wR*9', 'u374561970_servicepages');
    $botData = $db->select('
        SELECT
            c.value
        FROM config c
        WHERE c.name = ?;',
        ['s' => 'naubade_bot']
    )->fetch_row();
    $botData = json_decode($botData[0]);
    $bot = new TgBot($botData->bot_secret);
    $bot->addLogger($logger);

    $request = $bot->getUpdate();
    $update = json_decode($request);
    $knownUsers = json_decode(file_get_contents(__DIR__ . '/../tmp/naubade-users.json'), true);
    $knownUsersUpdated = false;
    $knownUser = false;

    if (!empty($update->message)) {
        $knownUser = !empty($knownUsers[$update->message->from->id]['first_name']);

        switch ($update->message->text) {
            case '/start':
                if (!$knownUser) {
                    $knownUsers[$update->message->from->id] = $update->message->from;
                    $knownUsersUpdated = true;
                }

                $message = <<<'TXT'
Якщо ви бажаєте:
- вирішувати складні економічні проблеми;
- удосконалити володіння діловою іноземною мовою;
- розвинути лідерські організаційні якості креативного та нестандартного мислення
Тоді ми чекаємо саме на Вас!

Скористайтесь меню нижче, щоб дізнатися більше.
TXT;
                $bot->message($update->message->from)
                    ->addText($message, [
                        ["type" => "bold", "offset" => 192, "length" => 28],
                        ["type" => "italic", "offset" => 192, "length" => 28]
                    ])
                    ->addKeyboard([
                        [['text' => 'Освітні програми'], ['text' => 'Переваги навчання']],
                        [['text' => 'Контакти кафедри'], ['text' => 'Замовити зворотній зв\'язок']],
                    ], true, true)
                    ->send();

                break;
            case 'Освітні програми':
                if (!$knownUser) {
                    break;
                }

                $message = <<<'TXT'
КАФЕДРА БІЗНЕС-АНАЛІТИКИ ТА ЦИФРОВОЇ ЕКОНОМІКИ
оголошує набір за спеціальністю
051 «ЕКОНОМІКА» 
на освітньо-професійні програми:
TXT;

                $result = $bot->message($update->message->from)
                    ->addText($message, [["type" => "bold", "offset" => 78, "length" => 15]])
                    ->addInlineKeyboard([
                        [['text' => 'ЕКОНОМІЧНА КІБЕРНЕТИКА', 'callback_data' => 'ec']],
                        [['text' => 'ЦИФРОВА ЕКОНОМІКА', 'callback_data' => 'de']],
                        [['text' => 'МІЖНАРОДНА ЕКОНОМІКА', 'callback_data' => 'ie']],
                    ])
                    ->send();
                $messageId = $result->result->message_id;
                $knownUsers[$update->message->from->id]['lastMessageId'] = $messageId;
                $knownUsersUpdated = true;

                break;
            case 'Переваги навчання':
                if (!$knownUser) {
                    break;
                }

                $message = <<<'TXT'
1. Ви станете студентом високорейтингового закладу вищої освіти – Національного авіаційного університету.
2. Ви долучитесь до економічної еліти, навчаючись у професійних та висококваліфікованих науково-педагогічних працівників університету.
3. Навчальний процес відбувається у відповідності до сучасних світових стандартів та новітніх методик викладання. 
4. Маєте можливість обирати мову викладання (українська, англійська), відкриваючи нові перспективи для подальшого працевлаштування у міжнародних компаніях України та за кордоном.
5. Розвинута інфраструктура НАУ включає: 
   • спортивний комплекс, 
   • палац культури, 
   • наукова бібліотека, 
   • студентські їдальні,
   • лікувальний профілакторій,
   • бази відпочинку.
6. Для всіх бажаючих представляються гуртожитки та хостели.
TXT;

                $result = $bot->message($update->message->from)
                    ->addText($message)
                    ->addInlineKeyboard([[[
                        'text' => 'Повна інформація для абітурієнтів',
                        'url' => 'https://pk.nau.edu.ua/vstup/vstup-na-1-kurs/',
                    ]]])
                    ->send();

                break;
            case 'Контакти кафедри':
                if (!$knownUser) {
                    break;
                }

                $message = <<<'TXT'
<b>Адреса:</b>
Україна, 03058, м. Київ,
пр. Космонавта Комарова 1,   
корпус 2, каб. 301, 402

<b>Тел.:</b> (044) 406-77-05, 406-77-90; 406-78-10

<b>E-mail:</b> nau_bade@ukr.net 

<b>Сайт:</b> <a href="http://feba.nau.edu.ua/kafedri/kafedra-ekonomichnoji-kibernetiki">feba.nau.edu.ua/kafedri/kafedra-ekonomichnoji-kibernetiki</a>

<b>TikTok:</b> <a href="https://www.tiktok.com/@economics_feba">economics_feba</a>

<b>Instagram:</b> <a href="https://instagram.com/economics_feba?igshid=YmMyMTA2M2Y=">economics_feba</a>
TXT;

                $result = $bot->message($update->message->from)
                    ->addText($message, null, TgBot::FORMAT_HTML)
                    ->send();

                break;
            case 'Замовити зворотній зв\'язок':
                if (!$knownUser) {
                    break;
                }

                $message = <<<'TXT'
Залиште нам свій номер телефону, і ми зв'яжемося з вами та надамо детальну інформацію щодо вступу та навчання.
TXT;

                $result = $bot->message($update->message->from)
                    ->addText($message)
                    ->addKeyboard([[
                        [
                            'text' => 'Не залишати мій номер'
                        ],
                        [
                            'text' => 'Залишити мій номер',
                            'request_contact' => true,
                        ],
                    ]])
                    ->send();

                break;
            case 'Не залишати мій номер':
                if (!$knownUser) {
                    break;
                }

                $message = <<<'TXT'
Нажаль, без телефонного номера ми не зможемо зв'язатися з вами :(
В цьому разі, будь ласка, скористайтеся меню нижче, щоб отримати більше інформації самостійно.
TXT;
                $bot->message($update->message->from)
                    ->addText($message)
                    ->addKeyboard([
                        [['text' => 'Освітні програми'], ['text' => 'Переваги навчання']],
                        [['text' => 'Контакти кафедри'], ['text' => 'Замовити зворотній зв\'язок']],
                    ], true, true)
                    ->send();

                break;
            case '/help':
                $message = <<<'TXT'
Цей бот призначений для отримання інформації про освітньо-професійні програми кафедри бізнес-аналітики та цифрової економіки.
Щоб почати роботу, введіть /start
Щоб закінчити роботу, введіть /stop
Щоб знову побачити це повідомлення, введіть /help
TXT;
                $result = $bot->message($update->message->from)
                    ->addText($message)
                    ->send();

                break;
            case '/stop':
                unset($knownUsers[$update->message->from->id]);
                $knownUsersUpdated = true;

                break;
            default:
                if (!$knownUser) {
                    break;
                }

                if (!empty($update->message->contact->phone_number)) {
                    $knownUsers[$update->message->from->id]['phone_number'] = $update->message->contact->phone_number;
                    $knownUsersUpdated = true;

                    $admins = json_decode(file_get_contents(__DIR__ . '/../tmp/naubade-admins.json'));
                    $user = (object)$knownUsers[$update->message->from->id];
                    $message = <<<TXT
Отримано запит на консультацію:
Ім'я: {$user->first_name}
Прізвище: {$user->last_name}
Телефон: {$user->phone_number}
TXT;

                    foreach ($admins as $adminId) {
                        if (!empty($knownUsers[$adminId])) {
                            $bot->message((object)$knownUsers[$adminId])
                                ->addText($message)
                                ->send();
                        }
                    }

                    $message = <<<'TXT'
Дякуємо! Ми обов'язково зв'яжемося з вами найближчим часом.
А поки, будь ласка, скористайтеся меню нижче, щоб отримати більше інформації самостійно.
TXT;

                    $bot->message($update->message->from)
                        ->addText($message)
                        ->addKeyboard([
                            [['text' => 'Освітні програми'], ['text' => 'Переваги навчання']],
                            [['text' => 'Контакти кафедри'], ['text' => 'Замовити зворотній зв\'язок']],
                        ], true, true)
                        ->send();

                    break;
                }

                $message = <<<'TXT'
Я не розумію цієї команди...
Може тому, що в мене поки немає штучного інтелекту?
Приходьте навчатися на кафедру бізнес-аналітики та цифрової економіки факультету економіки та бізнес-адміністрування НАУ і вивчайте AI, Machine Learning, VR/AR та інші сучасні цифрові технології, що допомагають бізнесу.
TXT;
                $bot->message($update->message->from)
                    ->addText($message)
                    ->send();

                break;
        }
    }

    if (!empty($update->callback_query)) {
        $knownUser = !empty($knownUsers[$update->callback_query->from->id]['first_name']);
        $messageId = $knownUsers[$update->callback_query->from->id]['lastMessageId'] ?? 0;

        switch ($update->callback_query->data) {
            case 'ec':
                $bot->answerCallbackQuery($update->callback_query->id);

                if (!$knownUser || 0 === $messageId) {
                    break;
                }

                $message = <<<'TXT'
ОСВІТНЬО-ПРОФЕСІЙНА ПРОГРАМА
<b><i>«ЕКОНОМІЧНА КІБЕРНЕТИКА»</i></b>

ВСТУПНІ КОНКУРСНІ ПРЕДМЕТИ:
та їх значимість:
<pre>│ 1 │ Українська мова │ 0,35 │
│ 2 │ Математика      │ 0,4  │
│ 3 │ Історія України │ 0,25 │</pre>

Навчаюсь на ОПП «Економічна кібернетика» ви отримаєте кваліфікацію у сфері бізнес-аналізу (Data Scientist), опануєте навички організаційно-економічного управління, ефективними математичними методами аналізу і прогнозування економічних процесів з використанням сучасних інформаційних технологій. 

В ході навчання ви отримаєте знання з дисциплін:
 • «Моделювання бізнес-процесів»,
 • «Економічна кібернетика»,
 • «Оптимізаційні методи і моделі»,
 • «Введення в бізнес-аналіз»,
 • «Програмування в економіці»,
 • «Теорія ігор в економіці»,
 • «Управління проєктами»,
 • «Системи підтримки прийняття рішень»,
 • «Ризикологія»,
 • «Python для бізнес-аналітика» тощо.

Випускники працевлаштовуються у:
Міністерство цифрової трансформації України, Міністерство фінансів України, Державну службу статистики України, Національний банк України, центри соціологічних та маркетингових досліджень, консалтингові агенції та аудиторські компанії, банки, ІТ-компанії, підприємства різних форм власності та виробничого спрямування, компанії стільникового зв’язку.
TXT;

                $bot->editMessageText($update->callback_query->from->id, $messageId, $message, TgBot::FORMAT_HTML);
                $bot->editMessageReplyMarkup($update->callback_query->from->id, $messageId, [
                    [[
                        'text' => 'Повна інформація для абітурієнтів',
                        'url' => 'https://pk.nau.edu.ua/vstup/vstup-na-1-kurs/',
                    ]],
                    [[
                        'text' => 'Освітні програми кафедри',
                        'url' => 'http://feba.nau.edu.ua/osvitni-prohramy-za-iakymy-kafedra-vede-pidhotovku',
                    ]],
                ]);
                unset($knownUsers[$update->callback_query->from->id]['lastMessageId']);

                break;
            case 'de':
                $bot->answerCallbackQuery($update->callback_query->id);

                if (!$knownUser || 0 === $messageId) {
                    break;
                }

                $message = <<<'TXT'
ОСВІТНЬО-ПРОФЕСІЙНА ПРОГРАМА
<b><i>«ЦИФРОВА ЕКОНОМІКА»</i></b>

ВСТУПНІ КОНКУРСНІ ПРЕДМЕТИ:
та їх значимість:
<pre>│ 1 │ Українська мова │ 0,35 │
│ 2 │ Математика      │ 0,4  │
│ 3 │ Історія України │ 0,25 │</pre>

Навчаюсь на ОПП «Цифрова економіка» ви отримаєте глибокі знання в області сучасних інформаційних технологій, опануєте сучасні програмні продукти інтелектуального аналізу даних, будете визначати перспективні напрями цифровізації та забезпечення кібербезпеки на різних рівнях управління економічними системами. 

В ході навчання ви отримаєте знання з дисциплін:
 • «Цифрова економіка: цифрова трансформація середовища і бізнесу»,
 • «Системний аналіз в економіці», 
 • «Електронна комерція», 
 • «Інтернет-технології в бізнесі», 
 • «Введення в аналіз Big Data», 
 • «Web-аналітика та цифровий маркетинг», 
 • «Основи машинного навчання» тощо.

Випускники працевлаштовуються у:
провідні ІТ-компанії України та світу, державні установи, Міністерство цифрової трансформації України, Міністерство економічного розвитку та торгівлі України, Національний банк України, науково-дослідні економічні інститути, компанії з управління активами та комерційні банки, страхові компанії та інвестиційні фонди, міжнародні та вітчизняні промислові підприємства.
TXT;

                $bot->editMessageText($update->callback_query->from->id, $messageId, $message, TgBot::FORMAT_HTML);
                $bot->editMessageReplyMarkup($update->callback_query->from->id, $messageId, [
                    [[
                        'text' => 'Повна інформація для абітурієнтів',
                        'url' => 'https://pk.nau.edu.ua/vstup/vstup-na-1-kurs/',
                    ]],
                    [[
                        'text' => 'Освітні програми кафедри',
                        'url' => 'http://feba.nau.edu.ua/osvitni-prohramy-za-iakymy-kafedra-vede-pidhotovku',
                    ]],
                ]);
                unset($knownUsers[$update->callback_query->from->id]['lastMessageId']);

                break;
            case 'ie':
                $bot->answerCallbackQuery($update->callback_query->id);

                if (!$knownUser || 0 === $messageId) {
                    break;
                }

                $message = <<<'TXT'
ОСВІТНЬО-ПРОФЕСІЙНА ПРОГРАМА
<b><i>«МІЖНАРОДНА ЕКОНОМІКА»</i></b>

ВСТУПНІ КОНКУРСНІ ПРЕДМЕТИ:
та їх значимість:
<pre>│ 1 │ Українська мова │ 0,35 │
│ 2 │ Математика      │ 0,4  │
│ 3 │ Історія України │ 0,25 │</pre>

Навчаюсь на ОПП «Міжнародна економіка» ви здобудете теоретичні знання та практичний досвід щодо роботи посольств, міжнародних організацій, представництв міжнародних компаній в Україні, ознайомитеся з історією, культурою, традиціями зарубіжних країн, що лежать в основі їх економічних відносин.

В ході навчання ви отримаєте знання з дисциплін: 
 • «Міжнародна економіка», 
 • «Міжнародне економічне право», 
 • «Міжнародна торгівля», 
 • «Міжнародний маркетинг», 
 • «Міжнародні фінанси», 
 • «Міжнародні біржові ринки», 
 • «Міжнародний економічний аналіз», 
 • «Міжнародні стратегії економічного розвитку» тощо.

Випускники працевлаштовуються у:
державних структурах, міністерствах та відомствах, міжнародних організаціях, комерційних підприємствах, фінансових установах (банки, кредитні організації, страхові компанії), авіапідприємствах (авіакомпаніях та їх іноземних представництвах, аеропортах), торгівельно-промисловій палаті, посольствах, представницьких державних та комерційних органах за кордоном.
TXT;

                $bot->editMessageText($update->callback_query->from->id, $messageId, $message, TgBot::FORMAT_HTML);
                $bot->editMessageReplyMarkup($update->callback_query->from->id, $messageId, [
                    [[
                        'text' => 'Повна інформація для абітурієнтів',
                        'url' => 'https://pk.nau.edu.ua/vstup/vstup-na-1-kurs/',
                    ]],
                    [[
                        'text' => 'Освітні програми кафедри',
                        'url' => 'http://feba.nau.edu.ua/osvitni-prohramy-za-iakymy-kafedra-vede-pidhotovku',
                    ]],
                ]);
                unset($knownUsers[$update->callback_query->from->id]['lastMessageId']);

                break;
            default:
                $bot->answerCallbackQuery($update->callback_query->id);
                break;
        }
    }

    if (!empty($update->my_chat_member->new_chat_member->status) && $update->my_chat_member->new_chat_member->status == 'kicked') {
        unset($knownUsers[$update->my_chat_member->from->id]);
        $knownUsersUpdated = true;
    }

    if ($knownUsersUpdated) {
        file_put_contents(__DIR__ . '/../tmp/naubade-users.json', json_encode($knownUsers));
    }
} catch (Throwable $e) {
    $logger->log($e->getMessage() . PHP_EOL . $e->getTraceAsString() . PHP_EOL);
}

exit;
