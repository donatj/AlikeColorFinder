# Alike Color Finder

[![CI](https://github.com/donatj/AlikeColorFinder/actions/workflows/ci.yml/badge.svg)](https://github.com/donatj/AlikeColorFinder/actions/workflows/ci.yml)
[![Latest Stable Version](https://poser.pugx.org/donatj/alike-color-finder/v/stable.png)](https://packagist.org/packages/donatj/alike-color-finder)
[![Total Downloads](https://poser.pugx.org/donatj/alike-color-finder/downloads.png)](https://packagist.org/packages/donatj/alike-color-finder)
[![Latest Unstable Version](https://poser.pugx.org/donatj/alike-color-finder/v/unstable.png)](https://packagist.org/packages/donatj/alike-color-finder)
[![License](https://poser.pugx.org/donatj/alike-color-finder/license.png)](https://packagist.org/packages/donatj/alike-color-finder)

Alike Color Finder is a command-line tool for CI that detects nearly identical colors before they become visual drift. It scans CSS and CSS-like files, directories, or standard input; reports color pairs at or below a chosen difference tolerance; and can fail a build when it finds them.

It understands hex and named colors; `rgb()`, `rgba()`, `hsl()`, `hsla()`, `hwb()`, `lab()`, `lch()`, `oklab()`, `oklch()`, and `color()` functions; and CSS Color Level 4 wide-gamut spaces including Display P3, Rec2020, and ProPhoto RGB.

Choose [CIEDE2000](https://en.wikipedia.org/wiki/Color_difference#CIEDE2000) + alpha (the default), CIE94 + alpha, or an absolute color-difference strategy with `--strategy`.

Prefer a browser? A [web-based interface](https://donatstudios.com/CSS-Alike-Color-Finder) is also available.

## Why

Nearly identical colors tend to accumulate in long-lived stylesheets. This began as a small script for finding them, then grew into a tool that can enforce a color-consistency rule in CI.

It is intended to make accidental color drift visible before it ships.

## Requirements

- PHP 7.4+ with CLI and SPL support

## Installation

Install `alike` globally:

```bash
composer global require donatj/alike-color-finder
```

Or add it to a project as a [vendor binary](https://getcomposer.org/doc/articles/vendor-binaries.md):

```bash
composer require --dev donatj/alike-color-finder
```

## Usage

The examples below use a global installation. For a project dependency, invoke `vendor/bin/alike` instead.

Scan files or directories:

```bash
alike main.css shared.scss styles/
```

Or pipe CSS through standard input:

```bash
css-generating-process | alike
```

The default `ciede2000` strategy reports colors whose difference is at most `4`. Lower `--tolerance` to require closer matches, or choose `cie94` or `actual` with `--strategy`.

```bash
alike --tolerance 1 styles/
```

## Continuous integration

By default, `alike` exits with `2` when it finds a qualifying color pair or encounters an extraction error, which is suitable for most CI systems. Set `--exit-code` to another status, or to `0` to report findings without failing the build.

## Options

Run `alike --help` for the complete CLI reference. The core options are:

| Option | Description |
| --- | --- |
| `--strategy` | Difference strategy: `ciede2000` (default; `perceptual` alias), `cie94`, or `actual`. |
| `--tolerance` | Maximum computed difference considered alike; defaults to `4`. |
| `--exit-code` | Exit status used when findings or extraction errors occur; defaults to `2`. Set to `0` to disable failure. |
| `--pattern` | Regular expression for files discovered when scanning a directory. |

## Example output

Scanning a CSS file:

```bash
alike main.css
                    (4) #e3e3e3                    (4) #e4e4e4   Δ: 0.352
                        #e3e3e3                        #e4e4e4

                    (4) #e3e3e3                    (4) #e5e5e5   Δ: 0.705
                        #e3e3e3                        #e5e5e5

                    (4) #e4e4e4                    (4) #e5e5e5   Δ: 0.352
                        #e4e4e4                        #e5e5e5

                    (2) #454545                  * (3) #444444   Δ: 0.437
                        #454545                        #444444
                                                          #444

Total alike colors: 4 - Average Δ: 2.167 - Total colors: 17 - Distinct colors: 5
```

## Credits

- SupplyHog, Inc - [CIEDE2000 Calculations](https://github.com/supplyhog/phpOptics/blob/e94ac9cf67fb61b89ad23bee01ae32365e587afa/OpticsColorPoint.php#L45-L157)
