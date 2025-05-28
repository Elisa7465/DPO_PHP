<?php
// Создаём пустой массив для хранения данных о баннерах
$banners = [];

while ($input = fgets(STDIN)) {
    // Читаем входные данные построчно
    $input = trim($input);  
    // Разделяем ID и дату
    $parts =explode("    ", $input); 
    // Если в строке не два элемента, пропускаем её
    if (count($parts) !== 2) continue; 

    // Распаковываем массив в переменные: $id - идентификатор баннера, $time - дата и время показа
    list($id, $time) = $parts;
    
    // Преобразуем строку с датой в объект DateTime
    $datetime = DateTime::createFromFormat('d.m.Y H:i:s', $time);
    if (!$datetime) continue; // Пропускаем строки с некорректной датой
    
    // Если баннер с таким ID ещё не встречался, инициализируем его в массиве
    if (!isset($banners[$id])) {
        $banners[$id] = ['count' => 0, 'last_time' => null];
    }
    
    // Увеличиваем счётчик показов для данного баннера
    $banners[$id]['count']++;
    
    // Обновляем дату Если это первый показ баннера или новый показ произошёл позже предыдущего
    if ($banners[$id]['last_time'] === null || $datetime->getTimestamp() > $banners[$id]['last_time']->getTimestamp()) {
        $banners[$id]['last_time'] = $datetime;
    }
}
// Выводим отсортированные данные по каждому баннеру
foreach ($banners as $id => $data) {
    echo "{$data['count']} $id " . $data['last_time']->format('d.m.Y H:i:s') . "\n";
}
