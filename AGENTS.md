# Alike Color Finder

This is a PHP 7.4+ command-line application. The `alike` executable is defined in `composer/bin/alike`; tests are PHPUnit suites and CSV fixtures under `test/`.

Run `./vendor/bin/phpunit --do-not-cache-result` for the test suite and `./vendor/bin/parallel-lint src composer/bin/alike test` for syntax checks.

## Code review

Treat Alike Color Finder as a CLI application, not a PHP library. Its compatibility contract is the command line: supported flags and defaults, positional file and directory inputs, stdin behavior, output and error text, and exit statuses.

Classes, interfaces, constructors, methods, return-array shapes, and source-file layout are internal implementation details. Do not request compatibility shims, deprecations, or migration paths for internal refactors unless they change the CLI contract. Review internal changes for correctness, maintainability, and their effect on the CLI instead.
