<?php
    $dir = readline("task: ");
    $path = "тесты/$dir";
    $php_script = "$dir.php";

    foreach (glob("$path/*.dat") as $dat_file) {
        $ans_file = str_replace(".dat", ".ans", $dat_file);

        $output = shell_exec("php $php_script < $dat_file");
        $result = file_get_contents($ans_file);

        if ($dir != "C") {
            $output = str_replace(array("\r", "\n", "\t"), '', $output);
            $result = str_replace(array("\r", "\n", "\t"), '', $result);
    
            if ($output == $result) {
                echo basename($dat_file). " OK\n";
            } else {
                echo basename($dat_file). " FAIL\n";
            }
        } else {
            $output = explode("\n", $output);
            $result = explode("\n", $result);
            $flag = true;

            for ($i = 0; $i < count($output) - 1; $i += 1) {
                $output = explode(" ", $output[$i]);
                $result = explode(" ", $result[$i]);


                if (abs($output[1] - $result[1]) > 0.01) {
                    $flag = false;
                    break;
                }
            }

            if ($flag) {
                echo basename($dat_file) . " OK\n";
            } else {
                echo basename($dat_file) . " FAIL\n";
            }
        }
        
    }
?>
