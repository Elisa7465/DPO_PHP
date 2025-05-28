<?php
// Устанавливаем заголовок, что ответ будет в формате JSON
header('Content-Type: application/json');

// Проверяем, передан ли параметр "address" через GET-запрос и не пустой ли он
if (!isset($_GET['address']) || empty($_GET['address'])) {
    // Если адрес не передан, возвращаем ошибку в формате JSON и завершаем скрипт
    echo json_encode(['error' => 'Не передан адрес']);
    exit;
}

// Кодируем адрес для корректной передачи в URL (замена пробелов и спецсимволов)
$address = urlencode($_GET['address']);

// API-ключ для доступа к Яндекс Геокодеру — он нужен для авторизации запросов
$apiKey = getenv('YANDEX_API_KEY');

if (!$apiKey) {
    echo json_encode(['error' => 'YANDEX_API_KEY не задан']);
    exit;
}

// Формируем URL для запроса к API Яндекс Геокодера с нужными параметрами:
// format=json — формат ответа JSON
// apikey — ключ доступа
// geocode — адрес для геокодирования
$geocodeUrl = "https://geocode-maps.yandex.ru/1.x/?format=json&apikey=$apiKey&geocode=$address";

// Отправляем HTTP GET-запрос по сформированному URL и получаем ответ сервера
$response = file_get_contents($geocodeUrl);

// Проверяем, успешно ли получен ответ
if ($response === false) {
    // Если нет — возвращаем ошибку и завершаем выполнение
    echo json_encode(['error' => 'Ошибка при обращении к API Яндекс.Карт']);
    exit;
}

// Преобразуем JSON-ответ в ассоциативный массив PHP
$data = json_decode($response, true);

try {
    // Извлекаем из ответа основной объект с геоданными (первый результат)
    $geoObject = $data['response']['GeoObjectCollection']['featureMember'][0]['GeoObject'];

    // Получаем структурированный (полный) адрес в удобочитаемом формате
    $structuredAddress = $geoObject['metaDataProperty']['GeocoderMetaData']['Address']['formatted'];

    // Получаем координаты точки в формате "долгота широта"
    $coordinates = $geoObject['Point']['pos']; 

    // Разбиваем строку с координатами на отдельные значения долготы и широты
    list($lon1, $lat1) = explode(' ', $coordinates);

    // Формируем URL для поиска ближайшего метро рядом с этими координатами
    // kind=metro — фильтр по типу объекта метро
    // results=1 — ограничиваем результат одним ближайшим объектом
    $metroUrl = "https://geocode-maps.yandex.ru/1.x/?format=json&apikey=$apiKey&geocode=$lon1,$lat1&kind=metro&results=1";

    // Запрашиваем данные о ближайшем метро
    $metroResponse = file_get_contents($metroUrl);
    $metroData = json_decode($metroResponse, true);

    // По умолчанию, если метро не найдено, устанавливаем такие значения
    $nearestMetro = 'Не найдено';
    $metroCoordinates = '—';
    $distanceToMetro = '—';

    // Проверяем, есть ли найденные объекты метро в ответе
    if (!empty($metroData['response']['GeoObjectCollection']['featureMember'])) {
        // Получаем первый найденный объект метро
        $metroObject = $metroData['response']['GeoObjectCollection']['featureMember'][0]['GeoObject'];

        // Название ближайшего метро
        $nearestMetro = $metroObject['name'];

        // Координаты метро в формате "долгота широта"
        $metroCoordinates = $metroObject['Point']['pos'];

        // Разбиваем координаты метро на долготу и широту
        list($lon2, $lat2) = explode(' ', $metroCoordinates);

        // Вычисляем расстояние между адресом и метро (в метрах)
        $distanceToMetro = haversineDistance($lat1, $lon1, $lat2, $lon2);
    }

    // Формируем и выводим JSON-ответ с найденными данными
    echo json_encode([
        'structuredAddress' => $structuredAddress,
        'coordinates' => $coordinates,
        'nearestMetro' => $nearestMetro,
        'metroCoordinates' => $metroCoordinates,
        'distanceToMetro' => $distanceToMetro . ' м'  // добавляем единицу измерения
    ]);

} catch (Exception $e) {
    // Если что-то пошло не так при обработке данных, возвращаем ошибку с сообщением
    echo json_encode(['error' => 'Ошибка при обработке данных: ' . $e->getMessage()]);
}

// Функция для расчёта расстояния между двумя точками на Земле по координатам
// Использует формулу Haversine, результат в метрах
function haversineDistance($lat1, $lon1, $lat2, $lon2)
{
    $earthRadius = 6371000; // Радиус Земли в метрах

    // Переводим градусы в радианы, так как тригонометрические функции работают с радианами
    $lat1 = deg2rad((float)$lat1);
    $lon1 = deg2rad((float)$lon1);
    $lat2 = deg2rad((float)$lat2);
    $lon2 = deg2rad((float)$lon2);

    // Вычисляем разницу между координатами
    $deltaLat = $lat2 - $lat1;
    $deltaLon = $lon2 - $lon1;

    // Формула для вычисления "центрального угла" между точками
    $a = sin($deltaLat / 2) ** 2 + cos($lat1) * cos($lat2) * sin($deltaLon / 2) ** 2;
    $c = 2 * atan2(sqrt($a), sqrt(1 - $a));

    // Возвращаем расстояние по дуге окружности (расстояние между точками)
    return round($earthRadius * $c);
}
