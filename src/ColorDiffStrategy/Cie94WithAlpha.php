<?php

namespace donatj\AlikeColorFinder\ColorDiffStrategy;

use donatj\AlikeColorFinder\ColorEntry;

class Cie94WithAlpha implements ColorDiffStrategyInterface {

	function __invoke( ColorEntry $color1, ColorEntry $color2 ) {
		$Kl = 1.0;
		$K1 = .045;
		$K2 = 0.015;
		$Kc = 1.0;
		$Kh = 1.0;

		$f1 = $color1->getLabAlphaCieArray();
		$f2 = $color2->getLabAlphaCieArray();

		$deltaL     = $f2['l'] - $f1['l'];
		$deltaA     = $f2['a'] - $f1['a'];
		$deltaB     = $f2['b'] - $f1['b'];
		$c1         = sqrt($f1['a'] * $f1['a'] + $f1['b'] * $f1['b']);
		$c2         = sqrt($f2['a'] * $f2['a'] + $f2['b'] * $f2['b']);
		$deltaC     = $c2 - $c1;
		$deltaH     = $deltaA * $deltaA + $deltaB * $deltaB - $deltaC * $deltaC;
		$deltaH     = $deltaH < 0 ? 0 : sqrt($deltaH);
		$Sl         = 1.0;
		$Sc         = 1 + $K1 * $c1;
		$Sh         = 1 + $K2 * $c1;
		$deltaLKlsl = $deltaL / ($Kl * $Sl);
		$deltaCkcsc = $deltaC / ($Kc * $Sc);
		$deltaHkhsh = $deltaH / ($Kh * $Sh);
		$deltaE     = $deltaLKlsl * $deltaLKlsl + $deltaCkcsc * $deltaCkcsc + $deltaHkhsh * $deltaHkhsh;

		$alphaDiff = abs($color1->getA() - $color2->getA()) * 255;

		return ($deltaE < 0 ? 0 : sqrt($deltaE)) + $alphaDiff;
	}

}
