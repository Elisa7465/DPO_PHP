<!DOCTYPE html>
<!-- Объявление типа документа: сообщает браузеру, что используется HTML5 -->
<html lang="en">
<!-- Начало HTML-документа. Атрибут lang="en" указывает, что язык содержимого — английский -->

<head>

    <meta charset="UTF-8">
    <!-- Устанавливает кодировку символов UTF-8 (поддерживает большинство символов всех языков) -->

    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <!-- Адаптация страницы под разные устройства. Делает дизайн отзывчивым на мобильных устройствах -->

    <link rel="stylesheet" href="./index1.css">
    <!-- Подключение внешнего CSS-файла для стилизации страницы -->

    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <!-- Устанавливает предварительное соединение с сервером шрифтов для ускорения загрузки -->

    <link href="https://fonts.googleapis.com/css2?family=Montserrat:ital,wght@0,100..900;1,100..900&display=swap" rel="stylesheet">
    <!-- Подключение шрифта Montserrat с различными стилями и толщинами через Google Fonts -->

    <title>Document</title>
    <!-- Заголовок страницы, отображаемый во вкладке браузера -->
</head>

<body>

    <div class="blank">
        <!-- Контейнер с классом "blank", используется для обертки содержимого -->

        <h1>Геокодер</h1>
        <!-- Заголовок страницы -->

        <div class="section">
            <!-- Блок ввода адреса -->

            <label>Введите адресс</label>
            <!-- Подпись к полю ввода -->

            <input type="text" name="address">
            <!-- Поле ввода текста. Пользователь сюда вводит адрес -->
        </div>

        <button>Получить данные</button>
        <!-- Кнопка, по нажатию на которую будут получены геоданные -->

        <div id="result" class="result"></div>
        <!-- Пустой блок с id="result", сюда будет выводиться результат после обработки -->
    </div>

    <script src="index.js"></script>
    <!-- Подключение внешнего JavaScript-файла, который будет обрабатывать взаимодействие -->
</body>

</html>
