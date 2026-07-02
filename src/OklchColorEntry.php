<?php

namespace donatj\AlikeColorFinder;

class OklchColorEntry implements ColorEntry {

	use ColorEntryTrait;

	/** OKLch storage (polar OKLab) */
	protected float $l;
	protected float $c;
	protected float $h;
	protected float $a;

/**
	 * @param float $l OKLch lightness (0–1)
	 * @param float $c OKLch chroma
	 * @param float $h OKLch hue (degrees)
	 * @param float $a alpha 0–1
	 */
	public function __construct(
		float $l,
		float $c,
		float $h,
		float $a = 1.0
	) {
		if( $a > 1 || $a < 0 ) {
			throw new \RangeException('Alpha must be between 0 and 1');
		}
		$this->l = $l;
		$this->c = $c;
		$this->h = $h;
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
			return sprintf('oklch(%.6g %.6g %.6g)', $this->l, $this->c, $this->h);
		}

		return sprintf('oklch(%.6g %.6g %.6g / %.6g)', $this->l, $this->c, $this->h, $this->a);
	}


	/**
	 * @return array
	 */
	public function getXyzaArray(): array {
		// Convert OKLch to OKLab
		$hRad = $this->h * M_PI / 180;
		$aVal = $this->c * cos($hRad);
		$bVal = $this->c * sin($hRad);

		// OKLab to linear sRGB via LMS
		$lPrime = $this->l + 0.3963377774 * $aVal + 0.2158037573 * $bVal;
		$mPrime = $this->l - 0.1055613458 * $aVal - 0.0638541728 * $bVal;
		$sPrime = $this->l - 0.0894841775 * $aVal - 1.2914855480 * $bVal;

		$lm = $lPrime ** 3;
		$mm = $mPrime ** 3;
		$sm = $sPrime ** 3;

		// LMS to linear sRGB
		$rLin = +4.0767416621 * $lm - 3.3077115913 * $mm + 0.2309699292 * $sm;
		$gLin = -1.2684380046 * $lm + 2.6097574011 * $mm - 0.3413193965 * $sm;
		$bLin = -0.0041960863 * $lm - 0.7034186147 * $mm + 1.7076147010 * $sm;

		// Linear sRGB to XYZ D65
		$x = 0.4124 * $rLin + 0.3576 * $gLin + 0.1805 * $bLin;
		$y = 0.2126 * $rLin + 0.7152 * $gLin + 0.0722 * $bLin;
		$z = 0.0193 * $rLin + 0.1192 * $gLin + 0.9505 * $bLin;

		return [
			'x' => $x * 100,
			'y' => $y * 100,
			'z' => $z * 100,
			'a' => $this->a,
		];
	}

	/**
	 * Convert stored OKLch to linear sRGB (may be outside [0, 1] for out-of-gamut).
	 *
	 * @return float[]  [r, g, b] linear
	 */
	private function getLinearSrgb(): array {
		// Convert OKLch to OKLab
		$hRad = $this->h * M_PI / 180;
		$aVal = $this->c * cos($hRad);
		$bVal = $this->c * sin($hRad);

		// OKLab to LMS (via cube-root-compressed LMS)
		$lPrime = $this->l + 0.3963377774 * $aVal + 0.2158037573 * $bVal;
		$mPrime = $this->l - 0.1055613458 * $aVal - 0.0638541728 * $bVal;
		$sPrime = $this->l - 0.0894841775 * $aVal - 1.2914855480 * $bVal;

		$lm = $lPrime ** 3;
		$mm = $mPrime ** 3;
		$sm = $sPrime ** 3;

		// LMS to linear sRGB
		return [
			+4.0767416621 * $lm - 3.3077115913 * $mm + 0.2309699292 * $sm,
			-1.2684380046 * $lm + 2.6097574011 * $mm - 0.3413193965 * $sm,
			-0.0041960863 * $lm - 0.7034186147 * $mm + 1.7076147010 * $sm,
		];
	}

}
