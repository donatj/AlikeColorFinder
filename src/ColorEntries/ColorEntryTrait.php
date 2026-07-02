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
	 * @return array
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
	 * @return array
	 */
	public function getLabAlphaCieArray(): array {
		$xyz = $this->getXyzaArray();

		// Observer = 2°, Illuminant = D65
		$xyz['x'] /= 95.047;
		$xyz['y'] /= 100;
		$xyz['z'] /= 108.883;

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
			'Alpha' => $this->a,
		];
	}

	/**
	 * Returns the simplest CSS representation.
	 * Default behavior: hex/rgba if in sRGB gamut, native format otherwise.
	 * Classes can override for custom behavior (e.g., XyzColorEntry uses custom epsilon).
	 *
	 * @param float $epsilon
	 */
	public function getSimplestCssString( float $epsilon = 0.001 ): string {
		if( $this->isInSrgbGamut($epsilon) ) {
			// Can be losslessly represented in sRGB
			if( $this->isAlphaHexCompatible($epsilon) ) {
				return $this->getRgbHexString();
			}

			return $this->getRgbaString();
		}

		// Out of sRGB gamut; return native format
		return $this->getNativeCssString();
	}

	/**
	 * Check if alpha can be losslessly represented as 2-digit hex (00-FF).
	 *
	 * @param float $epsilon Tolerance for round-trip conversion (default 0.001)
	 * @return bool True if alpha round-trips through hex without loss
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

		// XYZ D65 to linear sRGB
		$linear = [
			+3.2404542 * $x - 1.5371385 * $y - 0.4985314 * $z,
			-0.9692660 * $x + 1.8760108 * $y + 0.0415560 * $z,
			+0.0556434 * $x - 0.2040259 * $y + 1.0572252 * $z,
		];

		// Check if all components are within [0, 1] with epsilon tolerance
		return $linear[0] >= -$epsilon && $linear[0] <= 1 + $epsilon
			&& $linear[1] >= -$epsilon && $linear[1] <= 1 + $epsilon
			&& $linear[2] >= -$epsilon && $linear[2] <= 1 + $epsilon;
	}

	/**
	 * Apply sRGB gamma and clamp to [0, 255].
	 */
	protected static function linearToSrgb255( float $c ): float {
		$gamma = $c <= 0.0031308 ? 12.92 * $c : 1.055 * ($c ** (1 / 2.4)) - 0.055;

		return max(0.0, min(255.0, $gamma * 255));
	}

}
