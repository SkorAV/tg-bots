<?php
require_once __DIR__ . '/autoloader.php';

$logger = new FileLogger(__DIR__ . '/../tmp/viber-naubade.log');

try {
    $db = DBAccess::connect('localhost', 'u374561970_andrii', '#Jx+u52wR*9', 'u374561970_servicepages');
    $botData = $db->select('
        SELECT
            c.value
        FROM config c
        WHERE c.name = ?;',
        ['s' => 'naubade_viber']
    )->fetch_row();
    $botData = json_decode($botData[0]);
    $bot = new ViberBot($botData->bot_secret);
    $bot->addLogger($logger);

    $request = $bot->getUpdate();
    $update = json_decode($request);
    $knownUsers = json_decode(file_get_contents(__DIR__ . '/../tmp/viber-naubade-users.json'), true);
    $knownUsersUpdated = false;
    $knownUser = false;

    if (!empty($update->event)) {
        $mainKeyboard = [
            ['ActionBody' => 'Освітні програми', 'Text' => 'Освітні програми', 'Columns' => 3, 'Rows' => 1],
            ['ActionBody' => 'Переваги навчання', 'Text' => 'Переваги навчання', 'Columns' => 3, 'Rows' => 1],
            ['ActionBody' => 'Контакти кафедри', 'Text' => 'Контакти кафедри', 'Columns' => 3, 'Rows' => 1],
            ['ActionBody' => 'Замовити зворотній зв\'язок', 'Text' => 'Замовити зворотній зв\'язок', 'Columns' => 3, 'Rows' => 1],
            ['ActionBody' => '/help', 'Text' => 'Допомога', 'Columns' => 3, 'Rows' => 1],
            ['ActionBody' => '/stop', 'Text' => 'Залишити бот', 'Columns' => 3, 'Rows' => 1],
        ];
        $linksKeyboard = [
            [
                'Text' => 'Повна інформація для абітурієнтів',
                'ActionBody' => 'https://pk.nau.edu.ua/vstup/vstup-na-1-kurs/',
                'ActionType' => 'open-url',
                'Columns' => 6,
                'Rows' => 1,
                'Silent' => true,
            ],
            [
                'Text' => 'Освітні програми кафедри',
                'ActionBody' => 'http://feba.nau.edu.ua/osvitni-prohramy-za-iakymy-kafedra-vede-pidhotovku',
                'ActionType' => 'open-url',
                'Columns' => 6,
                'Rows' => 1,
                'Silent' => true,
            ],
            ['Text' => 'До головного меню', 'ActionBody' => 'back', 'Columns' => 6, 'Rows' => 1, 'Silent' => true],
        ];

        if ($update->event == 'conversation_started') {
            $bot->welcomeMessage()
                ->addText(messages::$helpMessage)
                ->addKeyboard([
                    ['ActionBody' => '/start', 'Text' => 'Почати', 'Columns' => 6, 'Rows' => 1],
                ])
                ->send();
        }

        if ($update->event == 'subscribed') {
            if (empty($knownUsers[$update->user->id]['name'])) {
                $knownUsers[$update->user->id] = $update->user;
                $knownUsersUpdated = true;
            }

            $bot->message($update->user)
                ->addText(messages::$welcome_message)
                ->addKeyboard($mainKeyboard)
                ->send();
        }

        if ($update->event == 'unsubscribed') {
            if (!empty($knownUsers[$update->user_id]['name'])) {
                unset($knownUsers[$update->user_id]);
                $knownUsersUpdated = true;
                $logger->log('User unsubscribed: ' . $update->user_id);
            }
        }

        if ($update->event == 'message' && $update->message->type == 'text') {
            $knownUser = !empty($knownUsers[$update->sender->id]['name']);

            switch ($update->message->text) {
                case '/start':
                    if (!$knownUser) {
                        $knownUsers[$update->sender->id] = $update->sender;
                        $knownUsersUpdated = true;
                    }

                    $bot->message($update->sender)
                        ->addText(messages::$welcome_message)
                        ->addKeyboard($mainKeyboard, true, true)
                        ->send();

                    break;
                case 'Освітні програми':
                    if (!$knownUser) {
                        break;
                    }

                    $message = <<<'TXT'
КАФЕДРА БІЗНЕС-АНАЛІТИКИ ТА ЦИФРОВОЇ ЕКОНОМІКИ
оголошує набір за спеціальністю
*051 «ЕКОНОМІКА»*
на освітньо-професійні програми:
TXT;

                    $result = $bot->message($update->sender)
                        ->addText($message)
                        ->addKeyboard([
                            ['Text' => 'ЕКОНОМІЧНА КІБЕРНЕТИКА', 'ActionBody' => 'ec', 'Columns' => 6, 'Rows' => 1],
                            ['Text' => 'ЦИФРОВА ЕКОНОМІКА', 'ActionBody' => 'de', 'Columns' => 6, 'Rows' => 1],
                            ['Text' => 'МІЖНАРОДНА ЕКОНОМІКА', 'ActionBody' => 'ie', 'Columns' => 6, 'Rows' => 1],
                            ['Text' => 'До головного меню', 'ActionBody' => 'back', 'Columns' => 6, 'Rows' => 1, 'Silent' => true],
                        ])
                        ->send();

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

                    $result = $bot->message($update->sender)
                        ->addText($message)
                        ->addKeyboard([
                            [
                                'Text' => 'Повна інформація для абітурієнтів',
                                'ActionBody' => 'https://pk.nau.edu.ua/vstup/vstup-na-1-kurs/',
                                'ActionType' => 'open-url',
                                'Columns' => 6,
                                'Rows' => 1,
                                'Silent' => true,
                            ],
                            ['Text' => 'До головного меню', 'ActionBody' => 'back', 'Columns' => 6, 'Rows' => 1, 'Silent' => true]
                        ])
                        ->send();

                    break;
                case 'Контакти кафедри':
                    if (!$knownUser) {
                        break;
                    }

                    $message = <<<'TXT'
*Адреса* :
Україна, 03058, м. Київ,
пр. Космонавта Комарова 1,   
корпус 2, каб. 301, 402

*Тел.* : (044) 406-77-05, 406-77-90; 406-78-10

*E-mail* : nau_bade@ukr.net 

*Сайт* : http://feba.nau.edu.ua/kafedri/kafedra-ekonomichnoji-kibernetiki

*TikTok* : https://www.tiktok.com/@economics_feba

*Instagram* : https://instagram.com/economics_feba?igshid=YmMyMTA2M2Y=
TXT;

                    $result = $bot->message($update->sender)
                        ->addText($message)
                        ->addKeyboard($mainKeyboard)
                        ->send();

                    break;
                case 'Замовити зворотній зв\'язок':
                    if (!$knownUser) {
                        break;
                    }

                    $message = <<<'TXT'
Залиште нам свій номер телефону, і ми зв'яжемося з вами та надамо детальну інформацію щодо вступу та навчання.
TXT;

                    $result = $bot->message($update->sender)
                        ->addText($message)
                        ->addKeyboard([
                            ['Text' => 'Не залишати мій номер', 'ActionBody' => 'Не залишати мій номер', 'Columns' => 6, 'Rows' => 1],
                            ['Text' => 'Залишити мій номер', 'ActionBody' => 'Залишити мій номер', 'ActionType' => 'share-phone', 'Columns' => 6, 'Rows' => 1],
                        ])
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
                    $bot->message($update->sender)
                        ->addText($message)
                        ->addKeyboard($mainKeyboard)
                        ->send();

                    break;
                case '/help':
                    $message = <<<'TXT'
Цей бот призначений для отримання інформації про освітньо-професійні програми кафедри бізнес-аналітики та цифрової економіки.
Щоб почати роботу, введіть /start
Щоб закінчити роботу, введіть /stop
Щоб знову побачити це повідомлення, введіть /help
TXT;
                    $result = $bot->message($update->sender)
                        ->addText($message)
                        ->addKeyboard([
                            ['ActionBody' => '/start', 'Text' => 'Почати', 'Columns' => 3, 'Rows' => 1],
                            ['ActionBody' => '/stop', 'Text' => 'Залишити бот', 'Columns' => 3, 'Rows' => 1],
                        ])
                        ->send();

                    break;
                case '/stop':
                    $bot->message($update->sender)
                        ->addText('До зустрічі!')
                        ->send();

                    unset($knownUsers[$update->sender->id]);
                    $knownUsersUpdated = true;

                    break;
                case 'back':
                    if (!$knownUser) {
                        break;
                    }

                    $bot->message($update->sender)
                        ->addKeyboard($mainKeyboard)
                        ->send();

                    break;
                case 'ec':
                    if (!$knownUser) {
                        break;
                    }

                    $message = <<<'TXT'
ОСВІТНЬО-ПРОФЕСІЙНА ПРОГРАМА
*_«ЕКОНОМІЧНА КІБЕРНЕТИКА»_*

ВСТУПНІ КОНКУРСНІ ПРЕДМЕТИ:
та їх значимість:
│ 1 │ Українська мова │ 0,35 │
│ 2 │ Математика        │ 0,4   │
│ 3 │ Історія України   │ 0,25 │

Навчаюсь на ОПП «Економічна кібернетика» ви отримаєте кваліфікацію у сфері бізнес-аналізу (Data Scientist), оволодієте навичками організаційно-економічного управління, ефективними математичними методами аналізу і прогнозування економічних процесів з використанням сучасних інформаційних технологій. 

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

                    $bot->message($update->sender)
                        ->addText($message)
                        ->addKeyboard($linksKeyboard)
                        ->send();

                    break;
                case 'de':
                    if (!$knownUser) {
                        break;
                    }

                    $message = <<<'TXT'
ОСВІТНЬО-ПРОФЕСІЙНА ПРОГРАМА
*_«ЦИФРОВА ЕКОНОМІКА»_*

ВСТУПНІ КОНКУРСНІ ПРЕДМЕТИ:
та їх значимість:
│ 1 │ Українська мова │ 0,35 │
│ 2 │ Математика        │ 0,4   │
│ 3 │ Історія України   │ 0,25 │

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

                    $bot->message($update->sender)
                        ->addText($message)
                        ->addKeyboard($linksKeyboard)
                        ->send();

                    break;
                case 'ie':
                    if (!$knownUser) {
                        break;
                    }

                    $message = <<<'TXT'
ОСВІТНЬО-ПРОФЕСІЙНА ПРОГРАМА
*_«МІЖНАРОДНА ЕКОНОМІКА»_*

ВСТУПНІ КОНКУРСНІ ПРЕДМЕТИ:
та їх значимість:
│ 1 │ Українська мова │ 0,35 │
│ 2 │ Математика        │ 0,4   │
│ 3 │ Історія України   │ 0,25 │

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

                    $bot->message($update->sender)
                        ->addText($message)
                        ->addKeyboard($linksKeyboard)
                        ->send();

                    break;
                default:
                    if (!$knownUser) {
                        break;
                    }

                    $message = <<<'TXT'
Я не розумію цієї команди...
Може тому, що в мене поки немає штучного інтелекту?
:)
Приходьте навчатися на кафедру бізнес-аналітики та цифрової економіки факультету економіки та бізнес-адміністрування НАУ і вивчайте AI, Machine Learning, VR/AR та інші сучасні цифрові технології, що допомагають бізнесу.
TXT;

                    if ($update->silent) {
                        $message = $update->message->text;
                    }

                    $bot->message($update->sender)
                        ->addText($message)
                        ->addKeyboard($mainKeyboard)
                        ->send();

                    break;
            }
        }

        if ($update->event == 'message' && $update->message->type == 'contact') {
            $knownUsers[$update->sender->id]['phone_number'] = $update->message->contact->phone_number;
            $knownUsersUpdated = true;

            $admins = json_decode(file_get_contents(__DIR__ . '/../tmp/viber-naubade-admins.json'));
            $user = (object)$knownUsers[$update->sender->id];
            $message = <<<TXT
Отримано запит на консультацію:
Ім'я: {$user->name}
Телефон: {$user->phone_number}
TXT;

            foreach ($admins as $adminId) {
                if (!empty($knownUsers[$adminId])) {
                    $bot->message((object)$knownUsers[$adminId])
                        ->addText($message)
                        ->addKeyboard($mainKeyboard)
                        ->send();
                }
            }

            $message = <<<'TXT'
Дякуємо! Ми обов'язково зв'яжемося з вами найближчим часом.
А поки, будь ласка, скористайтеся меню нижче, щоб отримати більше інформації самостійно.
TXT;

            $bot->message($update->sender)
                ->addText($message)
                ->addKeyboard($mainKeyboard)
                ->send();
        }
    }

    if ($knownUsersUpdated) {
        file_put_contents(__DIR__ . '/../tmp/viber-naubade-users.json', json_encode($knownUsers));
    }
} catch (Throwable $e) {
    $logger->log($e->getMessage() . PHP_EOL . $e->getTraceAsString() . PHP_EOL);
}