<?php

// === ЧТЕНИЕ И РАЗБОР JSON ===

// Считываем содержимое файла test.txt, содержащего JSON-запрос
$json = file_get_contents('test.txt');

// Преобразуем JSON-строку в ассоциативный массив
$data = json_decode($json, true);



// === ВСПОМОГАТЕЛЬНЫЕ ФУНКЦИИ ===

/**
 * Преобразует значение в SQL-подходящий вид (строка в кавычках, bool → true/false, null и т.д.)
 */
function escapeValue($value) {
    if (is_null($value)) return 'null';                             // null → 'null'
    if (is_bool($value)) return $value ? 'true' : 'false';          // true/false → 'true'/'false'
    if (is_string($value)) return "'" . addslashes($value) . "'";   // строка → 'строка'
    return $value;                                                  // число — без изменений
}

/**
 * Преобразует одно условие в SQL. Например, '<price' => 100 → 'price < 100'
 */
function buildCondition($key, $value) {
    // Разбиваем ключ: сначала операция (=, <, <=, >, >=, !) затем имя поля
    if (preg_match('/^(=|<|<=|>|>=|!)?(.*)$/', $key, $matches)) {
        $operator = $matches[1];               // операция сравнения (может быть пустой)
        $field = $matches[2];                  // имя поля
        $escaped = escapeValue($value);        // значение в SQL-совместимой форме

        // Определяем SQL-выражение по типу оператора
        switch ($operator) {
            case '=':
                // Равенство: учитываем тип значения
                return is_null($value) ? "$field is null" :
                       (is_bool($value) ? "$field is $escaped" : "$field = $escaped");

            case '!':
                // Неравенство: также учитываем null и boolean
                return is_null($value) ? "$field is not null" :
                       (is_bool($value) ? "$field is not $escaped" : "$field != $escaped");

            case '<':
            case '<=':
            case '>':
            case '>=':
                // Простые сравнения — универсальны
                return "$field $operator $escaped";

            case '':
                // Если нет оператора, выбираем по типу значения
                if (is_null($value)) return "$field is null";
                if (is_bool($value)) return "$field is $escaped";
                if (is_numeric($value)) return "$field = $escaped";
                return "$field like $escaped"; // строка → like
        }
    }

    return ''; // Если не распознали выражение — возвращаем пустую строку
}

/**
 * Рекурсивно обрабатывает все условия WHERE (включая and_*, or_* и вложенные уровни)
 */
function parseWhereWithLogic($where) {
    $result = []; // сюда собираем подусловия

    // Перебираем все ключи-значения в объекте where
    foreach ($where as $key => $value) {

        // Проверяем, логическая ли это группа (and/or, and_1, or_abc и т.д.)
        if (in_array($key, ['and', 'or']) || preg_match('/^(and|or)_/', $key, $m)) {
            $logic = strtoupper($m[1] ?? $key);  // Определяем тип объединения: AND или OR
            $subExprs = [];                      // сюда подусловия из группы

            // Перебираем вложенные условия
            foreach ($value as $subKey => $subVal) {
                if (is_array($subVal) && (in_array($subKey, ['and', 'or']) || preg_match('/^(and|or)_/', $subKey))) {
                    // Если внутри снова логическая группа — рекурсивно обрабатываем
                    $subExprs[] = '(' . parseWhereWithLogic([$subKey => $subVal]) . ')';
                } else {
                    // Обычное сравнение
                    $subExprs[] = buildCondition($subKey, $subVal);
                }
            }

            // Собираем блок условий с логическим оператором
            $result[] = '(' . implode(" $logic ", $subExprs) . ')';

        } else {
            // Простой случай: одиночное условие
            $result[] = buildCondition($key, $value);
        }
    }

    // Все верхнеуровневые блоки объединяются через AND
    return implode(' AND ', $result);
}



// === ФОРМИРОВАНИЕ ОСНОВНЫХ ЧАСТЕЙ SQL ===

// Проверяем наличие обязательного поля "from"
if (empty($data['from'])) {
    echo "Ошибка: отсутствует поле 'from'" . PHP_EOL;
    exit(1);
}

// SELECT: если указаны поля — формируем список, иначе выбираем все (*)
$select = '*';
if (!empty($data['select']) && is_array($data['select'])) {
    $select = implode(', ', $data['select']);
}

// FROM: имя таблицы из поля "from"
$from = $data['from'];

// WHERE: если указан блок условий, обрабатываем через функцию
$whereClause = '';
if (!empty($data['where']) && is_array($data['where'])) {
    $whereSQL = parseWhereWithLogic($data['where']);
    if ($whereSQL) {
        $whereClause = "where $whereSQL";
    }
}

// ORDER BY: если указана сортировка, берём только первое поле
$orderClause = '';
if (!empty($data['order']) && is_array($data['order'])) {
    foreach ($data['order'] as $field => $dir) {
        $orderClause = "order by $field " . strtoupper($dir); // ASC или DESC
        break;
    }
}

// LIMIT: ограничение количества строк
$limitClause = '';
if (!empty($data['limit']) && is_numeric($data['limit'])) {
    $limitClause = "limit " . intval($data['limit']);
}



// === СБОРКА И ВЫВОД ГОТОВОГО SQL ===

// Сначала SELECT и FROM
$sql = "select $select\nfrom $from";

// Добавляем блоки WHERE, ORDER, LIMIT при наличии
if ($whereClause) $sql .= "\n$whereClause";
if ($orderClause) $sql .= "\n$orderClause";
if ($limitClause) $sql .= "\n$limitClause";

// Завершаем точкой с запятой
$sql .= ';';

// Выводим готовый SQL-запрос
echo $sql . PHP_EOL;
