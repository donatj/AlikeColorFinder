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

	public function colorProvider() {
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
