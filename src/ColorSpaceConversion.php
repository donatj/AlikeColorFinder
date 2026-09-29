<?php

namespace donatj\AlikeColorFinder;

/**
 * CSS Color 4 conversions shared by the native-color implementations.
 *
 * @see https://www.w3.org/TR/css-color-4/#color-conversion-code
 */
final class ColorSpaceConversion {

	/**
	 * @return array{float, float, float}
	 */
	public function linearSrgbToXyzD65( float $r, float $g, float $b ): array {
		return [
			0.41239079926595934 * $r + 0.35758433938387796 * $g + 0.1804807884018343 * $b,
			0.21263900587151027 * $r + 0.7151686787677559 * $g + 0.07219231536073371 * $b,
			0.01933081871559182 * $r + 0.11919477979462598 * $g + 0.9505321522496607 * $b,
		];
	}

	/**
	 * @return array{float, float, float}
	 */
	public function xyzD65ToLinearSrgb( float $x, float $y, float $z ): array {
		return [
			+3.2409699419045226 * $x - 1.537383177570094 * $y - 0.4986107602930034 * $z,
			-0.9692436362808796 * $x + 1.8759675015077202 * $y + 0.04155505740717559 * $z,
			+0.05563007969699366 * $x - 0.20397695888897652 * $y + 1.0569715142428786 * $z,
		];
	}

	public function normalizeHue( float $hue ): float {
		$hue = fmod($hue, 360.0);

		return $hue < 0 ? $hue + 360.0 : $hue;
	}

}
