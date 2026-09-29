<?php

namespace donatj\AlikeColorFinder;

/**
 * Tracks distinct CSS string representations that map to the same color value.
 */
trait ColorInstanceTrait {

	/** @var array<string, int> */
	protected array $distinctInstances = [];

	public function addInstance( string $instance ): void {
		if( !isset($this->distinctInstances[$instance]) ) {
			$this->distinctInstances[$instance] = 0;
		}
		$this->distinctInstances[$instance]++;
	}

	public function getInstanceTotal(): int {
		return array_sum($this->distinctInstances);
	}

	/**
	 * @return string[]
	 */
	public function getDistinctInstances(): array {
		return array_keys($this->distinctInstances);
	}

}
