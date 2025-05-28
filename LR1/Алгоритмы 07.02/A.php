<?php
// Считываем количество ставок
$n = intval(fgets(STDIN));
$bets = [];
//Информация ос тавках
for ($i = 0; $i < $n; $i++) {
    list($ai, $si, $ri) = explode(" ", trim(fgets(STDIN)));
    //Добавляем новый элемент в массив ставок
    $bets[] = [
        //Номер игры
        'game_id_bets' => intval($ai),
        //ставка
        'amount' => intval($si),
        //на что ставим
        'result_bets' => $ri
    ];
}

// Считываем количество игр
$m = intval(fgets(STDIN));
//Массив в котором игра хранить по своему номеру
$game_map = [];
for ($i = 0; $i < $m; $i++) {
    list($bj, $cj, $dj, $kj, $tj) = explode(" ", trim(fgets(STDIN)));
    //Используем идентификатор игры как ключ в ассоциативном массиве.
    $game_map[intval($bj)] = [
        'left_coeff' => floatval($cj),
        'right_coeff' => floatval($dj),
        'draw_coeff' => floatval($kj),
        'result_games' => $tj
    ];
}
//Итоговая сумма выиграша
$total_win = 0;
//Проходимся по каждой ставке
foreach ($bets as $bet) {
    //Номер игры для текущей ставки
    $game_id = $bet['game_id_bets'];
    if (isset($game_map[$game_id])) { // Проверяем, существует ли игра с таким идентификатором
        $game = $game_map[$game_id];
        //Перебираем все возможные вероятности выиграша и прибовляем их к итоговой сумме
        if ($bet['result_bets'] === $game['result_games']) {
            if ($bet['result_bets'] === "L") {
                $total_win += $bet['amount'] * ($game['left_coeff'] - 1);
            } elseif ($bet['result_bets'] === "R") {
                $total_win += $bet['amount'] * ($game['right_coeff'] - 1);
            } elseif ($bet['result_bets'] === "D") {
                $total_win += $bet['amount'] * ($game['draw_coeff'] - 1);
            }
            //Если ни один из выиграшей не был осуществлен то вычитаем сумму ставки из итогового выиграша
        } else {
            $total_win -= $bet['amount'];
        }
    } 
}

echo $total_win;
?>
