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
	 * @param int $tolerance
	 * @return array
	 */
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
				$params = array_map('\floatval', array_map('\trim', explode(',', $result['params'])));

				switch( $result['func'] ) {
					case 'rgba':
						if( count($params) != 4 ) {
							echo "Invalid param count\n";
							continue;
						}

						$color = $this->factory->makeFromRgba($params[0], $params[1], $params[2], $params[3]);
						break;
					case 'rgb':
						if( count($params) != 3 ) {
							echo "Invalid param count\n";
							continue;
						}

						$color = $this->factory->makeFromRgb($params[0], $params[1], $params[2]);
						break;
					case 'hsla':
						if( count($params) != 4 ) {
							echo "Invalid param count\n";
							continue;
						}

						$color = $this->factory->makeFromHsla($params[0], $params[1] / 100, $params[2] / 100, $params[3]);
						break;
					case 'hsl':
						if( count($params) != 3 ) {
							echo "Invalid param count\n";
							continue;
						}

						$color = $this->factory->makeFromHsl($params[0], $params[1] / 100, $params[2] / 100);
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