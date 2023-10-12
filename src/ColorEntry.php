<?php

namespace donatj\AlikeColorFinder;

class ColorEntry {

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
	 * @param float $r
	 * @param float $g
	 * @param float $b
	 * @param float $a
	 */
	public function __construct( $r, $g, $b, $a = 1.0 ) {
		$this->setR($r);
		$this->setG($g);
		$this->setB($b);
		$this->setA($a);
	}

	/**
	 * @return float
	 */
	public function getR() {
		return $this->r;
	}

	/**
	 * @return float
	 */
	public function getG() {
		return $this->g;
	}

	/**
	 * @return float
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

	/**
	 * @param float $r
	 */
	public function setR( $r ) {
		if( $r > 255 || $r < 0 ) {
			throw new \RangeException('Red must be between 0 and 255');
		}
		$this->r = $r;
	}

	/**
	 * @param float $g
	 */
	public function setG( $g ) {
		if( $g > 255 || $g < 0 ) {
			throw new \RangeException('Green must be between 0 and 255');
		}
		$this->g = $g;
	}

	/**
	 * @param float $b
	 */
	public function setB( $b ) {
		if( $b > 255 || $b < 0 ) {
			throw new \RangeException('Blue must be between 0 and 255');
		}
		$this->b = $b;
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
		$r = round($this->r);
		$g = round($this->g);
		$b = round($this->b);
		return "rgba({$r},{$g},{$b},{$this->a})";
	}

	public function getRgbHexString() {
		$hex = "#";
		$hex .= str_pad(dechex((int)$this->r), 2, "0", STR_PAD_LEFT);
		$hex .= str_pad(dechex((int)$this->g), 2, "0", STR_PAD_LEFT);
		$hex .= str_pad(dechex((int)$this->b), 2, "0", STR_PAD_LEFT);

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
		$rgba = $this->getRgbaArray();
		unset($rgba['a']);

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

}
