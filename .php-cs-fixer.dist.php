<?php

use Corpus\PhpCsFixer\Config;
use PhpCsFixer\Finder;

$finder = Finder::create()
	->files()
	->in(__DIR__ . '/src')
	->in(__DIR__ . '/test')
	->name('*.php')
	->append([__DIR__ . '/composer/bin/alike']);

return Config::php7x4()->setFinder($finder);
