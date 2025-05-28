<?php
// Читаем содержимое файла
$filename = "example.txt";
//записываем содержимре файла в переменную text
$text = file_get_contents($filename);

// Регулярное выражение для поиска ссылок
$pattern = '/http:\/{2}asozd\.duma\.gov\.ru\/main\.nsf\/\(Spravka\)\?OpenAgent&RN=(\d+-\d+)&(\d+)/';
//находим все значения psttern в text и заменяем их вызовом функции
$text = preg_replace_callback($pattern, function($matches) { 
    //заменяем складывая через . две строчки ссылку+номер законо проекта 
       return "http://sozd.parlament.gov.ru/bill/".$matches[1]; 
   }, $text);
//записываем новый text в файл, применяя все зменения
file_put_contents($filename, $text);
echo "Замена завершена";
?>
