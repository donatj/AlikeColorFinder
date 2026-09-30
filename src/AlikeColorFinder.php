<?php

namespace donatj\AlikeColorFinder;

use donatj\AlikeColorFinder\ColorDiffStrategy\Absolute;
use donatj\AlikeColorFinder\ColorDiffStrategy\ColorDiffStrategyInterface;

class AlikeColorFinder {

	/** @var array<ColorEntry> */
	protected array $colors;

	protected ColorEntryFactory $factory;

	protected ColorDiffStrategyInterface $colorDiffer;

	/**
	 * @param array<ColorEntry> $colors
	 */
	public function __construct(
		array $colors,
		?ColorEntryFactory $colorEntryFactory = null,
		?ColorDiffStrategyInterface $colorDiffer = null
	) {
		$this->colors = $colors;

		if( $colorEntryFactory !== null ) {
			$this->factory = $colorEntryFactory;
		} else {
			$this->factory = new ColorEntryFactory;
		}

		if( $colorDiffer !== null ) {
			$this->colorDiffer = $colorDiffer;
		} else {
			$this->colorDiffer = new Absolute;
		}
	}

	/**
	 * @return list<array{master: ColorEntry, children: non-empty-list<array{diff: float, color: ColorEntry}>}>
	 */
	public function getAlikeColorsWithinTolerance( float $tolerance ): array {
		$output = [ ];

		$colorStack = $this->colors;
		while( count($colorStack) > 1 ) {
			/**
			 * @var \donatj\AlikeColorFinder\ColorEntry $colorOne
			 * @var \donatj\AlikeColorFinder\ColorEntry $colorTwo
			 */
			$colorOne = array_pop($colorStack);
			$children = [];

			foreach( $colorStack as $colorTwo ) {
				$diff = $this->colorDiffer->__invoke($colorOne, $colorTwo);

				if( $diff <= $tolerance ) {
					$children[] = [
						'diff'  => $diff,
						'color' => $colorTwo,
					];
				}
			}

			if( $children ) {
				usort($children, function ( $a, $b ) {
					if( $a['diff'] == $b['diff'] ) {
						return 0;
					}

					return ($a['diff'] < $b['diff']) ? -1 : 1;
				});

				$output[] = [
					'master'   => $colorOne,
					'children' => $children,
				];
			}
		}

		return $output;
	}

}
