<?php

namespace donatj\AlikeColorFinder;

class OklabColorEntry implements ColorEntry {

	use ColorEntryTrait;

	/** OKLab storage */
	protected float $l;
	protected float $aVal;
	protected float $bVal;
	protected float $a;

/**
	 * @param float $l OKLab lightness (0–1)
	 * @param float $aVal OKLab a component
	 * @param float $bVal OKLab b component
	 * @param float $a alpha 0–1
	 */
	public function __construct(
		float $l,
		float $aVal,
		float $bVal,
		float $a = 1.0
	) {
		if( $a > 1 || $a < 0 ) {
			throw new \RangeException('Alpha must be between 0 and 1');
		}
		$this->l = $l;
		$this->aVal = $aVal;
		$this->bVal = $bVal;
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
			return sprintf('oklab(%.6g %.6g %.6g)', $this->l, $this->aVal, $this->bVal);
		}

		return sprintf('oklab(%.6g %.6g %.6g / %.6g)', $this->l, $this->aVal, $this->bVal, $this->a);
	}


	/**
	 * @return array
	 */
	public function getXyzaArray(): array {
		// OKLab to linear sRGB via LMS
		$lPrime = $this->l + 0.3963377774 * $this->aVal + 0.2158037573 * $this->bVal;
		$mPrime = $this->l - 0.1055613458 * $this->aVal - 0.0638541728 * $this->bVal;
		$sPrime = $this->l - 0.0894841775 * $this->aVal - 1.2914855480 * $this->bVal;

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
	 * Convert stored OKLab to linear sRGB (may be outside [0, 1] for out-of-gamut).
	 *
	 * @return float[]  [r, g, b] linear
	 */
	private function getLinearSrgb(): array {
		// OKLab to LMS (via cube-root-compressed LMS)
		$lPrime = $this->l + 0.3963377774 * $this->aVal + 0.2158037573 * $this->bVal;
		$mPrime = $this->l - 0.1055613458 * $this->aVal - 0.0638541728 * $this->bVal;
		$sPrime = $this->l - 0.0894841775 * $this->aVal - 1.2914855480 * $this->bVal;

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
