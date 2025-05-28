<?php

// Читаем входные данные
$sections = [];
while ($line = fgets(STDIN)) {
    $line = trim($line);
    if (empty($line)) continue;

    // Разбиваем строку на 4 части: ID, название, left key, right key
    list($id, $name, $left, $right) = explode(" ", $line);

    // Добавляем данные в массив sections
    $sections[] = [
        'id' => (int)$id,
        'name' => $name,
        'left' => (int)$left,
        'right' => (int)$right,
    ];
}

// Функция для сортировки разделов по left key
function cmp($a, $b) {
    return $a['left'] <=> $b['left'];
}

// Сортируем разделы
usort($sections, "cmp");

// Вычисляем уровень вложенности
$stack = [];
foreach ($sections as $section) {
    // Убираем из стека элементы, которые не могут быть предками
    while (!empty($stack) && end($stack)['right'] < $section['left']) {
        array_pop($stack);
    }

    // Уровень вложенности равен текущему размеру стека, столько предков у узла
    $level = count($stack);
    echo str_repeat("-", $level) . $section['name'] . PHP_EOL;

    // Добавляем текущий узел в стек
    $stack[] = $section;
}
