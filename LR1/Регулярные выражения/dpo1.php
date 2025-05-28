<?php
echo "Введите строку: ";
$input = trim(fgets(STDIN)); // Считываем строку из консоли

// Регулярное выражение для поиска числа в кавычках и следующего за ним (не обязательного) числа
$pattern = "/'(\d+)'(\d+)?/";

//Находим все выражения в строке input которые соответствуют psttern и вместо замены передаем в функцию 
$input = preg_replace_callback($pattern, function($matches) {
   //умножаем число внутри кавычек (matches[1]) на 2

    $firstNum = $matches[1] * 2;
    
    // Проверяем, есть ли второе число и следуют ли за ним кавычки
    if (isset($matches[2]) && isset($matches[0][strlen($matches[0]) - 1]) && $matches[0][strlen($matches[0]) - 1] === "'") {
        $secondNum = $matches[2] * 2;
        // оборачиваем полученное в кавычки и возвращаем в preg_replace_callback которая вставит его вместо найденного ранее значения
        return "'$firstNum'$secondNum'";
    }
//если после второго числа нет кавычки оборачиваем только matches[1] в кавычки и добавляем найденное после него число, потому что взяли его ранее
    return "'$firstNum'" . (isset($matches[2]) ? $matches[2] : ""); 
}, $input);

echo "Результат: $input\n";
?>