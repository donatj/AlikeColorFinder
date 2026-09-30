<?php

namespace donatj\AlikeColorFinder;

class CssColorExtractor {

	protected string $subject;

	protected ColorEntryFactory $factory;

	/** @var array<string, string> */
	protected array $colors = [
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
		'darkgrey'             => 'a9a9a9',
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
		'darkslategrey'        => '2f4f4f',
		'darkturquoise'        => '00ced1',
		'darkviolet'           => '9400d3',
		'deeppink'             => 'ff1493',
		'deepskyblue'          => '00bfff',
		'dimgray'              => '696969',
		'dimgrey'              => '696969',
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
		'indianred'            => 'cd5c5c',
		'indigo'               => '4b0082',
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
		'lightgrey'            => 'd3d3d3',
		'lightgreen'           => '90ee90',
		'lightpink'            => 'ffb6c1',
		'lightsalmon'          => 'ffa07a',
		'lightseagreen'        => '20b2aa',
		'lightskyblue'         => '87cefa',
		'lightslategray'       => '778899',
		'lightslategrey'       => '778899',
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
		'slategrey'            => '708090',
		'snow'                 => 'fffafa',
		'springgreen'          => '00ff7f',
		'steelblue'            => '4682b4',
		'tan'                  => 'd2b48c',
		'teal'                 => '008080',
		'thistle'              => 'd8bfd8',
		'tomato'               => 'ff6347',
		'transparent'          => '00000000',
		'turquoise'            => '40e0d0',
		'violet'               => 'ee82ee',
		'wheat'                => 'f5deb3',
		'white'                => 'ffffff',
		'whitesmoke'           => 'f5f5f5',
		'yellow'               => 'ffff00',
		'yellowgreen'          => '9acd32',
	];

	/**
	 * CIEDE2000 raises chroma to the seventh power, so values above this
	 * coordinate magnitude cannot be compared reliably with PHP floats.
	 */
	protected float $maxComparableXyzComponent = 1.0e30;

	public function __construct( string $subject = "", ?ColorEntryFactory $colorEntryFactory = null ) {
		$this->subject = $subject;

		if( $colorEntryFactory !== null ) {
			$this->factory = $colorEntryFactory;
		} else {
			$this->factory = new ColorEntryFactory;
		}
	}

	/**
	 * @param list<array{exception: \Exception, result: array<int|string, string>}> $errors by reference
	 * @return array<string, \donatj\AlikeColorFinder\ColorEntry>
	 */
	public function extractColors( array &$errors ): array {
		$asciiCaseInsensitive = function ( string $identifier ): string {
			return implode('', array_map(function ( string $character ): string {
				if( $character >= 'a' && $character <= 'z' ) {
					return '[' . $character . strtoupper($character) . ']';
				}

				return $character;
			}, str_split($identifier)));
		};

		$preDefined = implode('|', array_map(function ( string $color ) use ( $asciiCaseInsensitive ): string {
			return $asciiCaseInsensitive(preg_quote($color, '/'));
		}, array_keys($this->colors)));

		// CSS <number> allows an optional sign and scientific notation.
		// @see https://www.w3.org/TR/css-syntax-3/#consume-a-number
		$number                = '[+-]?(?:\d+\.\d+|\d+|\.\d+)(?:[eE][+-]?\d+)?';
		$percentage            = $number . '%';
		$component             = $number . '%?';
		// CSS <hue> is a number in degrees or an angle dimension.
		// @see https://www.w3.org/TR/css-color-4/#hue-syntax
		$angleUnits            = implode('|', array_map($asciiCaseInsensitive, [ 'deg', 'grad', 'rad', 'turn' ]));
		$hue                   = $number . '(?:' . $angleUnits . ')?';
		$modernParams          = $component . '(?:\s+' . $component . '){2}(?:\s*\/\s*' . $component . ')?';
		$legacyRgbParams       = '(?:' .
			$number . '\s*,\s*' . $number . '\s*,\s*' . $number .
			'|' .
			$percentage . '\s*,\s*' . $percentage . '\s*,\s*' . $percentage .
			')(?:\s*,\s*' . $component . ')?';
		$modernHueFirstParams = $hue . '\s+' . $component . '\s+' . $component . '(?:\s*\/\s*' . $component . ')?';
		$legacyHueFirstParams = $hue . '\s*,\s*' . $percentage . '\s*,\s*' . $percentage . '(?:\s*,\s*' . $component . ')?';
		$modernHueLastParams  = $component . '\s+' . $component . '\s+' . $hue . '(?:\s*\/\s*' . $component . ')?';
		// A CSS identifier may contain any non-ASCII code point.
		$functionStart        = '(?<![\w\x{80}-\x{10FFFF}\\\-])';

		$rgbFunctions = $asciiCaseInsensitive('rgb') . '|' . $asciiCaseInsensitive('rgba');
		$hslFunctions = $asciiCaseInsensitive('hsl') . '|' . $asciiCaseInsensitive('hsla');
		$labFunctions = $asciiCaseInsensitive('lab') . '|' . $asciiCaseInsensitive('oklab');
		$lchFunctions = $asciiCaseInsensitive('lch') . '|' . $asciiCaseInsensitive('oklch');
		$colorSpaces  = implode('|', array_map($asciiCaseInsensitive, [
			'srgb-linear', 'srgb', 'display-p3-linear', 'display-p3', 'a98-rgb',
			'prophoto-rgb', 'rec2020', 'xyz-d50', 'xyz-d65', 'xyz',
		]));

		preg_match_all('/(?P<hex>\#[0-9a-fA-F]{3}(?:[0-9a-fA-F](?:[0-9a-fA-F]{2}(?:[0-9a-fA-F]{2})?)?)?(?![\w-]))|
(?:' . $functionStart . '(?P<func>' . $rgbFunctions . ')\s*\(\s*(?P<params>(?:' . $modernParams . '|' . $legacyRgbParams . '))\s*\))|
(?:' . $functionStart . '(?P<hue_func>' . $hslFunctions . ')\s*\(\s*(?P<hue_params>(?:' . $modernHueFirstParams . '|' . $legacyHueFirstParams . '))\s*\))|
(?:' . $functionStart . '(?P<func2>' . $labFunctions . ')\s*\(\s*(?P<params2>' . $modernParams . ')\s*\))|
(?:' . $functionStart . '(?P<hue_func2>' . $lchFunctions . ')\s*\(\s*(?P<hue_params2>' . $modernHueLastParams . ')\s*\))|
(?:' . $functionStart . '(?P<hue_func3>' . $asciiCaseInsensitive('hwb') . ')\s*\(\s*(?P<hue_params3>' . $modernHueFirstParams . ')\s*\))|
(?:' . $functionStart . '(?P<color_func>' . $asciiCaseInsensitive('color') . ')\s*\(\s*(?P<color_space>' . $colorSpaces . ')\s+(?P<color_params>' . $modernParams . ')\s*\))|
				(?:(?<=[\/\\\()"\':,.;<>~!@#$%^&*|+=[\]{}`?\s\t])(?P<named>' . $preDefined . ')(?=[\/\\\()"\':,.;<>~!@#$%^&*|+=[\]{}`?\s\t]))/xu', $this->subject, $results, PREG_SET_ORDER);

		if( preg_last_error() !== PREG_NO_ERROR ) {
			throw new \LogicException('Regex Error: ' . preg_last_error());
		}

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
				} elseif( !empty($result['color_func']) ) {
					$colorSpace   = strtolower($result['color_space']);
					$paramMatches = trim($result['color_params']);

					$params = $this->splitFunctionParams($paramMatches);
					$expectedParamCount = strpos($paramMatches, '/') === false ? 3 : 4;
					if( count($params) !== $expectedParamCount ) {
						throw new \LogicException('Invalid color() param count');
					}

					$params = $this->normalizeColorSpaceParams($params);

					$color = $this->factory->makeFromColorSpace(
						$colorSpace,
						$params[0],
						$params[1],
						$params[2],
						$params[3] ?? 1.0
					);
				} else {
					$funcMatch = strtolower(
						($result['func'] ?? '')
						?: ($result['hue_func'] ?? '')
						?: ($result['func2'] ?? '')
						?: ($result['hue_func2'] ?? '')
						?: ($result['hue_func3'] ?? '')
					);
					$paramMatches = ($result['params'] ?? '')
						?: ($result['hue_params'] ?? '')
						?: ($result['params2'] ?? '')
						?: ($result['hue_params2'] ?? '')
						?: ($result['hue_params3'] ?? '');

					$params = $this->splitFunctionParams($paramMatches);
					$params = $this->normalizeFunctionParams($funcMatch, $params);

					$color = $this->getFuncColor($funcMatch, $params);
				}

				$xyz = $color->getXyzaArray();
				foreach( $xyz as $component ) {
					if( !is_finite($component) ) {
						throw new \RangeException('Color conversion produced a non-finite coordinate');
					}

					if( abs($component) > $this->maxComparableXyzComponent ) {
						throw new \RangeException('Color conversion exceeds the supported comparison range');
					}
				}
			} catch( \Exception $e ) {
				$errors[] = [
					'exception' => $e,
					'result'    => $result,
				];

				continue;
			}

			// Use XYZ coordinates for deduplication to preserve HDR/wide-gamut distinctions.
			$key = md5(sprintf('%.8f,%.8f,%.8f,%.8f', $xyz['x'], $xyz['y'], $xyz['z'], $xyz['a']));
			if( !isset($colors[$key]) ) {
				$colors[$key] = $color;
			}

			$colors[$key]->addInstance($result[0]);
		}

		return $colors;
	}

	/**
	 * Convert CSS percentage components to their function-specific reference range.
	 *
	 * @return list<string>
	 */
	private function splitFunctionParams( string $paramMatches ): array {
		$params = preg_split('%\s*(,|\s|/)\s*%', $paramMatches, -1, PREG_SPLIT_NO_EMPTY);
		if( $params === false ) {
			throw new \LogicException('Unable to split color parameters');
		}

		return array_map('\trim', $params);
	}

	/**
	 * @param list<string> $params
	 * @return list<float>
	 */
	private function normalizeColorSpaceParams( array $params ): array {
		$params = array_map(function ( string $param ): float {
			if( substr($param, -1) === '%' ) {
				$value = ((float)substr($param, 0, -1)) / 100;
			} else {
				$value = (float)$param;
			}

			if( !is_finite($value) ) {
				throw new \RangeException('Color component must be finite');
			}

			return $value;
		}, $params);

		if( isset($params[3]) ) {
			$params[3] = $this->clamp($params[3], 0.0, 1.0);
		}

		return $params;
	}

	/**
	 * @param list<string> $params
	 * @return list<float>
	 */
	private function normalizeFunctionParams( string $func, array $params ): array {
		foreach( $params as $index => $param ) {
			if( $this->isHueComponent($func, $index) ) {
				$params[$index] = $this->normalizeHue($param);

				continue;
			}

			$isPercentage = substr($param, -1) === '%';
			$value        = (float)($isPercentage ? substr($param, 0, -1) : $param);

			if( !is_finite($value) ) {
				throw new \RangeException('Color component must be finite');
			}

			if( !$isPercentage ) {
				$params[$index] = $index !== 3 && in_array($func, [ 'hsl', 'hsla', 'hwb' ], true)
					? $value / 100
					: $value;

				continue;
			}

			if( $index === 3 ) {
				$params[$index] = $value / 100;

				continue;
			}

			switch( $func ) {
				case 'rgb':
				case 'rgba':
					$params[$index] = ($value / 100) * 255;
					break;
				case 'lab':
					$params[$index] = $index === 0 ? $value : $value * 1.25;
					break;
				case 'lch':
					$params[$index] = $index === 0 ? $value : ($index === 1 ? $value * 1.5 : $value);
					break;
				case 'oklab':
					$params[$index] = $index === 0 ? $value / 100 : $value * 0.004;
					break;
				case 'oklch':
					$params[$index] = $index === 0 ? $value / 100 : ($index === 1 ? $value * 0.004 : $value);
					break;
				default:
					$params[$index] = $value / 100;
			}
		}

		if( isset($params[3]) ) {
			$params[3] = $this->clamp($params[3], 0.0, 1.0);
		}

		switch( $func ) {
			case 'rgb':
			case 'rgba':
				$params[0] = $this->clamp($params[0], 0.0, 255.0);
				$params[1] = $this->clamp($params[1], 0.0, 255.0);
				$params[2] = $this->clamp($params[2], 0.0, 255.0);
				break;
			case 'hsl':
			case 'hsla':
				$params[1] = $this->clamp($params[1], 0.0, 1.0);
				$params[2] = $this->clamp($params[2], 0.0, 1.0);
				break;
			case 'hwb':
				$params[1] = max(0.0, $params[1]);
				$params[2] = max(0.0, $params[2]);
				break;
			case 'lab':
				$params[0] = $this->clamp($params[0], 0.0, 100.0);
				break;
			case 'lch':
				$params[0] = $this->clamp($params[0], 0.0, 100.0);
				$params[1] = max(0.0, $params[1]);
				break;
			case 'oklab':
				$params[0] = $this->clamp($params[0], 0.0, 1.0);
				break;
			case 'oklch':
				$params[0] = $this->clamp($params[0], 0.0, 1.0);
				$params[1] = max(0.0, $params[1]);
				break;
		}

		return array_values($params);
	}

	private function isHueComponent( string $func, int $index ): bool {
		return ($index === 0 && in_array($func, [ 'hsl', 'hsla', 'hwb' ], true))
			|| ($index === 2 && in_array($func, [ 'lch', 'oklch' ], true));
	}

	private function normalizeHue( string $hue ): float {
		$hue = strtolower($hue);

		if( substr($hue, -4) === 'turn' ) {
			$value = (float)substr($hue, 0, -4) * 360.0;
		} elseif( substr($hue, -4) === 'grad' ) {
			$value = (float)substr($hue, 0, -4) * 0.9;
		} elseif( substr($hue, -3) === 'deg' ) {
			$value = (float)substr($hue, 0, -3);
		} elseif( substr($hue, -3) === 'rad' ) {
			$value = (float)substr($hue, 0, -3) * 180.0 / M_PI;
		} else {
			$value = (float)$hue;
		}

		if( !is_finite($value) ) {
			throw new \RangeException('Hue must be finite');
		}

		return $this->factory->normalizeHue($value);
	}

	private function clamp( float $value, float $minimum, float $maximum ): float {
		return max($minimum, min($maximum, $value));
	}

	/**
	 * @param list<float> $params
	 * @throws \LogicException
	 */
	private function getFuncColor( string $func, array $params ): ColorEntry {
		switch( $func ) {
			case 'rgba':
			case 'rgb':
				if( count($params) === 3 ) {
					return $this->factory->makeFromRgb($params[0], $params[1], $params[2]);
				}

				if( count($params) === 4 ) {
					return $this->factory->makeFromRgba($params[0], $params[1], $params[2], $params[3]);
				}

				throw new \LogicException('Invalid param count');
			case 'hsla':
			case 'hsl':
				if( count($params) === 3 ) {
					return $this->factory->makeFromHsl($params[0], $params[1], $params[2]);
				}

				if( count($params) === 4 ) {
					return $this->factory->makeFromHsla($params[0], $params[1], $params[2], $params[3]);
				}

				throw new \LogicException('Invalid param count');
			case 'hwb':
				if( count($params) === 3 ) {
					return $this->factory->makeFromHwb($params[0], $params[1], $params[2]);
				}

				if( count($params) === 4 ) {
					return $this->factory->makeFromHwb($params[0], $params[1], $params[2], $params[3]);
				}

				throw new \LogicException('Invalid param count');
			case 'lab':
				if( count($params) === 3 ) {
					return $this->factory->makeFromLab($params[0], $params[1], $params[2]);
				}

				if( count($params) === 4 ) {
					return $this->factory->makeFromLab($params[0], $params[1], $params[2], $params[3]);
				}

				throw new \LogicException('Invalid param count');
			case 'lch':
				if( count($params) === 3 ) {
					return $this->factory->makeFromLch($params[0], $params[1], $params[2]);
				}

				if( count($params) === 4 ) {
					return $this->factory->makeFromLch($params[0], $params[1], $params[2], $params[3]);
				}

				throw new \LogicException('Invalid param count');
			case 'oklab':
				if( count($params) === 3 ) {
					return $this->factory->makeFromOklab($params[0], $params[1], $params[2]);
				}

				if( count($params) === 4 ) {
					return $this->factory->makeFromOklab($params[0], $params[1], $params[2], $params[3]);
				}

				throw new \LogicException('Invalid param count');
			case 'oklch':
				if( count($params) === 3 ) {
					return $this->factory->makeFromOklch($params[0], $params[1], $params[2]);
				}

				if( count($params) === 4 ) {
					return $this->factory->makeFromOklch($params[0], $params[1], $params[2], $params[3]);
				}

				throw new \LogicException('Invalid param count');
		}

		throw new \LogicException("Func type '{$func}' not implemented");
	}

}
