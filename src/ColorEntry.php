<?php

namespace donatj\AlikeColorFinder;

class ColorEntry {

	/**
	 * @var int
	 */
	protected $r, $g, $b;

	/**
	 * @var float
	 */
	protected $a;

	protected $distinctInstances = [ ];

	function __construct( $r, $g, $b, $a = 1 ) {
		$this->setR($r);
		$this->setG($g);
		$this->setB($b);
		$this->setA($a);
	}

	/**
	 * @return int
	 */
	public function getR() {
		return $this->r;
	}

	/**
	 * @return int
	 */
	public function getG() {
		return $this->g;
	}

	/**
	 * @return int
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
	 * @param int $r
	 */
	public function setR( $r ) {
		if( $r > 255 || $r < 0 ) {
			throw new \RangeException('Red must be between 0 and 255');
		}
		$this->r = $r;
	}

	/**
	 * @param int $g
	 */
	public function setG( $g ) {
		if( $g > 255 || $g < 0 ) {
			throw new \RangeException('Green must be between 0 and 255');
		}
		$this->g = $g;
	}

	/**
	 * @param int $b
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
		return "rgba({$this->r},{$this->g},{$this->b},{$this->a})";
	}

	function getRgbHexString() {
		$hex = "#";
		$hex .= str_pad(dechex($this->r), 2, "0", STR_PAD_LEFT);
		$hex .= str_pad(dechex($this->g), 2, "0", STR_PAD_LEFT);
		$hex .= str_pad(dechex($this->b), 2, "0", STR_PAD_LEFT);

		return $hex;
	}

	public function getSimplestCssString() {
		if( $this->a == 1 ) {
			return $this->getRgbHexString();
		} else {
			return $this->getRgbaString();
		}
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

		$rgba = array_map(function ( $item ) {
			if( $item > 0.04045 ) {
				$item = pow((($item + 0.055) / 1.055), 2.4);
			} else {
				$item = $item / 12.92;
			}

			return ($item * 100);
		}, $rgba);

		//Observer. = 2°, Illuminant = D65
		$xyz = array(
			'x' => ($rgba['r'] * 0.4124) + ($rgba['g'] * 0.3576) + ($rgba['b'] * 0.1805),
			'y' => ($rgba['r'] * 0.2126) + ($rgba['g'] * 0.7152) + ($rgba['b'] * 0.0722),
			'z' => ($rgba['r'] * 0.0193) + ($rgba['g'] * 0.1192) + ($rgba['b'] * 0.9505),
			'a' => $this->getA(),
		);

		return $xyz;
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

		$xyz = array_map(function ( $item ) {
			if( $item > 0.008856 ) {
				//return $item ^ (1/3);
				return pow($item, 1 / 3);
			} else {
				return (7.787 * $item) + (16 / 116);
			}
		}, $xyz);

		$lab = array(
			'l'     => (116 * $xyz['y']) - 16,
			'a'     => 500 * ($xyz['x'] - $xyz['y']),
			'b'     => 200 * ($xyz['y'] - $xyz['z']),
			'Alpha' => $this->getA(),
		);

		return $lab;
	}

}
