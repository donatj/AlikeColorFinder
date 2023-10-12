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

	protected $colors = [
		'aliceblue'            => 'f0f8ff',
		'antiquewhite'         => 'faebd7',
		'aqua'                 => '00ffff',
		'aquamarine'           => '7fffd4',
		'azure'                => 'f0ffff',
		'beige'                => 'f5f5dc',
		'bisque'               => 'ffe4c4',
		'black'                => '000000',
		'blanchedalmond'       => 'ffebcd',
		'blue'                 => '0000ff',
		'blueviolet'           => '8a2be2',
		'brown'                => 'a52a2a',
		'burlywood'            => 'deb887',
		'cadetblue'            => '5f9ea0',
		'chartreuse'           => '7fff00',
		'chocolate'            => 'd2691e',
		'coral'                => 'ff7f50',
		'cornflowerblue'       => '6495ed',
		'cornsilk'             => 'fff8dc',
		'crimson'              => 'dc143c',
		'cyan'                 => '00ffff',
		'darkblue'             => '00008b',
		'darkcyan'             => '008b8b',
		'darkgoldenrod'        => 'b8860b',
		'darkgray'             => 'a9a9a9',
		'darkgreen'            => '006400',
		'darkkhaki'            => 'bdb76b',
		'darkmagenta'          => '8b008b',
		'darkolivegreen'       => '556b2f',
		'darkorange'           => 'ff8c00',
		'darkorchid'           => '9932cc',
		'darkred'              => '8b0000',
		'darksalmon'           => 'e9967a',
		'darkseagreen'         => '8fbc8f',
		'darkslateblue'        => '483d8b',
		'darkslategray'        => '2f4f4f',
		'darkturquoise'        => '00ced1',
		'darkviolet'           => '9400d3',
		'deeppink'             => 'ff1493',
		'deepskyblue'          => '00bfff',
		'dimgray'              => '696969',
		'dodgerblue'           => '1e90ff',
		'firebrick'            => 'b22222',
		'floralwhite'          => 'fffaf0',
		'forestgreen'          => '228b22',
		'fuchsia'              => 'ff00ff',
		'gainsboro'            => 'dcdcdc',
		'ghostwhite'           => 'f8f8ff',
		'gold'                 => 'ffd700',
		'goldenrod'            => 'daa520',
		'gray'                 => '808080',
		'grey'                 => '808080',
		'green'                => '008000',
		'greenyellow'          => 'adff2f',
		'honeydew'             => 'f0fff0',
		'hotpink'              => 'ff69b4',
		'indianred '           => 'cd5c5c',
		'indigo '              => '4b0082',
		'ivory'                => 'fffff0',
		'khaki'                => 'f0e68c',
		'lavender'             => 'e6e6fa',
		'lavenderblush'        => 'fff0f5',
		'lawngreen'            => '7cfc00',
		'lemonchiffon'         => 'fffacd',
		'lightblue'            => 'add8e6',
		'lightcoral'           => 'f08080',
		'lightcyan'            => 'e0ffff',
		'lightgoldenrodyellow' => 'fafad2',
		'lightgray'            => 'd3d3d3',
		'lightgreen'           => '90ee90',
		'lightpink'            => 'ffb6c1',
		'lightsalmon'          => 'ffa07a',
		'lightseagreen'        => '20b2aa',
		'lightskyblue'         => '87cefa',
		'lightslategray'       => '778899',
		'lightsteelblue'       => 'b0c4de',
		'lightyellow'          => 'ffffe0',
		'lime'                 => '00ff00',
		'limegreen'            => '32cd32',
		'linen'                => 'faf0e6',
		'magenta'              => 'ff00ff',
		'maroon'               => '800000',
		'mediumaquamarine'     => '66cdaa',
		'mediumblue'           => '0000cd',
		'mediumorchid'         => 'ba55d3',
		'mediumpurple'         => '9370db',
		'mediumseagreen'       => '3cb371',
		'mediumslateblue'      => '7b68ee',
		'mediumspringgreen'    => '00fa9a',
		'mediumturquoise'      => '48d1cc',
		'mediumvioletred'      => 'c71585',
		'midnightblue'         => '191970',
		'mintcream'            => 'f5fffa',
		'mistyrose'            => 'ffe4e1',
		'moccasin'             => 'ffe4b5',
		'navajowhite'          => 'ffdead',
		'navy'                 => '000080',
		'oldlace'              => 'fdf5e6',
		'olive'                => '808000',
		'olivedrab'            => '6b8e23',
		'orange'               => 'ffa500',
		'orangered'            => 'ff4500',
		'orchid'               => 'da70d6',
		'palegoldenrod'        => 'eee8aa',
		'palegreen'            => '98fb98',
		'paleturquoise'        => 'afeeee',
		'palevioletred'        => 'db7093',
		'papayawhip'           => 'ffefd5',
		'peachpuff'            => 'ffdab9',
		'peru'                 => 'cd853f',
		'pink'                 => 'ffc0cb',
		'plum'                 => 'dda0dd',
		'powderblue'           => 'b0e0e6',
		'purple'               => '800080',
		'rebeccapurple'        => '663399',
		'red'                  => 'ff0000',
		'rosybrown'            => 'bc8f8f',
		'royalblue'            => '4169e1',
		'saddlebrown'          => '8b4513',
		'salmon'               => 'fa8072',
		'sandybrown'           => 'f4a460',
		'seagreen'             => '2e8b57',
		'seashell'             => 'fff5ee',
		'sienna'               => 'a0522d',
		'silver'               => 'c0c0c0',
		'skyblue'              => '87ceeb',
		'slateblue'            => '6a5acd',
		'slategray'            => '708090',
		'snow'                 => 'fffafa',
		'springgreen'          => '00ff7f',
		'steelblue'            => '4682b4',
		'tan'                  => 'd2b48c',
		'teal'                 => '008080',
		'thistle'              => 'd8bfd8',
		'tomato'               => 'ff6347',
		'turquoise'            => '40e0d0',
		'violet'               => 'ee82ee',
		'wheat'                => 'f5deb3',
		'white'                => 'ffffff',
		'whitesmoke'           => 'f5f5f5',
		'yellow'               => 'ffff00',
		'yellowgreen'          => '9acd32',
	];

	public function __construct( $subject = "", ColorEntryFactory $colorEntryFactory = null ) {
		$this->subject = $subject;

		if( $colorEntryFactory !== null ) {
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
		$preDefined = implode('|', array_map(function( $color ) {
			return preg_quote($color, '/');
		}, array_keys($this->colors)));

		preg_match_all('/(?P<hex>\#[0-9a-f]{3}(?:[0-9a-f]{3})?)|
				(?:(?P<func>(?:rgb|hsl)a?)\s*\((?P<params>[\s0-9.%,]+)\))|
				(?:(?<=[\/\\\\()"\':,.;<>~!@#$%^&*|+=[\]{}`?\s\t])(?P<named>' . $preDefined . ')(?=[\/\\\\()"\':,.;<>~!@#$%^&*|+=[\]{}`?\s\t]))/xi', $this->subject, $results, PREG_SET_ORDER);

		/**
		 * @var \donatj\AlikeColorFinder\ColorEntry[] $colors
		 */
		$colors = [];
		$errors = [];
		foreach( $results as $result ) {
			$color = false;

			try {
				if( !empty($result['hex']) ) {
					$color = $this->factory->makeFromHexString($result['hex']);
				} elseif( !empty($result['named']) ) {
					$color = $this->factory->makeFromHexString(
						$this->colors[strtolower($result['named'])]
					);
				} else {
					$params = preg_split('/\s*(,|\s)\s*/', $result['params'], -1, PREG_SPLIT_NO_EMPTY);
					$params = array_map('\trim', $params);
					foreach( $params as &$param ) {
						if( substr($param, -1) === '%' ) {
							$param = ((float)substr($param, 0, -1)) / 100;
						} else {
							$param = (float)$param;
						}
					}
					unset($param);

					switch( $result['func'] ) {
						case 'rgba':
							if( count($params) !== 4 ) {
								throw new \Exception('Invalid param count');
							}

							$color = $this->factory->makeFromRgba($params[0], $params[1], $params[2], $params[3]);
							break;
						case 'rgb':
							if( count($params) !== 3 ) {
								throw new \Exception('Invalid param count');
							}

							$color = $this->factory->makeFromRgb($params[0], $params[1], $params[2]);
							break;
						case 'hsla':
							if( count($params) !== 4 ) {
								throw new \Exception('Invalid param count');
							}

							$color = $this->factory->makeFromHsla($params[0], $params[1], $params[2], $params[3]);
							break;
						case 'hsl':
							if( count($params) !== 3 ) {
								throw new \Exception('Invalid param count');
							}

							$color = $this->factory->makeFromHsl($params[0], $params[1], $params[2]);
							break;
						default:
							throw new \Exception('Not Implemented');
					}
				}
			} catch( \Exception $e ) {
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
