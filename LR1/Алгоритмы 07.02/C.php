<?php
// Чтение входных данных
$input = [];
while ($line = fgets(STDIN)) {
    $input[] = trim($line);
}

// Обработка входных данных
foreach ($input as $line) {
    // Разбор строки на значение и правило
    $pattern = '/<(.+?)?>\s(\w)(?:\s(-?\d+)\s(-?\d+))?/';
    //Проверяет строку на соответствие шаблону и сохраняет совпадения в массив $matches
    if (!preg_match($pattern, $line, $matches)) {
        echo "FAIL\n";
        continue;
    }

    // Извлечение данных
    $value = isset($matches[1]) ? $matches[1] : ''; // Значение из треугольных скобок
    $rule = $matches[2]; // Тип валидации
    $param1 = isset($matches[3]) ? intval($matches[3]) : null; // Параметр 1
    $param2 = isset($matches[4]) ? intval($matches[4]) : null; // Параметр 2

    // Проверка правила
    $isValid = false;
    switch ($rule) {
        case 'S': // Строка
            if ($param1 !== null && $param2 !== null) {
                //определяем длину строки
                $length = strlen($value);
                //проверям что длина cоотвествует параметрам
                if ($length >= $param1 && $length <= $param2) {
                    $isValid = true;
                }
            }
            break;
        case 'N': // Целое число
            if ($param1 !== null && $param2 !== null) {
                if (preg_match('/^-?\d+$/', $value)) {
                    $number = intval($value);
                    //проверяет что число соответствует параметрам
                    if ($number >= $param1 && $number <= $param2) {
                        $isValid = true;
                    }
                }
            }
            break;
        case 'P': // Номер телефона
            $pattern_number = '/^\+7\s\(\d{3}\)\s\d{3}-\d{2}-\d{2}$/';
            if (preg_match($pattern_number, $value)) {
                $isValid = true;
            }
            break;
        case 'D': // Дата и время
            //Если строка совпадает с шаблоном дата будет извлечена в массив $dateMatches.
            $pattern_date = '/^(\d{1,2})\.(\d{1,2})\.(\d{4})\s(\d{1,2}):(\d{2})$/';
            if (preg_match($pattern_date, $value, $dateMatches)) {
                $day = intval($dateMatches[1]);
                $month = intval($dateMatches[2]);
                $year = intval($dateMatches[3]);
                $hour = intval($dateMatches[4]);
                $minute = intval($dateMatches[5]);

                if ($year < 100) {
                    $year += 2000; // Для двухзначного года
                }

                if (
                    checkdate($month, $day, $year) && // Проверка корректной даты
                    $hour >= 0 && $hour <= 23 && // Часы в диапазоне 0–23
                    $minute >= 0 && $minute <= 59 // Минуты в диапазоне 0–59
                ) {
                    $isValid = true;
                }
            }
            break;
        case 'E': // Email
            $pattern_email = '/^[a-zA-Z0-9][a-zA-Z0-9_]{3,29}@[a-zA-Z]{2,30}\.[a-z]{2,10}$/';
            if (preg_match($pattern_email, $value)) {
                $isValid = true;
            }
            break;
        default:
            $isValid = false;
    }

    // Вывод результата
    echo $isValid ? "OK" : "FAIL";
    echo PHP_EOL;
}
?>
