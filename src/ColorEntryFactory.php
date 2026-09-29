<?php

namespace donatj\AlikeColorFinder;

use donatj\AlikeColorFinder\ColorEntries\DisplayP3ColorEntry;
use donatj\AlikeColorFinder\ColorEntries\ExtendedSrgbColorEntry;
use donatj\AlikeColorFinder\ColorEntries\LabColorEntry;
use donatj\AlikeColorFinder\ColorEntries\LchColorEntry;
use donatj\AlikeColorFinder\ColorEntries\OklabColorEntry;
use donatj\AlikeColorFinder\ColorEntries\OklchColorEntry;
use donatj\AlikeColorFinder\ColorEntries\Rec2020ColorEntry;
use donatj\AlikeColorFinder\ColorEntries\SrgbColorEntry;
use donatj\AlikeColorFinder\ColorEntries\XyzColorEntry;

class ColorEntryFactory {

	public function normalizeHue( float $hue ): float {
		$hue = fmod($hue, 360.0);

		return $hue < 0 ? $hue + 360.0 : $hue;
	}

	public function makeFromRgba( $r, $g, $b, $a ) {
		return new SrgbColorEntry($r, $g, $b, $a);
	}

	public function makeFromRgb( $r, $g, $b ) {
		return $this->makeFromRgba($r, $g, $b, 1);
	}

	public function makeFromHexString( $hex ) {
		$hex = str_replace('#', '', $hex);
		$a   = 1;

		if( strlen($hex) === 3 ) {
			$r = hexdec($hex[0] . $hex[0]);
			$g = hexdec($hex[1] . $hex[1]);
			$b = hexdec($hex[2] . $hex[2]);
		} elseif( strlen($hex) === 4 ) {
			$r = hexdec($hex[0] . $hex[0]);
			$g = hexdec($hex[1] . $hex[1]);
			$b = hexdec($hex[2] . $hex[2]);
			$a = hexdec($hex[3] . $hex[3]) / 255;
		} elseif( strlen($hex) === 6 ) {
			$r = hexdec(substr($hex, 0, 2));
			$g = hexdec(substr($hex, 2, 2));
			$b = hexdec(substr($hex, 4, 2));
		} elseif( strlen($hex) === 8 ) {
			$r = hexdec(substr($hex, 0, 2));
			$g = hexdec(substr($hex, 2, 2));
			$b = hexdec(substr($hex, 4, 2));
			$a = hexdec(substr($hex, 6, 2)) / 255;
		} else {
			throw new \InvalidArgumentException('Invalid Hex "' . $hex . '"');
		}

		return new SrgbColorEntry($r, $g, $b, $a);
	}

	public function makeFromHsla( $h, $s, $l, $a ) {
		$h = $this->normalizeHue($h);

		$c = (1 - abs(2 * $l - 1)) * $s;
		$x = $c * (1 - abs(fmod($h / 60, 2) - 1));
		$m = $l - ($c / 2);

		if( $h < 60 ) {
			$r = $c;
			$g = $x;
			$b = 0;
		} elseif( $h < 120 ) {
			$r = $x;
			$g = $c;
			$b = 0;
		} elseif( $h < 180 ) {
			$r = 0;
			$g = $c;
			$b = $x;
		} elseif( $h < 240 ) {
			$r = 0;
			$g = $x;
			$b = $c;
		} elseif( $h < 300 ) {
			$r = $x;
			$g = 0;
			$b = $c;
		} else {
			$r = $c;
			$g = 0;
			$b = $x;
		}

		$r = ($r + $m) * 255;
		$g = ($g + $m) * 255;
		$b = ($b + $m) * 255;

		return new SrgbColorEntry($r, $g, $b, $a);
	}

	public function makeFromHsl( $h, $s, $l ) {
		return $this->makeFromHsla($h, $s, $l, 1);
	}

	public function makeFromHwb( float $h, float $w, float $b, float $a = 1.0 ): ColorEntry {
		$h = $this->normalizeHue($h);

		// Normalize whiteness and blackness
		if( $w + $b >= 1.0 ) {
			$gray = $w / ($w + $b) * 255;
			return new SrgbColorEntry($gray, $gray, $gray, $a, false);
		}

		// Compute pure hue RGB (0-1 range) by sector
		$hNorm   = $h;
		$hSector = $hNorm / 60;
		$sector  = (int)$hSector % 6;
		$f       = $hSector - floor($hSector);

		switch( $sector ) {
			case 0: $pr = 1; $pg = $f; $pb = 0; break;
			case 1: $pr = 1 - $f; $pg = 1; $pb = 0; break;
			case 2: $pr = 0; $pg = 1; $pb = $f; break;
			case 3: $pr = 0; $pg = 1 - $f; $pb = 1; break;
			case 4: $pr = $f; $pg = 0; $pb = 1; break;
			default: $pr = 1; $pg = 0; $pb = 1 - $f; break;
		}

		$scale = 1 - $w - $b;
		return new SrgbColorEntry(
			($pr * $scale + $w) * 255,
			($pg * $scale + $w) * 255,
			($pb * $scale + $w) * 255,
			$a,
			false
		);
	}

	public function makeFromLab( float $l, float $aVal, float $bVal, float $a = 1.0 ): ColorEntry {
		return new LabColorEntry($l, $aVal, $bVal, $a);
	}

	public function makeFromLch( float $l, float $c, float $h, float $a = 1.0 ): ColorEntry {
		return new LchColorEntry($l, $c, $this->normalizeHue($h), $a);
	}

	public function makeFromOklab( float $l, float $aVal, float $bVal, float $a = 1.0 ): ColorEntry {
		return new OklabColorEntry($l, $aVal, $bVal, $a);
	}

	public function makeFromOklch( float $l, float $c, float $h, float $a = 1.0 ): ColorEntry {
		return new OklchColorEntry($l, $c, $this->normalizeHue($h), $a);
	}

	public function makeFromColorSpace( string $colorSpace, float $c1, float $c2, float $c3, float $a = 1.0 ): ColorEntry {
		switch( $colorSpace ) {
			case 'srgb':
				return new ExtendedSrgbColorEntry($c1, $c2, $c3, $a);

			case 'srgb-linear':
				list($x, $y, $z) = $this->linearSrgbToXyzD65($c1, $c2, $c3);

				return new XyzColorEntry($x, $y, $z, $a);

			case 'display-p3':
				return new DisplayP3ColorEntry($c1, $c2, $c3, $a);

			case 'display-p3-linear':
				list($x, $y, $z) = $this->displayP3LinearToXyzD65($c1, $c2, $c3);

				return new XyzColorEntry($x, $y, $z, $a);

			case 'a98-rgb':
				// A98-RGB uses gamma 563/256 ≈ 2.19921875
				$rLin = ($c1 >= 0 ? 1 : -1) * (abs($c1) ** (563 / 256));
				$gLin = ($c2 >= 0 ? 1 : -1) * (abs($c2) ** (563 / 256));
				$bLin = ($c3 >= 0 ? 1 : -1) * (abs($c3) ** (563 / 256));
				list($x, $y, $z) = $this->a98RgbLinearToXyzD65($rLin, $gLin, $bLin);

				return new XyzColorEntry($x, $y, $z, $a);

			case 'prophoto-rgb':
				// ProPhoto RGB uses gamma 1.8 with a linear toe
				$rLin = $this->prophotoToLinear($c1);
				$gLin = $this->prophotoToLinear($c2);
				$bLin = $this->prophotoToLinear($c3);
				list($x50, $y50, $z50) = $this->prophotorgbLinearToXyzD50($rLin, $gLin, $bLin);
				list($x, $y, $z) = $this->xyzD50ToXyzD65($x50, $y50, $z50);

				return new XyzColorEntry($x, $y, $z, $a);

			case 'rec2020':
				return new Rec2020ColorEntry($c1, $c2, $c3, $a);

			case 'xyz':
			case 'xyz-d65':
				return new XyzColorEntry($c1, $c2, $c3, $a);

			case 'xyz-d50':
				list($x, $y, $z) = $this->xyzD50ToXyzD65($c1, $c2, $c3);

				return new XyzColorEntry($x, $y, $z, $a);
		}

		throw new \LogicException("Color space '{$colorSpace}' not implemented");
	}

	// -------------------------------------------------------------------------
	// Private conversion helpers
	// -------------------------------------------------------------------------

	/** @return array{float, float, float} */
	private function xyzD50ToXyzD65( float $x, float $y, float $z ): array {
		// Bradford chromatic adaptation D50 → D65
		// @see https://www.w3.org/TR/css-color-4/#color-conversion-code
		return [
			0.955473421488075 * $x - 0.02309845494876471 * $y + 0.06325924320057072 * $z,
			-0.0283697093338637 * $x + 1.0099953980813041 * $y + 0.021041441191917323 * $z,
			0.012314014864481998 * $x - 0.020507649298898964 * $y + 1.330365926242124 * $z,
		];
	}

	/** @return array{float, float, float} */
	private function linearSrgbToXyzD65( float $r, float $g, float $b ): array {
		// CSS Color 4 linear sRGB to XYZ D65 matrix.
		// @see https://www.w3.org/TR/css-color-4/#color-conversion-code
		return [
			0.41239079926595934 * $r + 0.35758433938387796 * $g + 0.1804807884018343 * $b,
			0.21263900587151027 * $r + 0.7151686787677559 * $g + 0.07219231536073371 * $b,
			0.01933081871559182 * $r + 0.11919477979462598 * $g + 0.9505321522496607 * $b,
		];
	}

	/** @return array{float, float, float} */
	private function displayP3LinearToXyzD65( float $r, float $g, float $b ): array {
		return [
			0.4865709486482162 * $r + 0.26566769316909306 * $g + 0.1982172852343625 * $b,
			0.22897456406974884 * $r + 0.6917385218564081 * $g + 0.07928691407384083 * $b,
			0.0 * $r + 0.04511338185890264 * $g + 1.0439443689736354 * $b,
		];
	}

	/** @return array{float, float, float} */
	private function a98RgbLinearToXyzD65( float $r, float $g, float $b ): array {
		return [
			0.5766690429101305 * $r + 0.1855582379065463 * $g + 0.1882286462349947 * $b,
			0.29734497525053605 * $r + 0.6273635662554661 * $g + 0.07529145849399788 * $b,
			0.02703136138515884 * $r + 0.07068885253582723 * $g + 0.9913375842357796 * $b,
		];
	}

	private function prophotoToLinear( float $c ): float {
		$sign = $c < 0 ? -1 : 1;
		$abs  = abs($c);

		if( $abs <= 16 / 512 ) {
			return $c / 16;
		}

		return $sign * ($abs ** 1.8);
	}

	/** @return array{float, float, float} */
	private function prophotorgbLinearToXyzD50( float $r, float $g, float $b ): array {
		// CSS Color 4 ProPhoto RGB to XYZ D50 matrix.
		// @see https://drafts.csswg.org/css-color-4/#color-conversion-code
		return [
			0.7977666449006423 * $r + 0.13518129740053308 * $g + 0.0313477341283922 * $b,
			0.2880748288194013 * $r + 0.711835234241873 * $g + 0.00008993693872564 * $b,
			0.0 * $r + 0.0 * $g + 0.8251046025104601 * $b,
		];
	}

}
