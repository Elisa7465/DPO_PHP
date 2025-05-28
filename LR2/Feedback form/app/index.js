// Ждём полной загрузки DOM (всего HTML-документа)
document.addEventListener("DOMContentLoaded", () => {

    // Получаем элементы формы и её поля по их идентификаторам и именам
    const form = document.getElementById("feedback-form");
    const resultDiv = document.getElementById("result");
    const nameInput = form.querySelector("input[name='name']");
    const emailInput = form.querySelector("input[name='email']");
    const phoneInput = form.querySelector("input[name='phone']");
    const errorMessages = form.querySelectorAll(".error-message");

    // === Маска для ввода телефона ===
    phoneInput.addEventListener("input", () => {
        // Удаляем все нецифровые символы
        let value = phoneInput.value.replace(/\D/g, "");

        // Заменяем первую 8 на 7 (т.к. формат российский)
        if (value.startsWith("8")) value = "7" + value.slice(1);
        if (!value.startsWith("7")) value = "7" + value;

        // Форматируем номер в виде +7 (XXX) XXX-XX-XX
        let formatted = "+7 (";
        if (value.length > 1) formatted += value.slice(1, 4);
        if (value.length >= 4) formatted += ") " + value.slice(4, 7);
        if (value.length >= 7) formatted += "-" + value.slice(7, 9);
        if (value.length >= 9) formatted += "-" + value.slice(9, 11);

        // Устанавливаем отформатированное значение в поле ввода
        phoneInput.value = formatted;
    });

    // === Обработка отправки формы ===
    form.addEventListener("submit", function (e) {
        e.preventDefault(); // Отменяем стандартную отправку формы (страница не перезагружается)

        let valid = true; // Флаг, показывающий, прошла ли форма проверку

        // Сброс всех предыдущих сообщений об ошибках
        errorMessages.forEach(msg => {
            msg.style.display = "none";
            msg.textContent = "";
        });
        form.querySelectorAll("input").forEach(input => input.classList.remove("error"));

        // === Валидация поля ФИО ===
        const nameValue = nameInput.value.trim(); // Удаляем пробелы по краям
        const nameRegex = /^[А-Яа-яЁё\s\-]+$/; // Регулярное выражение: только русские буквы, пробелы и дефисы

        if (nameValue === "") {
            showError(nameInput, "Пожалуйста, введите ФИО");
            valid = false;
        } else if (!nameRegex.test(nameValue)) {
            showError(nameInput, "ФИО должно содержать только русские буквы");
            valid = false;
        }

        // === Валидация email ===
        const emailValue = emailInput.value.trim();
        const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/; // Простейшая проверка на email-формат

        if (emailValue === "") {
            showError(emailInput, "Пожалуйста, введите email");
            valid = false;
        } else if (!emailRegex.test(emailValue)) {
            showError(emailInput, "Некорректный email");
            valid = false;
        }

        // === Валидация телефона ===
        const phoneValue = phoneInput.value.trim();
        const phoneDigits = phoneValue.replace(/\D/g, ""); // Оставляем только цифры

        if (phoneValue === "") {
            showError(phoneInput, "Пожалуйста, введите номер телефона");
            valid = false;
        } else if (phoneDigits.length !== 11 || !phoneDigits.startsWith("7")) {
            showError(phoneInput, "Некорректный номер телефона");
            valid = false;
        }

        // === Если все поля валидны, отправляем форму через fetch ===
        if (valid) {
            const formData = new FormData(form); // Сбор данных формы

            fetch("form.php", {
                method: "POST",
                body: formData
            })
                .then(response => response.text()) // Получаем текстовый ответ от сервера
                .then(data => {
                    // Прячем форму, показываем ответ от сервера
                    form.style.display = "none";
                    resultDiv.innerHTML = data;
                    resultDiv.style.display = "block";
                })
                .catch(() => {
                    // В случае ошибки при отправке запроса
                    resultDiv.innerHTML = "Произошла ошибка при отправке.";
                    resultDiv.style.display = "block";
                });
        }
    });

    // === Функция отображения ошибок для поля ===
    function showError(input, message) {
        input.classList.add("error"); // Добавляем стиль для выделения ошибки
        const section = input.closest(".section"); // Ищем родительский контейнер
        const errorMessage = section.querySelector(".error-message"); // Находим span с сообщением об ошибке

        if (errorMessage) {
            errorMessage.textContent = message; // Устанавливаем текст ошибки
            errorMessage.style.display = "block"; // Показываем сообщение
        }
    }
});
