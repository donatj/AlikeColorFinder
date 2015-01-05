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

	/**
	 * @param \donatj\AlikeColorFinder\ColorEntry $c
	 * @return number
	 */
	public function getAbsDiff( ColorEntry $c ) {
		$diff = abs($this->getR() - $c->getR()) +
				abs($this->getG() - $c->getG()) +
				abs($this->getB() - $c->getB()) +
				(abs($this->getA() - $c->getA()) * 255);

		return $diff;
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

}
