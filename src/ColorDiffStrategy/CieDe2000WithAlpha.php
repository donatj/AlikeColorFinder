<?php

namespace donatj\AlikeColorFinder\ColorDiffStrategy;

use donatj\AlikeColorFinder\ColorEntry;

class CieDe2000WithAlpha implements ColorDiffStrategyInterface {

	/**
	 * Color Distance CeiDe2000
	 *
	 * Taken from:
	 *
	 * @link https://github.com/supplyhog/phpOptics/blob/e94ac9cf67fb61b89ad23bee01ae32365e587afa/OpticsColorPoint.php
	 *
	 * The MIT License (MIT)
	 *
	 * Copyright (c) 2013 SupplyHog, Inc.
	 *
	 * Permission is hereby granted, free of charge, to any person obtaining a copy of
	 * this software and associated documentation files (the "Software"), to deal in
	 * the Software without restriction, including without limitation the rights to
	 * use, copy, modify, merge, publish, distribute, sublicense, and/or sell copies of
	 * the Software, and to permit persons to whom the Software is furnished to do so,
	 * subject to the following conditions:
	 *
	 * The above copyright notice and this permission notice shall be included in all
	 * copies or substantial portions of the Software.
	 *
	 * THE SOFTWARE IS PROVIDED "AS IS", WITHOUT WARRANTY OF ANY KIND, EXPRESS OR
	 * IMPLIED, INCLUDING BUT NOT LIMITED TO THE WARRANTIES OF MERCHANTABILITY, FITNESS
	 * FOR A PARTICULAR PURPOSE AND NONINFRINGEMENT. IN NO EVENT SHALL THE AUTHORS OR
	 * COPYRIGHT HOLDERS BE LIABLE FOR ANY CLAIM, DAMAGES OR OTHER LIABILITY, WHETHER
	 * IN AN ACTION OF CONTRACT, TORT OR OTHERWISE, ARISING FROM, OUT OF OR IN
	 * CONNECTION WITH THE SOFTWARE OR THE USE OR OTHER DEALINGS IN THE SOFTWARE.
	 *
	 * Which was based on:
	 * @link http://www.ece.rochester.edu/~gsharma/ciede2000/ciede2000noteCRNA.pdf
	 *
	 * @param \donatj\AlikeColorFinder\ColorEntry $color1
	 * @param \donatj\AlikeColorFinder\ColorEntry $color2
	 * @return float
	 * @throws \Exception
	 */
	public function __invoke( ColorEntry $color1, ColorEntry $color2 ) {
		$f1 = $color1->getLabAlphaCieArray();
		$f2 = $color2->getLabAlphaCieArray();

		$kl   = $kc = $kh = 1.0;
		$barL = ($f1['l'] + $f2['l']) / 2.0;
		//(Numbers correspond to http://www.ece.rochester.edu/~gsharma/ciede2000/ciede2000noteCRNA.pdf eq)
		//2
		$helperB1Sq = pow($f1['b'], 2);
		$helperB2Sq = pow($f2['b'], 2);
		$c1         = sqrt(pow($f1['a'], 2) + $helperB1Sq);
		$c2         = sqrt(pow($f2['a'], 2) + $helperB2Sq);
		//3
		$barC = ($c1 + $c2) / 2.0;
		//4
		$helperPow7 = sqrt(pow($barC, 7) / (pow($barC, 7) + 6103515625));
		$g          = 0.5 * (1 - $helperPow7);
		//5
		$primeA1 = (1 + $g) * $f1['a'];
		$primeA2 = (1 + $g) * $f2['a'];
		//6
		$primeC1 = sqrt(pow($primeA1, 2) + $helperB1Sq);
		$primeC2 = sqrt(pow($primeA2, 2) + $helperB2Sq);
		//7
		if( $f1['b'] === 0 && $primeA1 === 0 ) {
			$primeH1 = 0;
		} else {
			$primeH1 = (atan2($f1['b'], $primeA1) + 2 * M_PI) * (180 / M_PI);
		}
		if( $f2['b'] === 0 && $primeA2 === 0 ) {
			$primeH2 = 0;
		} else {
			$primeH2 = (atan2($f2['b'], $primeA2) + 2 * M_PI) * (180 / M_PI);
		}
		//8
		$deltaLPrime = $f2['l'] - $f1['l'];
		//9
		$deltaCPrime = $primeC2 - $primeC1;
		//10
		$helperH = $primeH2 - $primeH1;
		if( $primeC1 * $primeC2 === 0 ) {
			$deltahPrime = 0;
		} elseif( abs($helperH) <= 180 ) {
			$deltahPrime = $helperH;
		} elseif( $helperH > 180 ) {
			$deltahPrime = $helperH - 360.0;
		} elseif( $helperH < -180 ) {
			$deltahPrime = $helperH + 360.0;
		} else {
			throw new \Exception('Invalid delta h\'');
		}
		//11
		$deltaHPrime = 2 * sqrt($primeC1 * $primeC2) * sin(($deltahPrime / 2.0) * (M_PI / 180));
		//12
		$barLPrime = ($f1['l'] + $f2['l']) / 2.0;
		//13
		$barCPrime = ($primeC1 + $primeC2) / 2.0;
		//14
		$helperH = abs($primeH1 - $primeH2);
		if( $primeC1 * $primeC2 === 0 ) {
			$barHPrime = $primeH1 + $primeH2;
		} elseif( $helperH <= 180 ) {
			$barHPrime = ($primeH1 + $primeH2) / 2.0;
		} elseif( $helperH > 180 && ($primeH1 + $primeH2) < 360 ) {
			$barHPrime = ($primeH1 + $primeH2 + 360) / 2.0;
		} elseif( $helperH > 180 && ($primeH1 + $primeH2) >= 360 ) {
			$barHPrime = ($primeH1 + $primeH2 - 360) / 2.0;
		} else {
			throw new \Exception('Invalid bar h\'');
		}
		//15
		$t = 1 - .17 * cos(($barHPrime - 30) * (M_PI / 180)) + .24 * cos((2 * $barHPrime) * (M_PI / 180)) + .32 * cos((3 * $barHPrime + 6) * (M_PI / 180)) - .2 * cos((4 * $barHPrime - 63) * (M_PI / 180));
		//16
		$deltaTheta = 30 * exp(-1 * pow((($barHPrime - 275) / 25), 2));
		//17
		$rc = 2 * $helperPow7;
		//18
		$slHelper = pow($barLPrime - 50, 2);
		$sl       = 1 + ((0.015 * $slHelper) / sqrt(20 + $slHelper));
		//19
		$sc = 1 + 0.046 * $barCPrime;
		//20
		$sh = 1 + 0.015 * $barCPrime * $t;
		//21
		$rt = -1 * sin((2 * $deltaTheta) * (M_PI / 180)) * $rc;
		//22
		$deltaESquared = pow($deltaLPrime / ($kl * $sl), 2) +
						 pow($deltaCPrime / ($kc * $sc), 2) +
						 pow($deltaHPrime / ($kh * $sh), 2) +
						 ($rt * ($deltaCPrime / ($kc * $sc)) * ($deltaHPrime / ($kh * $sh)));
		$deltaE        = sqrt($deltaESquared);

		$alphaDiff = abs($color1->getA() - $color2->getA()) * 255;

		return $deltaE + $alphaDiff;
	}
}