<?php

namespace donatj\AlikeColorFinder\ColorDiffStrategy;

use donatj\AlikeColorFinder\ColorEntry;

class Absolute implements ColorDiffStrategyInterface {

	public function __invoke( ColorEntry $color1, ColorEntry $color2 ) {
		$rgba1 = $color1->getUnclampedRgbaArray();
		$rgba2 = $color2->getUnclampedRgbaArray();

		return abs($rgba1['r'] - $rgba2['r']) +
				abs($rgba1['g'] - $rgba2['g']) +
				abs($rgba1['b'] - $rgba2['b']) +
				(abs($rgba1['a'] - $rgba2['a']) * 255);
	}

}
