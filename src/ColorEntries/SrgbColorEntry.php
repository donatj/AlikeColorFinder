<?php

namespace donatj\AlikeColorFinder\ColorEntries;

use donatj\AlikeColorFinder\ColorEntry;
use donatj\AlikeColorFinder\ColorInstanceTrait;


class SrgbColorEntry implements ColorEntry {

	use ColorEntryTrait;
	use ColorInstanceTrait;

	protected float $r;
	protected float $g;
	protected float $b;
	protected float $a;

	/**
	 * @param float $r sRGB red   0–255
	 * @param float $g sRGB green 0–255
	 * @param float $b sRGB blue  0–255
	 * @param float $a alpha      0–1
	 */
	public function __construct( float $r, float $g, float $b, float $a = 1.0 ) {
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
	public function getR(): float {
		return $this->r;
	}

	/**
	 * @return float  sRGB green 0–255
	 */
	public function getG(): float {
		return $this->g;
	}

	/**
	 * @return float  sRGB blue 0–255
	 */
	public function getB(): float {
		return $this->b;
	}

	/**
	 * @return float
	 */
	public function getA(): float {
		return $this->a;
	}

	/**
	 * sRGB colors are already in the native sRGB format
	 */
	public function getNativeCssString(): string {
		if( $this->a == 1 ) {
			return $this->getRgbHexString();
		}

		return $this->getRgbaString();
	}

	/**
	 * @return array
	 */
	public function getXyzaArray(): array {
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

}
