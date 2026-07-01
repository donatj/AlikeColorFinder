<?php

namespace donatj\AlikeColorFinder;

class SrgbColorEntry implements ColorEntry {

	/**
	 * @var float
	 */
	protected $r, $g, $b;

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
		if( $a > 1 || $a < 0 ) {
			throw new \RangeException('Alpha must be between 0 and 1');
		}
		$this->r = $r;
		$this->g = $g;
		$this->b = $b;
		$this->a = $a;
	}

	/**
	 * @return float  sRGB red 0–255
	 */
	public function getR() {
		return $this->r;
	}

	/**
	 * @return float  sRGB green 0–255
	 */
	public function getG() {
		return $this->g;
	}

	/**
	 * @return float  sRGB blue 0–255
	 */
	public function getB() {
		return $this->b;
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
		$r = round($this->r);
		$g = round($this->g);
		$b = round($this->b);
		return "rgba({$r},{$g},{$b},{$this->a})";
	}

	public function getRgbHexString() {
		$hex = "#";
		$hex .= str_pad(dechex((int)round($this->r)), 2, "0", STR_PAD_LEFT);
		$hex .= str_pad(dechex((int)round($this->g)), 2, "0", STR_PAD_LEFT);
		$hex .= str_pad(dechex((int)round($this->b)), 2, "0", STR_PAD_LEFT);

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
			'r' => $this->r,
			'g' => $this->g,
			'b' => $this->b,
			'a' => $this->a,
		];
	}

	/**
	 * @return array
	 */
	public function getXyzaArray() {
		// Normalize RGB values to 1
		$r = $this->r / 255;
		$g = $this->g / 255;
		$b = $this->b / 255;

		// Apply sRGB gamma correction
		$linearR = $r > 0.04045 ? pow((($r + 0.055) / 1.055), 2.4) : $r / 12.92;
		$linearG = $g > 0.04045 ? pow((($g + 0.055) / 1.055), 2.4) : $g / 12.92;
		$linearB = $b > 0.04045 ? pow((($b + 0.055) / 1.055), 2.4) : $b / 12.92;

		// Scale by 100
		$linearR *= 100;
		$linearG *= 100;
		$linearB *= 100;

		// Observer = 2°, Illuminant = D65
		return [
			'x' => ($linearR * 0.4124) + ($linearG * 0.3576) + ($linearB * 0.1805),
			'y' => ($linearR * 0.2126) + ($linearG * 0.7152) + ($linearB * 0.0722),
			'z' => ($linearR * 0.0193) + ($linearG * 0.1192) + ($linearB * 0.9505),
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

}
