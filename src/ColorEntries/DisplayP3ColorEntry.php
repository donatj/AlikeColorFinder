<?php

namespace donatj\AlikeColorFinder\ColorEntries;

use donatj\AlikeColorFinder\ColorEntry;
use donatj\AlikeColorFinder\ColorInstanceTrait;


class DisplayP3ColorEntry implements ColorEntry {

	use ColorEntryTrait;
	use ColorInstanceTrait;

	/** Display P3 RGB storage (float, 0–1 range; may exceed for HDR). */
	protected float $r;
	protected float $g;
	protected float $b;
	protected float $a;

	/**
	 * @param float $r Display P3 red (0–1 range; may exceed for HDR)
	 * @param float $g Display P3 green (0–1 range; may exceed for HDR)
	 * @param float $b Display P3 blue (0–1 range; may exceed for HDR)
	 * @param float $a alpha 0–1
	 */
	public function __construct(
		float $r,
		float $g,
		float $b,
		float $a = 1.0
	) {
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
		return $this->linearToSrgb255($this->getLinearSrgb()[0]);
	}

	/**
	 * @return float  sRGB green 0–255
	 */
	public function getG(): float {
		return $this->linearToSrgb255($this->getLinearSrgb()[1]);
	}

	/**
	 * @return float  sRGB blue 0–255
	 */
	public function getB(): float {
		return $this->linearToSrgb255($this->getLinearSrgb()[2]);
	}

	/**
	 * @return float
	 */
	public function getA(): float {
		return $this->a;
	}

	public function getNativeCssString(): string {
		if( $this->a == 1 ) {
			return sprintf('color(display-p3 %.6g %.6g %.6g)', $this->r, $this->g, $this->b);
		}

		return sprintf('color(display-p3 %.6g %.6g %.6g / %.6g)', $this->r, $this->g, $this->b, $this->a);
	}


	/**
	 * @return array{x: float, y: float, z: float, a: float}
	 */
	public function getXyzaArray(): array {
		// Convert Display P3 to XYZ D65 (scaled ×100)
		$rLin = $this->srgbToLinear($this->r);
		$gLin = $this->srgbToLinear($this->g);
		$bLin = $this->srgbToLinear($this->b);

		// Display P3 linear to XYZ D65 matrix
		$x = 0.4865709486482162 * $rLin + 0.26566769316909306 * $gLin + 0.1982172852343625 * $bLin;
		$y = 0.22897456406974884 * $rLin + 0.6917385218564081 * $gLin + 0.07928691407384083 * $bLin;
		$z = 0.0 * $rLin + 0.04511338185890264 * $gLin + 1.0439443689736354 * $bLin;

		return [
			'x' => $x * 100,
			'y' => $y * 100,
			'z' => $z * 100,
			'a' => $this->a,
		];
	}

	/**
	 * Convert stored Display P3 to linear sRGB (may be outside [0, 1] for HDR).
	 *
	 * @return float[]  [r, g, b] linear
	 */
	private function getLinearSrgb(): array {
		// First convert Display P3 to linear
		$rLin = $this->srgbToLinear($this->r);
		$gLin = $this->srgbToLinear($this->g);
		$bLin = $this->srgbToLinear($this->b);

		// Direct linear Display P3 to linear sRGB conversion composed from the
		// CSS Color 4 matrices. Difference form preserves shared D65 neutrals.
		// @see https://www.w3.org/TR/css-color-4/#color-conversion-code
		return [
			$rLin - 0.22494017628055993 * ($gLin - $rLin),
			$gLin - 0.04205695470968818 * ($rLin - $gLin),
			$bLin - 0.01963755459033444 * ($rLin - $bLin) - 0.07863604555063189 * ($gLin - $bLin),
		];
	}

	private function srgbToLinear( float $c ): float {
		$sign = $c < 0 ? -1 : 1;
		$abs  = abs($c);

		if( $abs <= 0.04045 ) {
			return $c / 12.92;
		}

		return $sign * (($abs + 0.055) / 1.055) ** 2.4;
	}

}
