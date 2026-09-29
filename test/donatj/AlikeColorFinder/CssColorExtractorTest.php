<?php

namespace donatj\AlikeColorFinder;

use PHPUnit\Framework\TestCase;

class CssColorExtractorTest extends TestCase {

	/**
	 * @dataProvider colorProvider
	 */
	public function testExtract( $actual, $expected ) {
		$colors = (new CssColorExtractor("a { color: {$actual}; }"))->extractColors($errors);

		foreach( $errors as $error ) {
			throw $error['exception'];
		}

		$this->assertCount(1, $colors, 'Color count mismatch');
		$this->assertSame($expected, reset($colors)->getSimplestCssString());
	}

	public function testExtendedSrgbValuesAreNotCollapsed() {
		$colors = (new CssColorExtractor('a { color: #f00; background: color(srgb 1.1 0 0); }'))->extractColors($errors);

		foreach( $errors as $error ) {
			throw $error['exception'];
		}

		$this->assertCount(2, $colors);
	}

	public function testInvalidFunctionSyntaxIsIgnored() {
		foreach( [
			'lab(500)',
			'rgb(1, 2 3)',
			'rgb(1 2 3 4)',
			'rgb(1, 2, 3 / 0.5)',
			'hsl(50% 100% 50%)',
			'hwb(50% 0% 0%)',
			'lch(50 20 50%)',
			'oklch(.5 .2 50%)',
			'color(srgb 1, 2, 3)',
			'color(srgb 100)',
			'color(srgb 1 2)',
			'color(srgb 1 2 3 4)',
			'color(srgb 1 2 / 0.5)',
		] as $css ) {
			$colors = (new CssColorExtractor("a { color: {$css}; }"))->extractColors($errors);

			$this->assertCount(0, $colors, $css);
			$this->assertCount(0, $errors, $css);
		}
	}

	public function testHueAnglesNormalizeToDegrees() {
		foreach( [
			'hsl(180deg 100% 50%)' => 'hsl(180 100% 50%)',
			'hwb(.5turn 0% 0%)' => 'hwb(180 0% 0%)',
			'lch(50 20 200grad)' => 'lch(50 20 180)',
			'oklch(.5 .05 3.141592653589793rad)' => 'oklch(.5 .05 180)',
		] as $angle => $degrees ) {
			$this->assertSame(
				$this->extractSingleColor($degrees)->getSimplestCssString(),
				$this->extractSingleColor($angle)->getSimplestCssString(),
				$angle
			);
		}
	}

	public function testFunctionsAreNotExtractedFromIdentifiers() {
		$colors = (new CssColorExtractor('collab(50 0 0) mycolor(srgb 1 0 0)'))->extractColors($errors);

		$this->assertCount(0, $colors);
		$this->assertCount(0, $errors);
	}

	public function testModernSyntaxSupportsCssNumberGrammar() {
		foreach( [
			'rgb(+1e2 0 0)' => '#640000',
			'rgba(0 0 0 / +.5)' => 'rgba(0,0,0,0.5)',
			'rgba(0 0 0)' => '#000',
			'hsla(0 0% 0%)' => '#000',
			'lab(+50 0 0)' => '#777',
			'color(srgb 1e0 0 0)' => '#f00',
		] as $css => $expected ) {
			$this->assertSame($expected, $this->extractSingleColor($css)->getSimplestCssString(), $css);
		}
	}

	public function testExtendedTransferFunctionsPreserveSign() {
		foreach( [ 'display-p3', 'prophoto-rgb', 'rec2020' ] as $colorSpace ) {
			$positive = $this->extractSingleColor("color({$colorSpace} 0.5 0 0)");
			$negative = $this->extractSingleColor("color({$colorSpace} -0.5 0 0)");
			$positiveXyz = $positive->getXyzaArray();
			$negativeXyz = $negative->getXyzaArray();

			$this->assertEqualsWithDelta(-$positiveXyz['x'], $negativeXyz['x'], 0.000000001);
			$this->assertEqualsWithDelta(-$positiveXyz['y'], $negativeXyz['y'], 0.000000001);
			$this->assertEqualsWithDelta(-$positiveXyz['z'], $negativeXyz['z'], 0.000000001);
		}
	}

	private function extractSingleColor( $css ) {
		$colors = (new CssColorExtractor("a { color: {$css}; }"))->extractColors($errors);

		foreach( $errors as $error ) {
			throw $error['exception'];
		}

		$this->assertCount(1, $colors);

		return reset($colors);
	}

	public static function colorProvider() {
		$colors = [];
		$file   = fopen(__DIR__ . '/colors.csv', 'r');

		fgetcsv($file, 0, ',', '"', '\\'); // skip header line

		for( ; ; ) {
			$data = fgetcsv($file, 0, ',', '"', '\\');
			if( $data === false ) {
				break;
			}

			$colors[$data[0]] = [ $data[0], $data[1] ];
		}

		fclose($file);
		return $colors;
	}

}
