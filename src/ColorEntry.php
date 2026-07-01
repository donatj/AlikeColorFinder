<?php

namespace donatj\AlikeColorFinder;

interface ColorEntry {

	/**
	 * @return float  sRGB red 0–255
	 */
	public function getR();

	/**
	 * @return float  sRGB green 0–255
	 */
	public function getG();

	/**
	 * @return float  sRGB blue 0–255
	 */
	public function getB();

	/**
	 * @return float  alpha 0–1
	 */
	public function getA();

	public function addInstance( $instance );

	public function getInstanceTotal();

	/**
	 * @return string[]
	 */
	public function getDistinctInstances();

	public function getRgbaString();

	public function getRgbHexString();

	public function getSimplestCssString();

	/**
	 * @return array
	 */
	public function getRgbaArray();

	/**
	 * @return array
	 */
	public function getXyzaArray();

	/**
	 * @return array
	 */
	public function getLabAlphaCieArray();

}
