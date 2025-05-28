<?php

// Считываем количество рейсов из первой строки ввода
$n = intval(trim(fgets(STDIN)));  // Преобразуем строку в целое число

// Запускаем цикл на n итераций, по количеству рейсов
for ($i = 0; $i < $n; $i++) {
    // Считываем строку с описанием одного рейса и убираем пробелы по краям
    $line = trim(fgets(STDIN));

    // Разделяем строку на четыре части: время вылета, часовой пояс вылета, время прибытия, часовой пояс прибытия
    list($departure_time, $departure_tz, $arrival_time, $arrival_tz) = explode(" ", $line);

    // Создаем объект времени вылета в формате d.m.Y_H:i:s, указываем временную зону UTC (будем учитывать сдвиг отдельно)
    $departure = DateTime::createFromFormat('d.m.Y_H:i:s', $departure_time, new DateTimeZone("UTC"));

    // То же самое для времени прибытия
    $arrival = DateTime::createFromFormat('d.m.Y_H:i:s', $arrival_time, new DateTimeZone("UTC"));

    // Переводим часовой пояс вылета в секунды (1 час = 3600 секунд)
    $departure_offset = (int)$departure_tz * 3600;

    // Переводим часовой пояс прибытия в секунды
    $arrival_offset = (int)$arrival_tz * 3600;

    // Получаем отметку времени (timestamp) в UTC и корректируем с учетом часового пояса вылета
    $departure_utc = $departure->getTimestamp() - $departure_offset;

    // То же самое для времени прибытия
    $arrival_utc = $arrival->getTimestamp() - $arrival_offset;

    // Вычисляем длительность полета как разницу между временем прибытия и вылета в UTC
    $flight_time = $arrival_utc - $departure_utc;

    // Выводим время полета в секундах
    echo $flight_time . "\n";
}
