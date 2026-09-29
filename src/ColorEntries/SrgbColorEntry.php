<?php

namespace donatj\AlikeColorFinder\ColorEntries;

use donatj\AlikeColorFinder\ColorEntry;
use donatj\AlikeColorFinder\ColorInstanceTrait;


class SrgbColorEntry implements ColorEntry {

	use ColorEntryTrait {
		getRgbHexString as private getCompactRgbHexString;
		getSimplestCssString as private getCompactSimplestCssString;
	}
	use ColorInstanceTrait;

	protected float $r;
	protected float $g;
	protected float $b;
	protected float $a;
	protected bool $usesLegacySerialization;

	/**
	 * @param float $r sRGB red   0–255
	 * @param float $g sRGB green 0–255
	 * @param float $b sRGB blue  0–255
	 * @param float $a alpha      0–1
	 */
	public function __construct( float $r, float $g, float $b, float $a = 1.0, bool $usesLegacySerialization = true ) {
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
		$this->usesLegacySerialization = $usesLegacySerialization;
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
	 * Preserve the legacy CLI's full, RGB-only hexadecimal format.
	 */
	public function getRgbHexString(): string {
		if( !$this->usesLegacySerialization ) {
			return $this->getCompactRgbHexString();
		}

		$hex = str_pad(dechex((int)$this->r), 2, '0', STR_PAD_LEFT);
		$hex .= str_pad(dechex((int)$this->g), 2, '0', STR_PAD_LEFT);
		$hex .= str_pad(dechex((int)$this->b), 2, '0', STR_PAD_LEFT);

		return '#' . $hex;
	}

	/**
	 * Preserve the legacy CLI's rgba() serialization for transparent colors.
	 */
	public function getSimplestCssString( float $epsilon = 0.001 ): string {
		if( !$this->usesLegacySerialization ) {
			return $this->getCompactSimplestCssString($epsilon);
		}

		if( $this->a == 1 ) {
			return $this->getRgbHexString();
		}

		return $this->getRgbaString();
	}

	/**
	 * @return array{r: float, g: float, b: float, a: float}
	 */
	public function getUnclampedRgbaArray(): array {
		return [
			'r' => $this->r,
			'g' => $this->g,
			'b' => $this->b,
			'a' => $this->a,
		];
	}

	/**
	 * @return array{x: float, y: float, z: float, a: float}
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

		list($x, $y, $z) = $this->linearSrgbToXyzD65($linearR, $linearG, $linearB);

		return [
			'x' => $x * 100,
			'y' => $y * 100,
			'z' => $z * 100,
			'a' => $this->a,
		];
	}

}
