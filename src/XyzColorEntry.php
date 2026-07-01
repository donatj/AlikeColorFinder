<?php

namespace donatj\AlikeColorFinder;

class XyzColorEntry implements ColorEntry {

	/**
	 * XYZ D65 storage (float, 0–1 range; may exceed 1 for HDR colors).
	 *
	 * @var float
	 */
	protected $xyzX, $xyzY, $xyzZ;

	/**
	 * @var float
	 */
	protected $a;

	protected $distinctInstances = [];

	/**
	 * @param float $x  XYZ X coordinate (0–1 range; may exceed for HDR)
	 * @param float $y  XYZ Y coordinate (0–1 range; may exceed for HDR)
	 * @param float $z  XYZ Z coordinate (0–1 range; may exceed for HDR)
	 * @param float $a  alpha 0–1
	 */
	public function __construct( float $x, float $y, float $z, float $a = 1.0 ) {
		if( $a > 1 || $a < 0 ) {
			throw new \RangeException('Alpha must be between 0 and 1');
		}
		$this->xyzX = $x;
		$this->xyzY = $y;
		$this->xyzZ = $z;
		$this->a    = $a;
	}

	/**
	 * @return float  sRGB red 0–255
	 */
	public function getR() {
		return self::linearToSrgb255($this->getLinearSrgb()[0]);
	}

	/**
	 * @return float  sRGB green 0–255
	 */
	public function getG() {
		return self::linearToSrgb255($this->getLinearSrgb()[1]);
	}

	/**
	 * @return float  sRGB blue 0–255
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
			'a' => $this->a,
		];
	}

	/**
	 * @return array
	 */
	public function getXyzaArray() {
		// Return stored XYZ D65 scaled ×100
		return [
			'x' => $this->xyzX * 100,
			'y' => $this->xyzY * 100,
			'z' => $this->xyzZ * 100,
			'a' => $this->a,
		];
	}

	/**
	 * @return array
	 */
	public function getLabAlphaCieArray() {
		$xyz = $this->getXyzaArray();

		// Observer = 2°, Illuminant = D65
		$xyz['x'] /= 95.047;
		$xyz['y'] /= 100;
		$xyz['z'] /= 108.883;

		$xyz = array_map(function( $item ) {
			if( $item > 0.008856 ) {
				return pow($item, 1 / 3);
			}

			return (7.787 * $item) + (16 / 116);
		}, $xyz);

		return [
			'l'     => (116 * $xyz['y']) - 16,
			'a'     => 500 * ($xyz['x'] - $xyz['y']),
			'b'     => 200 * ($xyz['y'] - $xyz['z']),
			'Alpha' => $this->a,
		];
	}

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
