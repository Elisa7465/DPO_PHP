<!DOCTYPE html>
<html lang="en">

<head>
      <meta charset="UTF-8">
      <!-- Установка кодировки документа — UTF-8 (поддерживает большинство символов всех языков) -->

      <meta name="viewport" content="width=device-width, initial-scale=1.0">
      <!-- Адаптивная верстка: ширина страницы подстраивается под ширину экрана устройства -->

      <link rel="stylesheet" href="./index.css">
      <!-- Подключение внешнего файла со стилями (CSS) -->

      <link rel="preconnect" href="https://fonts.googleapis.com">
      <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
      <!-- Предварительное подключение к шрифтам Google для ускорения загрузки -->

      <link href="https://fonts.googleapis.com/css2?family=Montserrat:ital,wght@0,100..900;1,100..900&display=swap" rel="stylesheet">
      <!-- Подключение шрифта Montserrat с Google Fonts -->

      <title>Document</title>
      <!-- Заголовок страницы, отображается во вкладке браузера -->
</head>

<body>
      <div class="blank">
            <!-- Обертка формы с классом blank -->

            <h1>Обратная связь</h1>
            <!-- Заголовок формы -->

            <form action="form.php" method="POST" novalidate id="feedback-form">
                  <!-- Форма отправляется методом POST на серверный файл form.php -->
                  <!-- Атрибут novalidate отключает встроенную валидацию браузера -->

                  <div class="section">
                        <!-- Раздел формы -->
                        <label>ФИО</label>
                        <!-- Подпись для поля ввода -->
                        <input type="text" name="name">
                        <!-- Поле для ввода полного имени -->
                        <span class="error-message"></span>
                        <!-- Контейнер для отображения сообщений об ошибках -->
                  </div>

                  <div class="section">
                        <label>Email</label>
                        <input type="text" name="email">
                        <span class="error-message"></span>
                  </div>

                  <div class="section">
                        <label>Телефон</label>
                        <input type="text" name="phone">
                        <span class="error-message"></span>
                  </div>

                  <div class="section">
                        <label>Комменатрий</label>
                        <!-- В этом поле пользователь может оставить комментарий -->
                        <input type="text" name="comment">
                  </div>

                  <input type="submit" value="Отправить" id="submit">
                  <!-- Кнопка отправки формы -->
            </form>

            <div id="result" style="display:none; margin-top: 20px;"></div>
            <!-- Контейнер для отображения результата отправки формы (скрыт по умолчанию) -->
      </div>

      <script src="index.js"></script>
      <!-- Подключение JavaScript-файла для обработки логики формы -->
</body>
</html>
