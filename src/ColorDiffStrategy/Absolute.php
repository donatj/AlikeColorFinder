<?php

namespace donatj\AlikeColorFinder\ColorDiffStrategy;

use donatj\AlikeColorFinder\ColorEntry;

class Absolute implements ColorDiffStrategyInterface {

	public function __invoke( ColorEntry $color1, ColorEntry $color2 ) {
		return abs($color1->getR() - $color2->getR()) +
				abs($color1->getG() - $color2->getG()) +
				abs($color1->getB() - $color2->getB()) +
				(abs($color1->getA() - $color2->getA()) * 255);
	}

}
