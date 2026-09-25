<?php

namespace donatj\AlikeColorFinder\ColorEntries;

use donatj\AlikeColorFinder\ColorEntry;
use donatj\AlikeColorFinder\ColorInstanceTrait;


class ExtendedSrgbColorEntry implements ColorEntry {

	use ColorEntryTrait;
	use ColorInstanceTrait;

	/** Gamma-encoded sRGB storage (float, nominal range 0–1). */
	protected float $r;
	protected float $g;
	protected float $b;
	protected float $a;

	public function __construct( float $r, float $g, float $b, float $a = 1.0 ) {
		if( $a > 1 || $a < 0 ) {
			throw new \RangeException('Alpha must be between 0 and 1');
		}

		$this->r = $r;
		$this->g = $g;
		$this->b = $b;
		$this->a = $a;
	}

	public function getR(): float {
		return self::clampToSrgb255($this->r);
	}

	public function getG(): float {
		return self::clampToSrgb255($this->g);
	}

	public function getB(): float {
		return self::clampToSrgb255($this->b);
	}

	public function getA(): float {
		return $this->a;
	}

	public function getNativeCssString(): string {
		if( $this->a == 1 ) {
			return sprintf('color(srgb %.6g %.6g %.6g)', $this->r, $this->g, $this->b);
		}

		return sprintf('color(srgb %.6g %.6g %.6g / %.6g)', $this->r, $this->g, $this->b, $this->a);
	}

	/**
	 * @return array
	 */
	public function getXyzaArray(): array {
		$rLin = self::srgbToLinear($this->r);
		$gLin = self::srgbToLinear($this->g);
		$bLin = self::srgbToLinear($this->b);

		return [
			'x' => ($rLin * 0.4124 + $gLin * 0.3576 + $bLin * 0.1805) * 100,
			'y' => ($rLin * 0.2126 + $gLin * 0.7152 + $bLin * 0.0722) * 100,
			'z' => ($rLin * 0.0193 + $gLin * 0.1192 + $bLin * 0.9505) * 100,
			'a' => $this->a,
		];
	}

	private static function clampToSrgb255( float $component ): float {
		return max(0.0, min(255.0, $component * 255));
	}

	private static function srgbToLinear( float $component ): float {
		$sign = $component < 0 ? -1 : 1;
		$abs  = abs($component);

		if( $abs <= 0.04045 ) {
			return $component / 12.92;
		}

		return $sign * (($abs + 0.055) / 1.055) ** 2.4;
	}

}
