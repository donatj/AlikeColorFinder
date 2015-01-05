<?php

namespace donatj\AlikeColorFinder;

class ColorEntryFactory {

	public function makeFromRgba( $r, $g, $b, $a ) {
		return new ColorEntry($r, $g, $b, $a);
	}

	public function makeFromRgb( $r, $g, $b ) {
		return new ColorEntry($r, $g, $b);
	}

	public function makeFromHexString( $hex ) {
		$hex = str_replace("#", "", $hex);

		if( strlen($hex) == 3 ) {
			$r = hexdec(substr($hex, 0, 1) . substr($hex, 0, 1));
			$g = hexdec(substr($hex, 1, 1) . substr($hex, 1, 1));
			$b = hexdec(substr($hex, 2, 1) . substr($hex, 2, 1));
		} elseif( strlen($hex) == 6 ) {
			$r = hexdec(substr($hex, 0, 2));
			$g = hexdec(substr($hex, 2, 2));
			$b = hexdec(substr($hex, 4, 2));
		} else {
			throw new \InvalidArgumentException('Invalid Hex "'. $hex .'"');
		}

		return new ColorEntry($r, $g, $b, 1);
	}

}
