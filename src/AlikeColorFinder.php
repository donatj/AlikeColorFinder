<?php

namespace donatj\AlikeColorFinder;

class AlikeColorFinder {

	/**
	 * @var string
	 */
	protected $subject;

	/**
	 * @var \donatj\AlikeColorFinder\ColorEntryFactory
	 */
	protected $factory;

	public function __construct( $subject = "", ColorEntryFactory $colorEntryFactory = null ) {
		$this->subject = $subject;

		if( !is_null($colorEntryFactory) ) {
			$this->factory = $colorEntryFactory;
		} else {
			$this->factory = new ColorEntryFactory();
		}
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
			$color = false;
			if( !empty($result['hex']) ) {
				$color = $this->factory->makeFromHexString($result['hex']);
			} else {
				switch( $result['func'] ) {
					case 'rgba':
						$params = array_map('\floatval', array_map('\trim', explode(',', $result['params'])));
						if( count($params) != 4 ) {
							echo "Invalid param count\n";
							continue;
						}

						$color = $this->factory->makeFromRgba($params[0], $params[1], $params[2], $params[3]);
						break;
					case 'rgb':
						$params = array_map('\floatval', array_map('\trim', explode(',', $result['params'])));
						if( count($params) != 3 ) {
							echo "Invalid param count\n";
							continue;
						}

						$color = $this->factory->makeFromRgb($params[0], $params[1], $params[2]);
						break;
					default:
						echo "{$result['func']} not implemented yet\n";
						continue;
				}
			}


			if( $color ) {
				$key = md5($color->getRgbaString());

				if( !isset($colors[$key]) ) {
					$colors[$key] = $color;
				}

				$colors[$key]->addInstance($result[0]);

			}
		}

		return $colors;
	}

}