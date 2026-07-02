<?php

namespace donatj\AlikeColorFinder;

class Rec2020ColorEntry implements ColorEntry {

	use ColorEntryTrait;

	/** Rec.2020 RGB storage (float, 0–1 range; may exceed for HDR). */
	protected float $r;
	protected float $g;
	protected float $b;
	protected float $a;

/**
	 * @param float $r Rec.2020 red (0–1 range; may exceed for HDR)
	 * @param float $g Rec.2020 green (0–1 range; may exceed for HDR)
	 * @param float $b Rec.2020 blue (0–1 range; may exceed for HDR)
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
			return sprintf('color(rec2020 %.6g %.6g %.6g)', $this->r, $this->g, $this->b);
		}

		return sprintf('color(rec2020 %.6g %.6g %.6g / %.6g)', $this->r, $this->g, $this->b, $this->a);
	}


	/**
	 * @return array
	 */
	public function getXyzaArray(): array {
		// Convert Rec.2020 to linear
		$rLin = self::rec2020ToLinear($this->r);
		$gLin = self::rec2020ToLinear($this->g);
		$bLin = self::rec2020ToLinear($this->b);

		// Rec.2020 linear to XYZ D65 matrix
		$x = 0.6369580483012914 * $rLin + 0.14461690358620832 * $gLin + 0.1688809751641721 * $bLin;
		$y = 0.2627002120112671 * $rLin + 0.6779980715188708 * $gLin + 0.05930171646986196 * $bLin;
		$z = 0.0 * $rLin + 0.028072693049087428 * $gLin + 1.0609850577107909 * $bLin;

		return [
			'x' => $x * 100,
			'y' => $y * 100,
			'z' => $z * 100,
			'a' => $this->a,
		];
	}

	/**
	 * Convert stored Rec.2020 to linear sRGB (may be outside [0, 1] for HDR).
	 *
	 * @return float[]  [r, g, b] linear
	 */
	private function getLinearSrgb(): array {
		// First convert Rec.2020 to linear
		$rLin = self::rec2020ToLinear($this->r);
		$gLin = self::rec2020ToLinear($this->g);
		$bLin = self::rec2020ToLinear($this->b);

		// Convert linear Rec.2020 to XYZ D65
		$x = 0.6369580483012914 * $rLin + 0.14461690358620832 * $gLin + 0.1688809751641721 * $bLin;
		$y = 0.2627002120112671 * $rLin + 0.6779980715188708 * $gLin + 0.05930171646986196 * $bLin;
		$z = 0.0 * $rLin + 0.028072693049087428 * $gLin + 1.0609850577107909 * $bLin;

		// XYZ D65 to linear sRGB
		return [
			+3.2404542 * $x - 1.5371385 * $y - 0.4985314 * $z,
			-0.9692660 * $x + 1.8760108 * $y + 0.0415560 * $z,
			+0.0556434 * $x - 0.2040259 * $y + 1.0572252 * $z,
		];
	}

	private static function rec2020ToLinear( float $c ): float {
		$alpha = 1.09929682680944;
		$beta  = 0.018053968510807;
		if( $c < $beta * 4.5 ) {
			return $c / 4.5;
		}
		return (($c + $alpha - 1) / $alpha) ** (1 / 0.45);
	}

}
