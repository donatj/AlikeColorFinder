<?php

namespace donatj\AlikeColorFinder;

use donatj\AlikeColorFinder\ColorDiffStrategy\Absolute;
use donatj\AlikeColorFinder\ColorDiffStrategy\Cie94WithAlpha;
use donatj\AlikeColorFinder\ColorDiffStrategy\CieDe2000WithAlpha;
use PHPUnit\Framework\TestCase;

class ColorDiffStrategyTest extends TestCase {

	/**
	 * @dataProvider colorDiffProvider
	 */
	public function testColorDiffs( $color1, $color2, $absoluteDiff, $cie94Diff, $cie2000Diff ) {
		$colorEntry1 = $this->extractSingleColor($color1);
		$colorEntry2 = $this->extractSingleColor($color2);

		$this->assertEqualsWithDelta($absoluteDiff, (new Absolute())($colorEntry1, $colorEntry2), 0.000001, 'Absolute diff mismatch');
		$this->assertEqualsWithDelta($cie94Diff, (new Cie94WithAlpha())($colorEntry1, $colorEntry2), 0.000001, 'CIE94 diff mismatch');
		$this->assertEqualsWithDelta($cie2000Diff, (new CieDe2000WithAlpha())($colorEntry1, $colorEntry2), 0.000001, 'CIE2000 diff mismatch');
	}

	private function extractSingleColor( $color ) {
		$colors = (new CssColorExtractor("a { color: {$color}; }"))->extractColors($errors);

		foreach( $errors as $error ) {
			throw $error['exception'];
		}

		$this->assertCount(1, $colors, 'Color count mismatch');

		return reset($colors);
	}

	public function colorDiffProvider() {
		$rows = [];
		$file = fopen(__DIR__ . '/color-diffs.csv', 'r');

		fgetcsv($file, 0); // skip header line

		for( ; ; ) {
			$data = fgetcsv($file, 0);
			if( $data === false ) {
				break;
			}

			// skip blank lines
			if( count($data) === 1 && trim((string)$data[0]) === '' ) {
				continue;
			}

			if( count($data) < 5 ) {
				throw new \UnexpectedValueException('Invalid color-diffs.csv row: expected 5 columns, got ' . count($data));
			}

			$rows[$data[0] . ' -> ' . $data[1]] = [ $data[0], $data[1], (float)$data[2], (float)$data[3], (float)$data[4] ];
		}

		fclose($file);

		return $rows;
	}
}
