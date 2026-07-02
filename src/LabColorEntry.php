<?php

namespace donatj\AlikeColorFinder;

class LabColorEntry implements ColorEntry {

	use ColorEntryTrait;
	use ColorInstanceTrait;

	/** Lab D50 storage */
	protected float $l;
	protected float $aVal;
	protected float $bVal;
	protected float $a;

/**
	 * @param float $l Lab lightness (0–100)
	 * @param float $aVal Lab a component
	 * @param float $bVal Lab b component
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
			return sprintf('lab(%.6g %.6g %.6g)', $this->l, $this->aVal, $this->bVal);
		}

		return sprintf('lab(%.6g %.6g %.6g / %.6g)', $this->l, $this->aVal, $this->bVal, $this->a);
	}


	/**
	 * @return array
	 */
	public function getXyzaArray(): array {
		// Lab D50 to XYZ D50
		$kappa   = 24389 / 27;
		$epsilon = 216 / 24389;

		// D50 white point
		$d50X = 0.3457 / 0.3585;
		$d50Y = 1.0;
		$d50Z = (1.0 - 0.3457 - 0.3585) / 0.3585;

		$fy = ($this->l + 16) / 116;
		$fx = $this->aVal / 500 + $fy;
		$fz = $fy - $this->bVal / 200;

		$x50 = ($fx ** 3 > $epsilon) ? ($fx ** 3) : ((116 * $fx - 16) / $kappa);
		$y50 = ($this->l > 8) ? (($this->l + 16) / 116) ** 3 : ($this->l / $kappa);
		$z50 = ($fz ** 3 > $epsilon) ? ($fz ** 3) : ((116 * $fz - 16) / $kappa);

		$x50 *= $d50X;
		$y50 *= $d50Y;
		$z50 *= $d50Z;

		// XYZ D50 to XYZ D65 (Bradford chromatic adaptation)
		$x = 0.9554734527042182 * $x50 - 0.02301801888092314 * $y50 + 0.0632352294227355 * $z50;
		$y = -0.0283697093338637 * $x50 + 1.0099953980813410 * $y50 + 0.0210413696583471 * $z50;
		$z = 0.0123140016883655 * $x50 - 0.0205076964334771 * $y50 + 1.3303659366080753 * $z50;

		return [
			'x' => $x * 100,
			'y' => $y * 100,
			'z' => $z * 100,
			'a' => $this->a,
		];
	}

	/**
	 * Convert stored Lab D50 to linear sRGB (may be outside [0, 1] for out-of-gamut).
	 *
	 * @return float[]  [r, g, b] linear
	 */
	private function getLinearSrgb(): array {
		$xyz = $this->getXyzaArray();
		$x = $xyz['x'] / 100;
		$y = $xyz['y'] / 100;
		$z = $xyz['z'] / 100;

		// XYZ D65 to linear sRGB
		return [
			+3.2404542 * $x - 1.5371385 * $y - 0.4985314 * $z,
			-0.9692660 * $x + 1.8760108 * $y + 0.0415560 * $z,
			+0.0556434 * $x - 0.2040259 * $y + 1.0572252 * $z,
		];
	}

}
