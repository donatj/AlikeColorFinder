<?php

require('vendor/autoload.php');

$flags = new \donatj\Flags();

$tolerance =& $flags->uint('tolerance', 5, 'Computed Difference Tolerance - default 5');
$help      =& $flags->bool('help', false, 'Displays this message');

try {
	$flags->parse();
	if( $help ) {
		throw new Exception;
	}
} catch(Exception $e) {
	die($e->getMessage() . PHP_EOL . $flags->getDefaults() . PHP_EOL);
}

$subject = file_get_contents("php://stdin");

preg_match_all('/(?P<hex>#[0-9a-f]{3}(?:[0-9a-f]{3})?)|(?:(?P<func>(?:rgb|hsl)a?)\s*\((?P<params>[\s0-9.%,]+)\))/i', $subject, $results, PREG_SET_ORDER);


$colors = [ ];
foreach( $results as $result ) {
	$rgba = false;
	if( !empty($result['hex']) ) {
		$rgba = hex2rgba($result['hex']);

	} else {
		switch( $result['func'] ) {
			case 'rgba':
				$params = array_map('\trim', explode(',', $result['params']));
				if( count($params) != 4 ) {
					echo "Invalid param count\n";
					continue;
				}

				$rgba = [ ];
				list($rgba['r'], $rgba['g'], $rgba['b'], $rgba['a']) = $params;
				break;
			case 'rgb':
				$params = array_map('\trim', explode(',', $result['params']));
				if( count($params) != 3 ) {
					echo "Invalid param count\n";
					continue;
				}

				$rgba = [ 'a' => 1 ];
				list($rgba['r'], $rgba['g'], $rgba['b']) = $params;
				break;
			default:
				echo "{$result['func']} not implemented yet\n";
				continue;
		}
	}

	if( $rgba ) {
		$rgba = array_map('\floatval', $rgba);
		$key  = implode('|', $rgba);

		if( !isset($colors[$key]) ) {
			$colors[$key] = [
				'rgba'  => $rgba,
				'count' => 0,
				'orig'  => [ ],
			];
		}

		$colors[$key]['count']++;
		$colors[$key]['orig'][strtolower($result[0])] = $result[0];

	}
}

$colorStack = $colors;
while( count($colorStack) > 1 ) {
	$colorOne = array_pop($colorStack);

	foreach( $colorStack as $colorTwo ) {
		$diff = abs($colorOne['rgba']['r'] - $colorTwo['rgba']['r']) +
				abs($colorOne['rgba']['g'] - $colorTwo['rgba']['g']) +
				abs($colorOne['rgba']['b'] - $colorTwo['rgba']['b']) +
				(abs($colorOne['rgba']['a'] - $colorTwo['rgba']['a']) * 255);

		if( $diff < $tolerance ) {
//			see($colorOne, $colorTwo, $diff);
			$oneString = "rgba({$colorOne['rgba']['r']},{$colorOne['rgba']['g']},{$colorOne['rgba']['b']},{$colorOne['rgba']['a']})";
			$twoString = "rgba({$colorTwo['rgba']['r']},{$colorTwo['rgba']['g']},{$colorTwo['rgba']['b']},{$colorTwo['rgba']['a']})";

			$oneString = "({$colorOne['count']}) {$oneString}";
			$twoString = "({$colorTwo['count']}) {$twoString}";

			if( $colorOne['count'] > $colorTwo['count'] ) {
				$oneString = "*" . $oneString;
			} elseif( $colorOne['count'] > $colorTwo['count'] ) {
				$twoString = "* " . $twoString;
			}

			echo sprintf(" %30s %30s   diff: %d\n", $oneString, $twoString, $diff);

			$oneOrig = array_values($colorOne['orig']);
			$twoOrig = array_values($colorTwo['orig']);

			$max = max(count($oneOrig), count($twoOrig));

			for( $i = 0; $i < $max; $i++ ) {
				$oneString = !empty($oneOrig[$i]) ? $oneOrig[$i] : "";
				$twoString = !empty($twoOrig[$i]) ? $twoOrig[$i] : "";
				echo sprintf(" %30s %30s\n", $oneString, $twoString);
			}

			echo "\n";
		}
	}
}


function rgb_to_hsl( array $rgb ) {
	$r = $rgb['r'];
	$g = $rgb['g'];
	$b = $rgb['b'];

	$r /= 255;
	$g /= 255;
	$b /= 255;

	$h   = 0;
	$max = max($r, $g, $b);
	$min = min($r, $g, $b);

	$l = ($max + $min) / 2;
	$d = $max - $min;

	if( $d == 0 ) {
		$h = $s = 0; // achromatic
	} else {
		$s = $d / (1 - abs(2 * $l - 1));

		switch( $max ) {
			case $r:
				$h = 60 * fmod((($g - $b) / $d), 6);
				if( $b > $g ) {
					$h += 360;
				}
				break;

			case $g:
				$h = 60 * (($b - $r) / $d + 2);
				break;

			case $b:
				$h = 60 * (($r - $g) / $d + 4);
				break;
		}
	}

	return array( 'h' => $h, 's' => $s, 'l' => $l );
}

function hex2rgba( $hex ) {
	$hex = str_replace("#", "", $hex);

	if( strlen($hex) == 3 ) {
		$r = hexdec(substr($hex, 0, 1) . substr($hex, 0, 1));
		$g = hexdec(substr($hex, 1, 1) . substr($hex, 1, 1));
		$b = hexdec(substr($hex, 2, 1) . substr($hex, 2, 1));
	} else {
		$r = hexdec(substr($hex, 0, 2));
		$g = hexdec(substr($hex, 2, 2));
		$b = hexdec(substr($hex, 4, 2));
	}
	$rgb = array( 'r' => $r, 'g' => $g, 'b' => $b, 'a' => 1 );

	return $rgb; // returns an array with the rgb values
}