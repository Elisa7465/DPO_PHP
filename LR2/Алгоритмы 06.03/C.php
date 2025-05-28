<?php

$banners = []; // Ассоциативный массив для хранения ID баннера и его веса
$total_weight = 0; // Общий вес всех баннеров

// Читаем входные данные построчно
while ($input = fgets(STDIN)) {
    $input = trim($input); // Удаляем пробелы и символы новой строки
    
    // Разбиваем строку по пробелу на ID баннера и его вес
    $parts = explode(" ", $input, 2);
    if (count($parts) !== 2) continue; // Пропускаем строки с некорректным форматом

    list($id, $weight) = $parts; // Извлекаем ID и вес баннера


    $banners[$id] = $weight; // Записываем вес баннера в массив
    $total_weight += $weight; // Увеличиваем общий вес
}

//Выводим 6 знаков после запятой 
foreach ($banners as $id => $weight) {
      echo $id, " ", round($weight / $total_weight, 6), "\n";
  }
