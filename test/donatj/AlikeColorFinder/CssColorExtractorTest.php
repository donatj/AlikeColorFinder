<?php

namespace donatj\AlikeColorFinder;

use PHPUnit\Framework\TestCase;

class CssColorExtractorTest extends TestCase {

	/**
	 * @dataProvider colorProvider
	 */
	public function testExtract( $actual, $expected ) {
		$colors = (new CssColorExtractor("a { color: {$actual}; }"))->extractColors($errors);
		$this->assertCount(1, $colors);
		$this->assertSame($expected, reset($colors)->getSimplestCssString());
	}

	public function colorProvider() {
		$colors = [];
		$file   = fopen(__DIR__ . '/colors.csv', 'r');

		fgetcsv($file, 0); // skip header line

		for( ; ; ) {
			$data = fgetcsv($file, 0);
			if( $data === false ) {
				break;
			}

			$colors[] = [ $data[0], $data[1] ];
		}

		fclose($file);
		return $colors;
	}

}