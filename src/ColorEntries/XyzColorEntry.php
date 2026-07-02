<?php

namespace donatj\AlikeColorFinder\ColorEntries;

use donatj\AlikeColorFinder\ColorEntry;
use donatj\AlikeColorFinder\ColorInstanceTrait;


class XyzColorEntry implements ColorEntry {

	use ColorEntryTrait;
	use ColorInstanceTrait;

	/** XYZ D65 storage (float, 0–1 range; may exceed 1 for HDR colors). */
	protected float $xyzX;
	protected float $xyzY;
	protected float $xyzZ;

	protected float $a;
	protected float $gamutEpsilon;

	/**
	 * @param float $x XYZ X coordinate (0–1 range; may exceed for HDR)
	 * @param float $y XYZ Y coordinate (0–1 range; may exceed for HDR)
	 * @param float $z XYZ Z coordinate (0–1 range; may exceed for HDR)
	 * @param float $a alpha 0–1
	 * @param float $gamutEpsilon tolerance for gamut detection (default 0.001)
	 */
	public function __construct(
		float $x,
		float $y,
		float $z,
		float $a = 1.0,
		float $gamutEpsilon = 0.001
	) {
		if( $a > 1 || $a < 0 ) {
			throw new \RangeException('Alpha must be between 0 and 1');
		}
		$this->xyzX = $x;
		$this->xyzY = $y;
		$this->xyzZ = $z;
		$this->a    = $a;

		$this->gamutEpsilon = $gamutEpsilon;
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

	/**
	 * Returns XYZ D65 color() format
	 */
	public function getNativeCssString(): string {
		$x = $this->xyzX;
		$y = $this->xyzY;
		$z = $this->xyzZ;
		if( $this->a == 1 ) {
			return sprintf('color(xyz-d65 %.6g %.6g %.6g)', $x, $y, $z);
		}

		return sprintf('color(xyz-d65 %.6g %.6g %.6g / %.6g)', $x, $y, $z, $this->a);
	}

	/**
	 * @return array
	 */
	public function getXyzaArray(): array {
		// Return stored XYZ D65 scaled ×100
		return [
			'x' => $this->xyzX * 100,
			'y' => $this->xyzY * 100,
			'z' => $this->xyzZ * 100,
			'a' => $this->a,
		];
	}

	/**
	 * Convert stored XYZ D65 to linear sRGB (may be outside [0, 1] for HDR).
	 *
	 * @return float[]  [r, g, b] linear
	 */
	private function getLinearSrgb(): array {
		return [
			+3.2404542 * $this->xyzX - 1.5371385 * $this->xyzY - 0.4985314 * $this->xyzZ,
			-0.9692660 * $this->xyzX + 1.8760108 * $this->xyzY + 0.0415560 * $this->xyzZ,
			+0.0556434 * $this->xyzX - 0.2040259 * $this->xyzY + 1.0572252 * $this->xyzZ,
		];
	}

}
