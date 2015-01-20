<?php

namespace donatj\AlikeColorFinder;

use donatj\AlikeColorFinder\ColorDiffStrategy\Absolute;
use donatj\AlikeColorFinder\ColorDiffStrategy\ColorDiffStrategyInterface;

class AlikeColorFinder {

	/**
	 * @var ColorEntry[]
	 */
	protected $colors;

	/**
	 * @var \donatj\AlikeColorFinder\ColorEntryFactory
	 */
	protected $factory;

	/**
	 * @var \donatj\AlikeColorFinder\ColorDiffStrategy\ColorDiffStrategyInterface
	 */
	protected $colorDiffer;

	public function __construct( array $colors, ColorEntryFactory $colorEntryFactory = null, ColorDiffStrategyInterface $colorDiffer = null ) {
		$this->colors = $colors;

		if( !is_null($colorEntryFactory) ) {
			$this->factory = $colorEntryFactory;
		} else {
			$this->factory = new ColorEntryFactory();
		}

		if( !is_null($colorDiffer) ) {
			$this->colorDiffer = $colorDiffer;
		} else {
			$this->colorDiffer = new Absolute();
		}
	}

	/**
	 * @param int $tolerance
	 * @return array
	 */
	public function getAlikeColorsWithinTolerance( $tolerance ) {
		$output = [ ];

		$colorStack = $this->colors;
		while( count($colorStack) > 1 ) {
			/**
			 * @var $colorOne \donatj\AlikeColorFinder\ColorEntry
			 * @var $colorTwo \donatj\AlikeColorFinder\ColorEntry
			 */
			$colorOne = array_pop($colorStack);
			$row      = [ ];

			foreach( $colorStack as $colorTwo ) {
				$diff = $this->colorDiffer->__invoke($colorOne, $colorTwo);

				if( $diff <= $tolerance ) {
					if( !$row ) {
						$row['master']   = $colorOne;
						$row['children'] = [ ];
					}
					$row['children'][] = [
						'diff'  => $diff,
						'color' => $colorTwo,
					];
				}
			}
			if( $row ) {

				usort($row['children'], function ( $a, $b ) {
					if( $a['diff'] == $b['diff'] ) {
						return 0;
					}

					return ($a['diff'] < $b['diff']) ? -1 : 1;
				});

				$output[] = $row;
			}
		}

		return $output;
	}


}