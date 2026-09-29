# Alike Color Finder

Alike Color Finder is a PHP 7.4+ command-line application that scans CSS and CSS-like input for colors that are alike within a chosen tolerance. It accepts files, directories, or stdin; reports matching color pairs and their difference; and can fail CI when it finds them.

The project exists to help teams find accidental color drift in real stylesheets. Its goals are correct, predictable CSS color parsing and comparison, useful command-line output, and dependable automation behavior across supported PHP versions. The `alike` executable is defined in `composer/bin/alike`; tests are PHPUnit suites and CSV fixtures under `test/`.

Run `./vendor/bin/phpunit --do-not-cache-result` for the test suite and `./vendor/bin/parallel-lint src composer/bin/alike test` for syntax checks.

## Code review

Treat Alike Color Finder as a CLI application, not a PHP library. Its compatibility contract is the command line: supported flags and defaults, positional file and directory inputs, stdin behavior, output and error text, and exit statuses.

Classes, interfaces, constructors, methods, return-array shapes, and source-file layout are internal implementation details. Do not request compatibility shims, deprecations, or migration paths for internal refactors unless they change the CLI contract. Review internal changes for correctness, maintainability, and their effect on the CLI instead.
