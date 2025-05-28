<?php
$dir = readline("task: ");
$path = "тесты/$dir";
$php_script = "$dir.php";

if ($dir != "B") {
    // Обычная обработка .dat и .ans
    foreach (glob("$path/*.dat") as $dat_file) {
        $ans_file = str_replace(".dat", ".ans", $dat_file);

        $output = shell_exec("php $php_script < $dat_file");
        $result = file_get_contents($ans_file);

        if ($dir != "C") {
            $output = str_replace(["\r", "\n", "\t"], '', $output);
            $result = str_replace(["\r", "\n", "\t"], '', $result);

            if ($output === $result) {
                echo basename($dat_file) . " OK\n";
            } else {
                echo basename($dat_file) . " FAIL\n";
            }
        } else {
            $output = explode("\n", $output);
            $result = explode("\n", $result);
            $flag = true;

            for ($i = 0; $i < count($output) - 1; $i++) {
                $o = explode(" ", $output[$i]);
                $r = explode(" ", $result[$i]);

                if (abs($o[1] - $r[1]) > 0.01) {
                    $flag = false;
                    break;
                }
            }

            echo basename($dat_file) . ($flag ? " OK\n" : " FAIL\n");
        }
    }
} else {
    // === ЗАДАЧА B ===
    function canonicalizeXML($xmlPath) {
        $dom = new DOMDocument();
        $dom->preserveWhiteSpace = false;
        $dom->formatOutput = false;
        $dom->load($xmlPath);
        return $dom->C14N(); // Canonical form
    }

    foreach (glob("$path/*_sections.xml") as $sections_file) {
        $prefix = basename($sections_file, "_sections.xml");
        $products_file = "$path/{$prefix}_products.xml";
        $expected_output = "$path/{$prefix}_result.xml";
        $output_file = "$path/{$prefix}_output.xml";

        // Запуск пользовательского решения
        shell_exec("php $php_script \"$sections_file\" \"$products_file\" \"$output_file\"");

        // Проверка существования
        if (!file_exists($output_file)) {
            echo "$prefix FAIL (no output)\n";
            continue;
        }

        // Сравнение канонических XML
        $gen_str = canonicalizeXML($output_file);
        $exp_str = canonicalizeXML($expected_output);

        if ($gen_str === $exp_str) {
            echo "$prefix OK\n";
        } else {
            echo "$prefix FAIL\n";
        }
    }
}
?>
