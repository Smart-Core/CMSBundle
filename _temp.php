<?php
function sc_is_absolute_path($path)
{
    return (strpos($path, '/') === 0 or strpos($path, ':') === 1) ? true : false;
}

function sc_profile($Logger = null, $precision = 3)
{
    $exec_time = microtime(true) - SMARTCORE_START_TIME;

    echo '<hr />Execution time: <b>', round($exec_time, $precision) * 1000 , '</b> ms',
        '. Memory usage <b>' , round((memory_get_usage() - SMARTCORE_START_MEMORY_USAGE) / 1024 / 1024, 2), '</b> MB (<b>',
        round((memory_get_peak_usage(true) - SMARTCORE_START_MEMORY_USAGE) / 1024 / 1024, 2), '</b> peak).',
        ' Included files: <b>' . count(get_included_files()) . "</b>.\n";

    if (!is_null($Logger) and is_object($Logger)) {
        echo '<br />DB query count: <b>' . $Logger->currentQuery . '</b>';

        $queries_time = 0;
        foreach ($Logger->queries as $value) {
            $queries_time += $value['executionMS'];
        }

        $delta = round($queries_time * 100 / $exec_time, 2);
        echo ' (summary execution time: <b>' . round($queries_time, $precision) * 1000 . "</b> ms, $delta %)." . "\n";
    }
}

function sc_dump($input, $title = false, $to_file = false)
{
    if (isset($input)) {
        if ($to_file) {
            $handle = fopen('e:\debug.txt', 'a+');
            if($title != false) {
                fwrite($handle, $title . "\n");
            }
            fwrite($handle, print_r($input, true));
            fwrite($handle, "\n============\n");
            fclose($handle);
        } else {
            ob_start();
            echo "\n<pre>";

            if($title != false) {
                echo "<hr><b>$title :</b> <br />";
            }

            print_r($input);

            if ($input === false) {
                echo "установлен в <b>false</b>";
            }

            echo "</pre>\n";
            $output = ob_get_clean();
            $output = str_ireplace('    ', '   ', $output);
            echo($output);
        }
    } elseif ($input === null) {
        if (!$to_file) {
            echo "\n<pre>\n<b>$title</b> установлен в <b>null</b>.\n</pre>\n";
        }
    } else {
        if (!$to_file) {
            echo "\n<pre>\n<b>$title</b> не установлен.\n</pre>\n";
        }
    }
}

function sc_redirect($url = null)
{
    $str = (null == $url) ? $_SERVER['REQUEST_URI'] : $url;
    header('Location: ' . $str);
    exit;
}
