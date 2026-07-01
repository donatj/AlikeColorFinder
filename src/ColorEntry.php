<?php

namespace donatj\AlikeColorFinder;

class ColorEntry {

	/**
	 * Internal storage in XYZ D65 (0–1 range; may exceed 1 for HDR colors).
	 *
	 * @var float
	 */
	protected $x, $y, $z;

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
		[ $this->x, $this->y, $this->z ] = self::srgb255ToXyzD65($r, $g, $b);
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
		$entry    = new self(0, 0, 0, $a);
		$entry->x = $x;
		$entry->y = $y;
		$entry->z = $z;

		return $entry;
	}

	/**
	 * @return float  sRGB red 0–255, clamped
	 */
	public function getR() {
		return self::linearToSrgb255($this->getLinearSrgb()[0]);
	}

	/**
	 * @return float  sRGB green 0–255, clamped
	 */
	public function getG() {
		return self::linearToSrgb255($this->getLinearSrgb()[1]);
	}

	/**
	 * @return float  sRGB blue 0–255, clamped
	 */
	public function getB() {
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
		[ $this->x, $this->y, $this->z ] = self::srgb255ToXyzD65($r, $this->getG(), $this->getB());
	}

	/**
	 * @param float $g  sRGB green 0–255
	 */
	public function setG( $g ) {
		if( $g > 255 || $g < 0 ) {
			throw new \RangeException('Green must be between 0 and 255');
		}
		[ $this->x, $this->y, $this->z ] = self::srgb255ToXyzD65($this->getR(), $g, $this->getB());
	}

	/**
	 * @param float $b  sRGB blue 0–255
	 */
	public function setB( $b ) {
		if( $b > 255 || $b < 0 ) {
			throw new \RangeException('Blue must be between 0 and 255');
		}
		[ $this->x, $this->y, $this->z ] = self::srgb255ToXyzD65($this->getR(), $this->getG(), $b);
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
		$r = $this->getR();
		$g = $this->getG();
		$b = $this->getB();
		return "rgba({$r},{$g},{$b},{$this->a})";
	}

	public function getRgbHexString() {
		$hex = "#";
		$hex .= str_pad(dechex((int)$this->getR()), 2, "0", STR_PAD_LEFT);
		$hex .= str_pad(dechex((int)$this->getG()), 2, "0", STR_PAD_LEFT);
		$hex .= str_pad(dechex((int)$this->getB()), 2, "0", STR_PAD_LEFT);

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
		// Return XYZ D65 scaled ×100 for backward compatibility
		return [
			'x' => $this->x * 100,
			'y' => $this->y * 100,
			'z' => $this->z * 100,
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
	// Private helpers
	// -------------------------------------------------------------------------

	/**
	 * Convert sRGB 0–255 to XYZ D65 0–1 range.
	 *
	 * @return float[]  [x, y, z]
	 */
	private static function srgb255ToXyzD65( float $r, float $g, float $b ): array {
		$rn = $r / 255;
		$gn = $g / 255;
		$bn = $b / 255;

		// Inverse sRGB gamma (linearise)
		$rLin = $rn > 0.04045 ? (($rn + 0.055) / 1.055) ** 2.4 : $rn / 12.92;
		$gLin = $gn > 0.04045 ? (($gn + 0.055) / 1.055) ** 2.4 : $gn / 12.92;
		$bLin = $bn > 0.04045 ? (($bn + 0.055) / 1.055) ** 2.4 : $bn / 12.92;

		// Observer 2°, Illuminant D65
		return [
			0.4124 * $rLin + 0.3576 * $gLin + 0.1805 * $bLin,
			0.2126 * $rLin + 0.7152 * $gLin + 0.0722 * $bLin,
			0.0193 * $rLin + 0.1192 * $gLin + 0.9505 * $bLin,
		];
	}

	/**
	 * Convert stored XYZ D65 to linear sRGB (may be outside [0, 1] for HDR).
	 *
	 * @return float[]  [r, g, b] linear
	 */
	private function getLinearSrgb(): array {
		return [
			+3.2404542 * $this->x - 1.5371385 * $this->y - 0.4985314 * $this->z,
			-0.9692660 * $this->x + 1.8760108 * $this->y + 0.0415560 * $this->z,
			+0.0556434 * $this->x - 0.2040259 * $this->y + 1.0572252 * $this->z,
		];
	}

	/**
	 * Apply sRGB gamma and clamp to [0, 255].
	 */
	private static function linearToSrgb255( float $c ): float {
		$gamma = $c <= 0.0031308 ? 12.92 * $c : 1.055 * ($c ** (1 / 2.4)) - 0.055;

		return max(0, min(255, round($gamma * 255)));
	}

}
