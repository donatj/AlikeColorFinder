<?php

namespace donatj\AlikeColorFinder;

class ColorEntryFactory {

	public function makeFromRgba( $r, $g, $b, $a ) {
		return new ColorEntry($r, $g, $b, $a);
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

		return new ColorEntry($r, $g, $b, $a);
	}

	public function makeFromHsla( $h, $s, $l, $a ) {
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

		$r = round(($r + $m) * 255);
		$g = round(($g + $m) * 255);
		$b = round(($b + $m) * 255);

		return new ColorEntry($r, $g, $b, $a);
	}

	public function makeFromHsl( $h, $s, $l ) {
		return $this->makeFromHsla($h, $s, $l, 1);
	}

	public function makeFromHwb( $h, $w, $b, $a = 1.0 ) {
		// Normalize whiteness and blackness
		if( $w + $b >= 1.0 ) {
			$gray = round($w / ($w + $b) * 255);
			return new ColorEntry($gray, $gray, $gray, $a);
		}

		// Compute pure hue RGB (0-1 range) by sector
		$hNorm   = fmod($h, 360);
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
		return new ColorEntry(
			round(($pr * $scale + $w) * 255),
			round(($pg * $scale + $w) * 255),
			round(($pb * $scale + $w) * 255),
			$a
		);
	}

	public function makeFromLab( $l, $aVal, $bVal, $a = 1.0 ) {
		list($x50, $y50, $z50) = self::labD50ToXyzD50($l, $aVal, $bVal);
		list($x65, $y65, $z65) = self::xyzD50ToXyzD65($x50, $y50, $z50);

		return ColorEntry::fromXyzD65($x65, $y65, $z65, $a);
	}

	public function makeFromLch( $l, $c, $h, $a = 1.0 ) {
		$hRad = $h * M_PI / 180;
		return $this->makeFromLab($l, $c * cos($hRad), $c * sin($hRad), $a);
	}

	public function makeFromOklab( $l, $aVal, $bVal, $a = 1.0 ) {
		// OKLab to LMS (via cube-root-compressed LMS)
		$lPrime = $l + 0.3963377774 * $aVal + 0.2158037573 * $bVal;
		$mPrime = $l - 0.1055613458 * $aVal - 0.0638541728 * $bVal;
		$sPrime = $l - 0.0894841775 * $aVal - 1.2914855480 * $bVal;

		$lm = $lPrime ** 3;
		$mm = $mPrime ** 3;
		$sm = $sPrime ** 3;

		// LMS to linear sRGB
		$rLin = +4.0767416621 * $lm - 3.3077115913 * $mm + 0.2309699292 * $sm;
		$gLin = -1.2684380046 * $lm + 2.6097574011 * $mm - 0.3413193965 * $sm;
		$bLin = -0.0041960863 * $lm - 0.7034186147 * $mm + 1.7076147010 * $sm;

		list($x, $y, $z) = self::linearSrgbToXyzD65($rLin, $gLin, $bLin);

		return ColorEntry::fromXyzD65($x, $y, $z, $a);
	}

	public function makeFromOklch( $l, $c, $h, $a = 1.0 ) {
		$hRad = $h * M_PI / 180;
		return $this->makeFromOklab($l, $c * cos($hRad), $c * sin($hRad), $a);
	}

	public function makeFromColorSpace( $colorSpace, $c1, $c2, $c3, $a = 1.0 ) {
		switch( $colorSpace ) {
			case 'srgb':
				// Gamma-encoded sRGB 0–1; snap to the integer sRGB lattice
				return new ColorEntry(
					round(max(0.0, min(1.0, $c1)) * 255),
					round(max(0.0, min(1.0, $c2)) * 255),
					round(max(0.0, min(1.0, $c3)) * 255),
					$a
				);

			case 'srgb-linear':
				list($x, $y, $z) = self::linearSrgbToXyzD65($c1, $c2, $c3);

				return ColorEntry::fromXyzD65($x, $y, $z, $a);

			case 'display-p3':
				$rLin = self::srgbToLinear($c1);
				$gLin = self::srgbToLinear($c2);
				$bLin = self::srgbToLinear($c3);
				list($x, $y, $z) = self::displayP3LinearToXyzD65($rLin, $gLin, $bLin);

				return ColorEntry::fromXyzD65($x, $y, $z, $a);

			case 'a98-rgb':
				// A98-RGB uses gamma 563/256 ≈ 2.19921875
				$rLin = ($c1 >= 0 ? 1 : -1) * (abs($c1) ** (563 / 256));
				$gLin = ($c2 >= 0 ? 1 : -1) * (abs($c2) ** (563 / 256));
				$bLin = ($c3 >= 0 ? 1 : -1) * (abs($c3) ** (563 / 256));
				list($x, $y, $z) = self::a98RgbLinearToXyzD65($rLin, $gLin, $bLin);

				return ColorEntry::fromXyzD65($x, $y, $z, $a);

			case 'prophoto-rgb':
				// ProPhoto RGB uses gamma 1.8 with a linear toe
				$rLin = self::prophotoToLinear($c1);
				$gLin = self::prophotoToLinear($c2);
				$bLin = self::prophotoToLinear($c3);
				list($x50, $y50, $z50) = self::prophotorgbLinearToXyzD50($rLin, $gLin, $bLin);
				list($x, $y, $z) = self::xyzD50ToXyzD65($x50, $y50, $z50);

				return ColorEntry::fromXyzD65($x, $y, $z, $a);

			case 'rec2020':
				// Rec2020 uses a transfer function similar to sRGB
				$rLin = self::rec2020ToLinear($c1);
				$gLin = self::rec2020ToLinear($c2);
				$bLin = self::rec2020ToLinear($c3);
				list($x, $y, $z) = self::rec2020LinearToXyzD65($rLin, $gLin, $bLin);

				return ColorEntry::fromXyzD65($x, $y, $z, $a);

			case 'xyz':
			case 'xyz-d65':
				return ColorEntry::fromXyzD65($c1, $c2, $c3, $a);

			case 'xyz-d50':
				list($x, $y, $z) = self::xyzD50ToXyzD65($c1, $c2, $c3);

				return ColorEntry::fromXyzD65($x, $y, $z, $a);
		}

		throw new \LogicException("Color space '{$colorSpace}' not implemented");
	}

	// -------------------------------------------------------------------------
	// Private conversion helpers
	// -------------------------------------------------------------------------

	private static function labD50ToXyzD50( $l, $a, $b ) {
		$kappa   = 24389 / 27;
		$epsilon = 216 / 24389;

		// D50 white point
		$d50X = 0.3457 / 0.3585;
		$d50Y = 1.0;
		$d50Z = (1.0 - 0.3457 - 0.3585) / 0.3585;

		$fy = ($l + 16) / 116;
		$fx = $a / 500 + $fy;
		$fz = $fy - $b / 200;

		$x = ($fx ** 3 > $epsilon) ? ($fx ** 3) : ((116 * $fx - 16) / $kappa);
		$y = ($l > 8) ? (($l + 16) / 116) ** 3 : ($l / $kappa);
		$z = ($fz ** 3 > $epsilon) ? ($fz ** 3) : ((116 * $fz - 16) / $kappa);

		return [ $x * $d50X, $y * $d50Y, $z * $d50Z ];
	}

	private static function xyzD50ToXyzD65( $x, $y, $z ) {
		// Bradford chromatic adaptation D50 → D65
		return [
			0.9554734527042182 * $x - 0.02301801888092314 * $y + 0.0632352294227355 * $z,
			-0.0283697093338637 * $x + 1.0099953980813410 * $y + 0.0210413696583471 * $z,
			0.0123140016883655 * $x - 0.0205076964334771 * $y + 1.3303659366080753 * $z,
		];
	}

	private static function linearSrgbToXyzD65( $r, $g, $b ) {
		// Observer 2°, Illuminant D65
		return [
			0.4124 * $r + 0.3576 * $g + 0.1805 * $b,
			0.2126 * $r + 0.7152 * $g + 0.0722 * $b,
			0.0193 * $r + 0.1192 * $g + 0.9505 * $b,
		];
	}

	private static function xyzD65ToLinearSrgb( $x, $y, $z ) {
		return [
			+3.2404542 * $x - 1.5371385 * $y - 0.4985314 * $z,
			-0.9692660 * $x + 1.8760108 * $y + 0.0415560 * $z,
			+0.0556434 * $x - 0.2040259 * $y + 1.0572252 * $z,
		];
	}

	private static function linearToSrgbGamma( $c ) {
		if( $c <= 0.0031308 ) {
			return 12.92 * $c;
		}
		return 1.055 * ($c ** (1 / 2.4)) - 0.055;
	}

	private static function linearToSrgb255( $c ) {
		return max(0, min(255, round(self::linearToSrgbGamma($c) * 255)));
	}

	private static function srgbToLinear( $c ) {
		if( $c <= 0.04045 ) {
			return $c / 12.92;
		}
		return (($c + 0.055) / 1.055) ** 2.4;
	}

	private static function displayP3LinearToXyzD65( $r, $g, $b ) {
		return [
			0.4865709486482162 * $r + 0.26566769316909306 * $g + 0.1982172852343625 * $b,
			0.22897456406974884 * $r + 0.6917385218564081 * $g + 0.07928691407384083 * $b,
			0.0 * $r + 0.04511338185890264 * $g + 1.0439443689736354 * $b,
		];
	}

	private static function a98RgbLinearToXyzD65( $r, $g, $b ) {
		return [
			0.5766690429101305 * $r + 0.1855582379065463 * $g + 0.1882286462349947 * $b,
			0.29734497525053605 * $r + 0.6273635662554661 * $g + 0.07529145849399788 * $b,
			0.02703136138515884 * $r + 0.07068885253582723 * $g + 0.9913375842357796 * $b,
		];
	}

	private static function prophotoToLinear( $c ) {
		if( $c <= 16 / 512 ) {
			return $c / 16;
		}
		return $c ** 1.8;
	}

	private static function prophotorgbLinearToXyzD50( $r, $g, $b ) {
		return [
			0.7977604896723027 * $r + 0.13518583717574031 * $g + 0.03135495205777543 * $b,
			0.2880711282292934 * $r + 0.7118432178101014 * $g + 0.00008565396060525902 * $b,
			0.0 * $r + 0.0 * $g + 0.8251046025104601 * $b,
		];
	}

	private static function rec2020ToLinear( $c ) {
		$alpha = 1.09929682680944;
		$beta  = 0.018053968510807;
		if( $c < $beta * 4.5 ) {
			return $c / 4.5;
		}
		return (($c + $alpha - 1) / $alpha) ** (1 / 0.45);
	}

	private static function rec2020LinearToXyzD65( $r, $g, $b ) {
		return [
			0.6369580483012914 * $r + 0.14461690358620832 * $g + 0.1688809751641721 * $b,
			0.2627002120112671 * $r + 0.6779980715188708 * $g + 0.05930171646986196 * $b,
			0.0 * $r + 0.028072693049087428 * $g + 1.0609850577107909 * $b,
		];
	}

}
