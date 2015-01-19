<?php

namespace donatj\AlikeColorFinder;

class CssColorExtractor {

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
	 * @param array $errors by reference
	 * @return \donatj\AlikeColorFinder\ColorEntry[]
	 */
	public function extractColors( &$errors = null ) {
		preg_match_all('/(?P<hex>#[0-9a-f]{3}(?:[0-9a-f]{3})?)|(?:(?P<func>(?:rgb|hsl)a?)\s*\((?P<params>[\s0-9.%,]+)\))/i', $this->subject, $results, PREG_SET_ORDER);

		/**
		 * @var $colors \donatj\AlikeColorFinder\ColorEntry[]
		 */
		$colors = [ ];
		$errors = [ ];
		foreach( $results as $result ) {
			$color = false;
			try {
				if( !empty($result['hex']) ) {
					$color = $this->factory->makeFromHexString($result['hex']);
				} else {
					$params = array_map('\floatval', array_map('\trim', explode(',', $result['params'])));

					switch( $result['func'] ) {
						case 'rgba':
							if( count($params) != 4 ) {
								throw new \Exception('Invalid param count');
							}

							$color = $this->factory->makeFromRgba($params[0], $params[1], $params[2], $params[3]);
							break;
						case 'rgb':
							if( count($params) != 3 ) {
								throw new \Exception('Invalid param count');
							}

							$color = $this->factory->makeFromRgb($params[0], $params[1], $params[2]);
							break;
						case 'hsla':
							if( count($params) != 4 ) {
								throw new \Exception('Invalid param count');
							}

							$color = $this->factory->makeFromHsla($params[0], $params[1] / 100, $params[2] / 100, $params[3]);
							break;
						case 'hsl':
							if( count($params) != 3 ) {
								throw new \Exception('Invalid param count');
							}

							$color = $this->factory->makeFromHsl($params[0], $params[1] / 100, $params[2] / 100);
							break;
						default:
							throw new \Exception('Not Implemented');
							continue;
					}
				}
			} catch(\Exception $e) {
				$errors[] = [
					'exception' => $e,
					'result'    => $result,
				];
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
