<?php
    $input = readline("task: ");
    $test = "тесты/$input";
    $script = "$input.php";

    foreach (glob("$test/*.dat") as $dat_file) {
        $ans_file = str_replace(".dat", ".ans", $dat_file);

        $output = shell_exec("php $script < $dat_file");
        $result = file_get_contents($ans_file);

        if ($output == $result) {
            echo basename($dat_file). " OK\n";
        } else {
            echo basename($dat_file). " FAILED\n";
        }
    }
?>