<?php
// Получаем пути к входным и выходному файлу из аргументов командной строки
$sections_file = $argv[1];       // Путь к XML-файлу с разделами
$products_file = $argv[2];       // Путь к XML-файлу с товарами
$output_file = $argv[3];         // Путь, куда сохранить результат

// Загружаем XML с разделами
$sectionsXml = simplexml_load_file($sections_file);  // Чтение и парсинг файла с разделами
if ($sectionsXml === false) {                         // Если не удалось загрузить файл
    fwrite(STDERR, "Ошибка загрузки файла разделов: $sections_file\n"); // Сообщаем об ошибке
    exit(1);                                          // Завершаем выполнение с кодом ошибки
}

// Загружаем XML с товарами
$productsXml = simplexml_load_file($products_file);   // Чтение и парсинг файла с товарами
if ($productsXml === false) {                         // Если файл не найден или невалидный
    $productsXml = new SimpleXMLElement('<Товары/>'); // Создаем пустую структуру для обработки без ошибок
}

// Индексируем разделы для быстрого доступа по UUID
$sections = [];                                       // Ассоциативный массив для хранения разделов
foreach ($sectionsXml->Раздел as $section) {         // Проходим по каждому разделу в XML
    $id = (string)$section->Ид;                      // Преобразуем UUID раздела в строку
    $sections[$id] = [                               // Сохраняем информацию о разделе
        'Ид' => $id,
        'Наименование' => (string)$section->Наименование,
        'Товары' => []                              // Список товаров, который пока пуст
    ];
}

// Привязываем каждый товар к соответствующему разделу
foreach ($productsXml->Товар as $product) {          // Проходим по каждому товару в XML
    $productId = (string)$product->Ид;               // UUID товара
    $productName = (string)$product->Наименование;   // Название товара
    $productArt = (string)$product->Артикул;         // Артикул товара

    foreach ($product->Разделы->ИдРаздела as $sectionId) { // Товар может принадлежать нескольким разделам
        $sectionId = (string)$sectionId;             // Преобразуем ID раздела в строку

        if (isset($sections[$sectionId])) {          // Если раздел существует в загруженных
            // Добавляем товар в массив "Товары" этого раздела
            $sections[$sectionId]['Товары'][] = [
                'Ид' => $productId,
                'Наименование' => $productName,
                'Артикул' => $productArt
            ];
        }
    }
}

// Создаем выходной XML-документ <ЭлементыКаталога>
$outputXml = new SimpleXMLElement('<ЭлементыКаталога/>');

// Добавляем корневой узел <Разделы>
$sectionsNode = $outputXml->addChild('Разделы');

// Формируем структуру XML из массива $sections
foreach ($sections as $sectionData) {
    $sectionNode = $sectionsNode->addChild('Раздел');                     // <Раздел>
    $sectionNode->addChild('Ид', $sectionData['Ид']);                    //     <Ид>
    $sectionNode->addChild('Наименование', $sectionData['Наименование']); // <Наименование>
    $productsNode = $sectionNode->addChild('Товары');                    //     <Товары>

    // Добавляем каждый товар в соответствующий раздел
    foreach ($sectionData['Товары'] as $productData) {
        $productNode = $productsNode->addChild('Товар');                 //         <Товар>
        $productNode->addChild('Ид', $productData['Ид']);               //             <Ид>
        $productNode->addChild('Наименование', $productData['Наименование']); //  <Наименование>
        $productNode->addChild('Артикул', $productData['Артикул']);     //             <Артикул>
    }
}

// Сохраняем сгенерированный XML в файл, указанный в $output_file
$outputXml->asXML($output_file);
?>
