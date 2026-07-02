<?php

namespace donatj\AlikeColorFinder;

interface ColorEntry {

	/**
	 * @return float  sRGB red 0–255
	 */
	public function getR(): float;

	/**
	 * @return float  sRGB green 0–255
	 */
	public function getG(): float;

	/**
	 * @return float  sRGB blue 0–255
	 */
	public function getB(): float;

	/**
	 * @return float  alpha 0–1
	 */
	public function getA(): float;

	public function addInstance( string $instance ): void;

	public function getInstanceTotal(): int;

	/**
	 * @return string[]
	 */
	public function getDistinctInstances(): array;

	public function getRgbaString(): string;

	public function getRgbHexString(): string;

	public function getSimplestCssString( float $epsilon = 0.001 ): string;

	/**
	 * Returns the native CSS string representation for this color space.
	 * For example: "lab(50 0 0)" for Lab, "oklch(0.5 0.1 180)" for OKLch
	 */
	public function getNativeCssString(): string;

	/**
	 * @return array
	 */
	public function getRgbaArray(): array;

	/**
	 * @return array
	 */
	public function getXyzaArray(): array;

	/**
	 * @return array
	 */
	public function getLabAlphaCieArray(): array;

}
