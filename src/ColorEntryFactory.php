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

		if( strlen($hex) === 3 ) {
			$r = hexdec(substr($hex, 0, 1) . substr($hex, 0, 1));
			$g = hexdec(substr($hex, 1, 1) . substr($hex, 1, 1));
			$b = hexdec(substr($hex, 2, 1) . substr($hex, 2, 1));
		} elseif( strlen($hex) === 6 ) {
			$r = hexdec(substr($hex, 0, 2));
			$g = hexdec(substr($hex, 2, 2));
			$b = hexdec(substr($hex, 4, 2));
		} else {
			throw new \InvalidArgumentException('Invalid Hex "' . $hex . '"');
		}

		return new ColorEntry($r, $g, $b, 1);
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

		$r = ($r + $m) * 255;
		$g = ($g + $m) * 255;
		$b = ($b + $m) * 255;

		return new ColorEntry($r, $g, $b, $a);
	}

	public function makeFromHsl( $h, $s, $l ) {
		return $this->makeFromHsla($h, $s, $l, 1);
	}

}
