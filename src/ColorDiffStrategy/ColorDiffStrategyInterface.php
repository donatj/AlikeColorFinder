<?php

namespace donatj\AlikeColorFinder\ColorDiffStrategy;

use donatj\AlikeColorFinder\ColorEntry;

interface ColorDiffStrategyInterface {

	public function __invoke( ColorEntry $color1, ColorEntry $color2 );

}
