<?php

function cmf_is_absolute_path($path)
{
	return (strpos($path, '/') === 0 or strpos($path, ':') === 1) ? true : false;
}

function cmf_profile($container = null)
{
	echo '<hr /><b>', round(microtime(true) - SMARTCORE_START_TIME, 3), '</b> sec', 
		', <b>' , memory_get_usage(), '</b> current usage bytes, (<b>', memory_get_peak_usage(true), '</b> peak usage bytes )', 
		"\n";
	
	if ($container and $container->has('db.logger')) {
		$logger = $container->get('db.logger');
		echo '<br />DB query count: <b>' . $logger->currentQuery . '</b>';
		
		$total_time = 0;
		foreach ($logger->queries as $value) {
			$total_time += $value['executionMS'];
		}
		
		echo ' (summary execution time: <b>' . round($total_time, 3) . '</b> sec)' . "\n";
		
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
