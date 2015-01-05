<?php

namespace donatj\AlikeColorFinder;

class AlikeColorFinder {

	protected $subject;

	public function __construct( $subject = "" ) {
		$this->subject = $subject;
	}

	/**
	 * @param int      $tolerance
	 * @param resource $stream
	 */
	public function displayDiff( $tolerance, $stream ) {
		$data = $this->getAlikeColorsWithinTolerance($tolerance);

		foreach( $data as $colorSet ) {
			/**
			 * @var $colorOne \donatj\AlikeColorFinder\ColorEntry
			 * @var $colorTwo \donatj\AlikeColorFinder\ColorEntry
			 */
			$colorOne = $colorSet['master'];
			foreach( $colorSet['children'] as $childEntry ) {
				$colorTwo = $childEntry['color'];

				$oneString = "({$colorOne->getInstanceTotal()}) {$colorOne->getRgbaString()}";
				$twoString = "({$colorTwo->getInstanceTotal()}) {$colorTwo->getRgbaString()}";

				if( $colorOne->getInstanceTotal() > $colorTwo->getInstanceTotal() ) {
					$oneString = "*" . $oneString;
				} elseif( $colorOne->getInstanceTotal() < $colorTwo->getInstanceTotal() ) {
					$twoString = "* " . $twoString;
				}

				fwrite($stream, sprintf(" %30s %30s   diff: %d\n", $oneString, $twoString, $childEntry['diff']));

				$oneOrig = $colorOne->getDistinctInstances();
				$twoOrig = $colorTwo->getDistinctInstances();

				$max = max(count($oneOrig), count($twoOrig));

				for( $i = 0; $i < $max; $i++ ) {
					$oneString = !empty($oneOrig[$i]) ? $oneOrig[$i] : "";
					$twoString = !empty($twoOrig[$i]) ? $twoOrig[$i] : "";

					fwrite($stream, sprintf(" %30s %30s\n", $oneString, $twoString));
				}

				fwrite($stream, "\n");
			}
		}
	}

	public function getAlikeColorsWithinTolerance( $tolerance ) {
		$output = [ ];

		$colors = $this->extractColors($this->subject);

		$colorStack = $colors;
		while( count($colorStack) > 1 ) {
			/**
			 * @var $colorOne \donatj\AlikeColorFinder\ColorEntry
			 * @var $colorTwo \donatj\AlikeColorFinder\ColorEntry
			 */
			$colorOne = array_pop($colorStack);
			$row      = [ ];

			foreach( $colorStack as $colorTwo ) {
				$diff = $colorOne->getAbsDiff($colorTwo);

				if( $diff < $tolerance ) {
					if( !$row ) {
						$row['master']   = $colorOne;
						$row['children'] = [ ];
					}
					$row['children'][] = [
						'diff'  => $diff,
						'color' => $colorTwo,
					];
				}
			}
			if( $row ) {
				$output[] = $row;
			}
		}

		return $output;
	}

	/**
	 * @param string $subject
	 * @return \donatj\AlikeColorFinder\ColorEntry[]
	 */
	private function extractColors( $subject ) {
		preg_match_all('/(?P<hex>#[0-9a-f]{3}(?:[0-9a-f]{3})?)|(?:(?P<func>(?:rgb|hsl)a?)\s*\((?P<params>[\s0-9.%,]+)\))/i', $subject, $results, PREG_SET_ORDER);

		/**
		 * @var $colors \donatj\AlikeColorFinder\ColorEntry[]
		 */
		$colors = [ ];
		foreach( $results as $result ) {
			$rgba = false;
			if( !empty($result['hex']) ) {
				$rgba = $this->hex2rgba($result['hex']);

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
					$colors[$key] = new ColorEntry($rgba['r'], $rgba['g'], $rgba['b'], $rgba['a']);;
				}

				$colors[$key]->addInstance($result[0]);

			}
		}

		return $colors;
	}


	private function rgb2hsl( array $rgb ) {
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

	private function hsla2rgba( $h, $s, $l, $a ) {
		$c = (1 - abs(2 * $l - 1)) * $s;
		$x = $c * (1 - abs(fmod(($h / 60), 2) - 1));
		$m = $l - ($c / 2);

		if( $h < 60 ) {
			$r = $c;
			$g = $x;
			$b = 0;
		} else if( $h < 120 ) {
			$r = $x;
			$g = $c;
			$b = 0;
		} else if( $h < 180 ) {
			$r = 0;
			$g = $c;
			$b = $x;
		} else if( $h < 240 ) {
			$r = 0;
			$g = $x;
			$b = $c;
		} else if( $h < 300 ) {
			$r = $x;
			$g = 0;
			$b = $c;
		} else {
			$r = $c;
			$g = 0;
			$b = $x;
		}

		$r = ($r + $m) * 255;
		$g = ($g + $m) * 255;
		$b = ($b + $m) * 255;

		return [ 'r' => $r, 'g' => $g, 'b' => $b, 'a' => $a ];
	}

	/**
	 * @param string $hex
	 * @return array
	 */
	private function hex2rgba( $hex ) {
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

}