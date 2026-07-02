<?php

namespace donatj\AlikeColorFinder;

class DisplayP3ColorEntry implements ColorEntry {

	use ColorEntryTrait;

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
		return self::linearToSrgb255($this->getLinearSrgb()[0]);
	}

	/**
	 * @return float  sRGB green 0–255
	 */
	public function getG(): float {
		return self::linearToSrgb255($this->getLinearSrgb()[1]);
	}

	/**
	 * @return float  sRGB blue 0–255
	 */
	public function getB(): float {
		return self::linearToSrgb255($this->getLinearSrgb()[2]);
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
	 * @return array
	 */
	public function getXyzaArray(): array {
		// Convert Display P3 to XYZ D65 (scaled ×100)
		$rLin = self::srgbToLinear($this->r);
		$gLin = self::srgbToLinear($this->g);
		$bLin = self::srgbToLinear($this->b);

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
		$rLin = self::srgbToLinear($this->r);
		$gLin = self::srgbToLinear($this->g);
		$bLin = self::srgbToLinear($this->b);

		// Convert linear Display P3 to XYZ D65
		$x = 0.4865709486482162 * $rLin + 0.26566769316909306 * $gLin + 0.1982172852343625 * $bLin;
		$y = 0.22897456406974884 * $rLin + 0.6917385218564081 * $gLin + 0.07928691407384083 * $bLin;
		$z = 0.0 * $rLin + 0.04511338185890264 * $gLin + 1.0439443689736354 * $bLin;

		// XYZ D65 to linear sRGB
		return [
			+3.2404542 * $x - 1.5371385 * $y - 0.4985314 * $z,
			-0.9692660 * $x + 1.8760108 * $y + 0.0415560 * $z,
			+0.0556434 * $x - 0.2040259 * $y + 1.0572252 * $z,
		];
	}

	private static function srgbToLinear( float $c ): float {
		if( $c <= 0.04045 ) {
			return $c / 12.92;
		}
		return (($c + 0.055) / 1.055) ** 2.4;
	}

}
