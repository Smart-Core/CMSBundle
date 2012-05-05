<?php

function cmf_is_absolute_path($path)
{
	return (strpos($path, '/') === 0 or strpos($path, ':') === 1) ? true : false;
}

function cmf_profile($container = null)
{
	echo '<hr />', memory_get_usage(), ' (', memory_get_peak_usage(true), ')<br />', microtime(true) - SMARTCORE_START_TIME , "<br />\n";
	
	if ($container and $container->has('db.logger')) {
		$logger = $container->get('db.logger');
		echo '<br />Запросов в БД: <b>' . $logger->currentQuery . '</b>';
		
		$total_time = 0;
		foreach ($logger->queries as $value) {
			$total_time += $value['executionMS'];
		}
		
		echo ', суммарное время исполнения: <b>' . $total_time . '</b>';
		
//		cmf_dump($logger);
//		cmf_dump($logger->queries);
	}
}

function cmf_dump($input, $title = false, $to_file = false)
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
