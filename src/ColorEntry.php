<?php

namespace donatj\AlikeColorFinder;

class ColorEntry {

	/**
	 * sRGB storage (float, 0–255).
	 * Populated when the color is created from sRGB inputs (constructor).
	 * null when the color was created via fromXyzD65() (HDR path).
	 *
	 * @var float|null
	 */
	protected $r, $g, $b;

	/**
	 * XYZ D65 storage (float, 0–1 range; may exceed 1 for HDR colors).
	 * Populated only when the color was created via fromXyzD65().
	 * null when the color was created from sRGB inputs.
	 *
	 * @var float|null
	 */
	protected $xyzX, $xyzY, $xyzZ;

	/**
	 * @var float
	 */
	protected $a;

	protected $distinctInstances = [];

	/**
	 * @param float $r  sRGB red   0–255
	 * @param float $g  sRGB green 0–255
	 * @param float $b  sRGB blue  0–255
	 * @param float $a  alpha      0–1
	 */
	public function __construct( $r, $g, $b, $a = 1.0 ) {
		if( $r > 255 || $r < 0 ) {
			throw new \RangeException('Red must be between 0 and 255');
		}
		if( $g > 255 || $g < 0 ) {
			throw new \RangeException('Green must be between 0 and 255');
		}
		if( $b > 255 || $b < 0 ) {
			throw new \RangeException('Blue must be between 0 and 255');
		}
		$this->r    = $r;
		$this->g    = $g;
		$this->b    = $b;
		$this->xyzX = null;
		$this->xyzY = null;
		$this->xyzZ = null;
		$this->setA($a);
	}

	/**
	 * Create a ColorEntry directly from XYZ D65 coordinates.
	 * Values may exceed the sRGB gamut (HDR); clamping only happens at display time.
	 *
	 * @param float $x
	 * @param float $y
	 * @param float $z
	 * @param float $a  alpha 0–1
	 */
	public static function fromXyzD65( float $x, float $y, float $z, float $a = 1.0 ): self {
		$entry       = new self(0, 0, 0, $a);
		$entry->r    = null;
		$entry->g    = null;
		$entry->b    = null;
		$entry->xyzX = $x;
		$entry->xyzY = $y;
		$entry->xyzZ = $z;

		return $entry;
	}

	/**
	 * @return float  sRGB red 0–255
	 */
	public function getR() {
		if( $this->r !== null ) {
			return $this->r;
		}

		return self::linearToSrgb255($this->getLinearSrgb()[0]);
	}

	/**
	 * @return float  sRGB green 0–255
	 */
	public function getG() {
		if( $this->g !== null ) {
			return $this->g;
		}

		return self::linearToSrgb255($this->getLinearSrgb()[1]);
	}

	/**
	 * @return float  sRGB blue 0–255
	 */
	public function getB() {
		if( $this->b !== null ) {
			return $this->b;
		}

		return self::linearToSrgb255($this->getLinearSrgb()[2]);
	}

	/**
	 * @return float
	 */
	public function getA() {
		return $this->a;
	}

	/**
	 * @param float $r  sRGB red 0–255
	 */
	public function setR( $r ) {
		if( $r > 255 || $r < 0 ) {
			throw new \RangeException('Red must be between 0 and 255');
		}
		$this->r    = $r;
		$this->xyzX = null;
		$this->xyzY = null;
		$this->xyzZ = null;
	}

	/**
	 * @param float $g  sRGB green 0–255
	 */
	public function setG( $g ) {
		if( $g > 255 || $g < 0 ) {
			throw new \RangeException('Green must be between 0 and 255');
		}
		$this->g    = $g;
		$this->xyzX = null;
		$this->xyzY = null;
		$this->xyzZ = null;
	}

	/**
	 * @param float $b  sRGB blue 0–255
	 */
	public function setB( $b ) {
		if( $b > 255 || $b < 0 ) {
			throw new \RangeException('Blue must be between 0 and 255');
		}
		$this->b    = $b;
		$this->xyzX = null;
		$this->xyzY = null;
		$this->xyzZ = null;
	}

	/**
	 * @param float $a
	 */
	public function setA( $a ) {
		if( $a > 1 || $a < 0 ) {
			throw new \RangeException('Alpha must be between 0 and 1');
		}
		$this->a = $a;
	}

	public function addInstance( $instance ) {
		if( !isset($this->distinctInstances[$instance]) ) {
			$this->distinctInstances[$instance] = 0;
		}
		$this->distinctInstances[$instance]++;
	}

	public function getInstanceTotal() {
		return array_sum($this->distinctInstances);
	}

	/**
	 * @return string[]
	 */
	public function getDistinctInstances() {
		return array_keys($this->distinctInstances);
	}

	public function getRgbaString() {
		$r = round($this->getR());
		$g = round($this->getG());
		$b = round($this->getB());
		return "rgba({$r},{$g},{$b},{$this->a})";
	}

	public function getRgbHexString() {
		$hex = "#";
		$hex .= str_pad(dechex((int)round($this->getR())), 2, "0", STR_PAD_LEFT);
		$hex .= str_pad(dechex((int)round($this->getG())), 2, "0", STR_PAD_LEFT);
		$hex .= str_pad(dechex((int)round($this->getB())), 2, "0", STR_PAD_LEFT);

		return $hex;
	}

	public function getSimplestCssString() {
		if( $this->a == 1 ) {
			return $this->getRgbHexString();
		}

		return $this->getRgbaString();
	}

	/**
	 * @return array
	 */
	public function getRgbaArray() {
		return [
			'r' => $this->getR(),
			'g' => $this->getG(),
			'b' => $this->getB(),
			'a' => $this->getA(),
		];
	}

	/**
	 * @return array
	 */
	public function getXyzaArray() {
		if( $this->xyzX !== null ) {
			// HDR path: return stored XYZ D65 scaled ×100
			return [
				'x' => $this->xyzX * 100,
				'y' => $this->xyzY * 100,
				'z' => $this->xyzZ * 100,
				'a' => $this->getA(),
			];
		}

		// sRGB path: compute XYZ from stored float values (original algorithm)
		$rgba = [ 'r' => $this->r, 'g' => $this->g, 'b' => $this->b ];

		// Normalize RGB values to 1
		$rgba['r'] /= 255;
		$rgba['g'] /= 255;
		$rgba['b'] /= 255;

		$rgba = array_map(function( $item ) {
			if( $item > 0.04045 ) {
				$item = pow((($item + 0.055) / 1.055), 2.4);
			} else {
				$item = $item / 12.92;
			}

			return $item * 100;
		}, $rgba);

		//Observer. = 2°, Illuminant = D65
		return [
			'x' => ($rgba['r'] * 0.4124) + ($rgba['g'] * 0.3576) + ($rgba['b'] * 0.1805),
			'y' => ($rgba['r'] * 0.2126) + ($rgba['g'] * 0.7152) + ($rgba['b'] * 0.0722),
			'z' => ($rgba['r'] * 0.0193) + ($rgba['g'] * 0.1192) + ($rgba['b'] * 0.9505),
			'a' => $this->getA(),
		];
	}

	/**
	 * @return array
	 */
	public function getLabAlphaCieArray() {
		$xyz = $this->getXyzaArray();
		unset($xyz['a']);

		//Ovserver = 2*, Iluminant=D65
		$xyz['x'] /= 95.047;
		$xyz['y'] /= 100;
		$xyz['z'] /= 108.883;

		$xyz = array_map(function( $item ) {
			if( $item > 0.008856 ) {
				//return $item ^ (1/3);
				return pow($item, 1 / 3);
			}

			return (7.787 * $item) + (16 / 116);
		}, $xyz);

		return [
			'l'     => (116 * $xyz['y']) - 16,
			'a'     => 500 * ($xyz['x'] - $xyz['y']),
			'b'     => 200 * ($xyz['y'] - $xyz['z']),
			'Alpha' => $this->getA(),
		];
	}

	// -------------------------------------------------------------------------
	// Private helpers (HDR path only)
	// -------------------------------------------------------------------------

	/**
	 * Convert stored XYZ D65 to linear sRGB (may be outside [0, 1] for HDR).
	 *
	 * @return float[]  [r, g, b] linear
	 */
	private function getLinearSrgb(): array {
		return [
			+3.2404542 * $this->xyzX - 1.5371385 * $this->xyzY - 0.4985314 * $this->xyzZ,
			-0.9692660 * $this->xyzX + 1.8760108 * $this->xyzY + 0.0415560 * $this->xyzZ,
			+0.0556434 * $this->xyzX - 0.2040259 * $this->xyzY + 1.0572252 * $this->xyzZ,
		];
	}

	/**
	 * Apply sRGB gamma and clamp to [0, 255].
	 */
	private static function linearToSrgb255( float $c ): float {
		$gamma = $c <= 0.0031308 ? 12.92 * $c : 1.055 * ($c ** (1 / 2.4)) - 0.055;

		return max(0.0, min(255.0, $gamma * 255));
	}

}
