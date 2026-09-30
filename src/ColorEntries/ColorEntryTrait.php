<?php

namespace donatj\AlikeColorFinder\ColorEntries;

trait ColorEntryTrait {

	public function getRgbaString(): string {
		$r = round($this->getR());
		$g = round($this->getG());
		$b = round($this->getB());

		return "rgba({$r},{$g},{$b},{$this->a})";
	}

	public function getRgbHexString(): string {
		$hex = str_pad(dechex((int)round($this->getR())), 2, "0", STR_PAD_LEFT);
		$hex .= str_pad(dechex((int)round($this->getG())), 2, "0", STR_PAD_LEFT);
		$hex .= str_pad(dechex((int)round($this->getB())), 2, "0", STR_PAD_LEFT);

		$a = $this->getA();
		if( $a < 1.0 ) {
			$hex .= str_pad(dechex((int)round($a * 255)), 2, "0", STR_PAD_LEFT);
		}

		if( preg_match('/^(.)\1(.)\2(.)\3(?:(.)\4)?$/u', $hex, $regs) ) {
			$hex = $regs[1][0] . $regs[2][0] . $regs[3][0];

			if( isset($regs[4]) ) {
				$hex .= $regs[4][0];
			}
		}

		return '#' . $hex;
	}

	/**
	 * @return array{r: float, g: float, b: float, a: float}
	 */
	public function getRgbaArray(): array {
		return [
			'r' => $this->getR(),
			'g' => $this->getG(),
			'b' => $this->getB(),
			'a' => $this->a,
		];
	}

	/**
	 * @return array{r: float, g: float, b: float, a: float}
	 */
	public function getUnclampedRgbaArray(): array {
		$linear = $this->getLinearSrgb();

		return [
			'r' => $this->linearToUnclampedSrgb255($linear[0]),
			'g' => $this->linearToUnclampedSrgb255($linear[1]),
			'b' => $this->linearToUnclampedSrgb255($linear[2]),
			'a' => $this->a,
		];
	}

	/**
	 * @return array{l: float, a: float, b: float, alpha: float}
	 */
	public function getLabAlphaCieArray(): array {
		$xyz = $this->getXyzaArray();

		// Observer = 2°, Illuminant = D65
		$xyz['x'] /= 95.047;
		$xyz['y'] /= 100;
		$xyz['z'] /= 108.883;

		// Unset alpha before array_map to avoid unnecessary computation
		unset($xyz['a']);

		$xyz = array_map(function( $item ) {
			if( $item > 0.008856 ) {
				return pow($item, 1 / 3);
			}

			return (7.787 * $item) + (16 / 116);
		}, $xyz);

		return [
			'l'     => (116 * $xyz['y']) - 16,
			'a'     => 500 * ($xyz['x'] - $xyz['y']),
			'b'     => 200 * ($xyz['y'] - $xyz['z']),
			'alpha' => $this->a,
		];
	}

	/**
	 * Returns the simplest CSS representation.
	 * Default behavior: hex/rgba if in sRGB gamut, native format otherwise.
	 * Classes can override for custom behavior.
	 */
	public function getSimplestCssString( float $epsilon = 0.001 ): string {
		if( $this->isInSrgbGamut($epsilon) ) {
			// In-gamut colors use an sRGB representation.
			if( $this->isAlphaHexCompatible($epsilon) ) {
				return $this->getRgbHexString();
			}

			return $this->getRgbaString();
		}

		// Out of sRGB gamut; return native format
		return $this->getNativeCssString();
	}

	/**
	 * Check if alpha is sufficiently close to an 8-bit hex value (00-FF).
	 *
	 * @param float $epsilon Maximum permitted round-trip error (default 0.001)
	 * @return bool True if alpha differs from its 8-bit representation by no more than epsilon
	 */
	public function isAlphaHexCompatible( float $epsilon = 0.001 ): bool {
		$hexValue  = round($this->a * 255);
		$roundTrip = $hexValue / 255;

		return abs($this->a - $roundTrip) <= $epsilon;
	}

	/**
	 * Check if this color is within the sRGB gamut (with floating-point tolerance).
	 *
	 * @param float $epsilon Tolerance for gamut boundary (default 0.001)
	 * @return bool True if the color can be represented in sRGB without clipping
	 */
	public function isInSrgbGamut( float $epsilon = 0.001 ): bool {
		// Convert to XYZ then to linear sRGB to check bounds
		$xyz = $this->getXyzaArray();
		$x   = $xyz['x'] / 100;
		$y   = $xyz['y'] / 100;
		$z   = $xyz['z'] / 100;

		$linear = $this->xyzD65ToLinearSrgb($x, $y, $z);

		// Check if all components are within [0, 1] with epsilon tolerance
		return $linear[0] >= -$epsilon && $linear[0] <= 1 + $epsilon
			&& $linear[1] >= -$epsilon && $linear[1] <= 1 + $epsilon
			&& $linear[2] >= -$epsilon && $linear[2] <= 1 + $epsilon;
	}

	/**
	 * Apply sRGB gamma and clamp to [0, 255].
	 */
	protected function linearToSrgb255( float $c ): float {
		return max(0.0, min(255.0, $this->linearToUnclampedSrgb255($c)));
	}

	/**
	 * Apply sRGB gamma without clipping for color comparisons.
	 */
	protected function linearToUnclampedSrgb255( float $c ): float {
		$sign  = $c < 0 ? -1 : 1;
		$abs   = abs($c);
		$gamma = $abs <= 0.0031308 ? 12.92 * $abs : 1.055 * ($abs ** (1 / 2.4)) - 0.055;

		return $sign * $gamma * 255;
	}

	/**
	 * @return array{float, float, float}
	 * @see https://www.w3.org/TR/css-color-4/#color-conversion-code
	 */
	protected function linearSrgbToXyzD65( float $r, float $g, float $b ): array {
		return [
			0.41239079926595934 * $r + 0.35758433938387796 * $g + 0.1804807884018343 * $b,
			0.21263900587151027 * $r + 0.7151686787677559 * $g + 0.07219231536073371 * $b,
			0.01933081871559182 * $r + 0.11919477979462598 * $g + 0.9505321522496607 * $b,
		];
	}

	/**
	 * @return array{float, float, float}
	 * @see https://www.w3.org/TR/css-color-4/#color-conversion-code
	 */
	protected function xyzD65ToLinearSrgb( float $x, float $y, float $z ): array {
		return [
			+3.2409699419045226 * $x - 1.537383177570094 * $y - 0.4986107602930034 * $z,
			-0.9692436362808796 * $x + 1.8759675015077202 * $y + 0.04155505740717559 * $z,
			+0.05563007969699366 * $x - 0.20397695888897652 * $y + 1.0569715142428786 * $z,
		];
	}

}
