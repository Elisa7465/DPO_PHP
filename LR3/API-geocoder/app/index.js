// Ждём полной загрузки HTML-документа, чтобы скрипт начал работать
document.addEventListener('DOMContentLoaded', () => {
    // Находим первую кнопку на странице и навешиваем обработчик клика
    document.querySelector('button').addEventListener('click', () => {
        // Находим поле ввода адреса по имени "address"
        const input = document.querySelector('input[name="address"]');
        // Получаем введённое значение и убираем пробелы по краям
        const address = input.value.trim();

        // Проверяем, пустое ли поле
        if (!address) {
            alert("Введите адрес"); // Если пусто — показываем предупреждение
            return; // Прекращаем дальнейшее выполнение функции
        }

        // Создаём новый объект XMLHttpRequest для запроса к серверу
        const xhr = new XMLHttpRequest();

        // Настраиваем GET-запрос на адрес geocoder.php с параметром address,
        // значение адреса кодируем для корректной передачи в URL
        xhr.open("GET", `geocoder.php?address=${encodeURIComponent(address)}`, true);

        // Определяем функцию, которая будет вызываться при изменении состояния запроса
        xhr.onreadystatechange = function () {
            // Проверяем, что запрос полностью завершён (readyState === 4) и успешен (status === 200)
            if (xhr.readyState === 4 && xhr.status === 200) {
                // Находим элемент с id "result", куда будем выводить ответ
                let resultDiv = document.getElementById('result');
                try {
                    // Парсим JSON-строку ответа сервера в объект
                    const data = JSON.parse(xhr.responseText);

                    // Проверяем, есть ли в ответе ошибка
                    if (data.error) {
                        // Если есть ошибка, показываем её красным цветом
                        resultDiv.innerHTML = `<p style="color:red;">Ошибка: ${data.error}</p>`;
                    } else {
                        // Если ошибки нет, выводим структурированные данные по адресу
                        resultDiv.innerHTML = `
                            <p><strong>Структурированный адрес:</strong> ${data.structuredAddress}</p>
                            <p><strong>Координаты:</strong> ${data.coordinates}</p>
                            <p><strong>Ближайшее метро:</strong> ${data.nearestMetro}</p>
                            <p><strong>Координаты метро:</strong> ${data.metroCoordinates}</p>
                            <p><strong>Расстояние до метро:</strong> ${data.distanceToMetro}</p>
                        `;
                    }
                } catch (e) {
                    // Если не удалось распарсить ответ (например, сервер вернул не JSON),
                    // выводим сообщение об ошибке
                    resultDiv.innerHTML = `<p style="color:red;">Ошибка при разборе ответа сервера</p>`;
                }
            }
        };

        // Отправляем запрос на сервер
        xhr.send();
    });
});
