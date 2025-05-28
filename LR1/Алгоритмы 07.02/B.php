<?php
// Чтение входных данных
$input = [];
while ($line = fgets(STDIN)) {
    $input[] = trim($line);
}

// Обработка каждого адреса и вывод результата
foreach ($input as $short_address) {
    // Разделяем адрес по "::" на две половины
    $parts = explode("::", $short_address);
    //левая и правая половина массива разделяются по двоеточию
    $left_blocks = isset($parts[0]) ? explode(":", $parts[0]) : [];
    $right_blocks = isset($parts[1]) ? explode(":", $parts[1]) : [];

    // Вычисляем недостающие блоки
    $missing_blocks = 8 - count($left_blocks) - count($right_blocks);

    // Добавляем недостающие нулевые блоки
    //создаем массив длинной missing_blocks и помещаем его между левым и правым блоком
    $expanded_blocks = array_merge($left_blocks, array_fill(0, $missing_blocks, "0"), $right_blocks);

    // Расширяем каждый блок до 4 символов
    //array_map: Применяет указанную функцию к каждому элементу массива $expanded_blocks
    $full_address = array_map(function ($block) {
      //str_pad функция используемая для дополнения строки до определенной длины
        return str_pad($block, 4, "0", STR_PAD_LEFT);
    }, $expanded_blocks);

      // implode Объединяет элементы массива $full_address в строку, вставляя ":" между блоками.
      //PHP_EOL: Добавляет символ конца строки.
    echo implode(":", $full_address) . PHP_EOL;
}
?>
