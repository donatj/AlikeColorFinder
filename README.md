# Alike Color Finder

[![Latest Stable Version](https://poser.pugx.org/donatj/alike-color-finder/v/stable.png)](https://packagist.org/packages/donatj/alike-color-finder) 
[![Total Downloads](https://poser.pugx.org/donatj/alike-color-finder/downloads.png)](https://packagist.org/packages/donatj/alike-color-finder) 
[![Latest Unstable Version](https://poser.pugx.org/donatj/alike-color-finder/v/unstable.png)](https://packagist.org/packages/donatj/alike-color-finder) 
[![License](https://poser.pugx.org/donatj/alike-color-finder/license.png)](https://packagist.org/packages/donatj/alike-color-finder)
[![Scrutinizer Code Quality](https://scrutinizer-ci.com/g/donatj/AlikeColorFinder/badges/quality-score.png?b=master)](https://scrutinizer-ci.com/g/donatj/AlikeColorFinder/?branch=master)

Finds similar (e.g. alike) colors in CSS and CSS-like data, within a set likeness threshold. It compares `#hex`, `rgb()`, `rgba()`, `hsl()`, and `hsla()` colors.

Includes the [CIEDE2000](http://en.wikipedia.org/wiki/Color_difference#CIEDE2000)+Alpha (default), [CIE94](http://en.wikipedia.org/wiki/Color_difference#CIE94)+Alpha, as well as "*actual*" mathematical absolute color diff strategies, switchable with a flag.

A web based interface to this exists [here](https://donatstudios.com/CSS-Alike-Color-Finder).

## Why

***Very similar*** but *not* identical colors seem to pop up really often in CSS files of any reasonable age, and I became **sick** of them. This started as a little script to help me find them in a stylesheet, and grew into this full-fledged tool.

## Requirements

- PHP 5.4.0+ with CLI and SPL

## Installation

Using composer, `alike` can be installed globally via:

```bash
$ composer global require 'donatj/alike-color-finder=~0.1'
```

Or if you are using composer for the project you wish to test, you can simply add it as a [vendor binary](https://getcomposer.org/doc/articles/vendor-binaries.md):

```bash
{
  "require-dev": {
      "donatj/alike-color-finder": "~0.1"
  }
}
```

## Usage

Pass CSS-like files to scan as arguments.

```bash
$ alike main.css shared.scss
```

Or pipe CSS into stdin.

```bash
$ alike < main.css
$ css-generating-process | alike
```

## Continuous Integration

By default on finding any alike colors it will exit with an exit code of `2`. This is enough to flag as a failure with most CI tools. This value is also configurable with the `--exit-code` option or can be set to `0` to be disabled.

## Example Output

Help:

```bash
$ alike --help
usage: alike [<files>]
      --strategy   [string] Color diff strategy.
            Options:
                actual
                cie94
                ciede2000 [default - aka. perceptual]
     --tolerance   [float] Computed Difference Tolerance - default 5
     --exit-code   [uint] Exit code to raise on alike. 0 for no exit code
          --help   Displays this message
```

Single CSS File:

```bash
$ alike main.css
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