# Alike Color Finder

[![Latest Stable Version](https://poser.pugx.org/donatj/alike-color-finder/v/stable.png)](https://packagist.org/packages/donatj/alike-color-finder) 
[![Total Downloads](https://poser.pugx.org/donatj/alike-color-finder/downloads.png)](https://packagist.org/packages/donatj/alike-color-finder) 
[![Latest Unstable Version](https://poser.pugx.org/donatj/alike-color-finder/v/unstable.png)](https://packagist.org/packages/donatj/alike-color-finder) 
[![License](https://poser.pugx.org/donatj/alike-color-finder/license.png)](https://packagist.org/packages/donatj/alike-color-finder)

Finds similar (read: alike) colors in CSS-like data within a set likeness threshold.

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
usage: ./composer/bin/alike [<files>]
     --tolerance   [uint] Computed Difference Tolerance - default 5
     --exit-code   [uint] Exit code to raise on alike. 0 for no exit code
          --help   Displays this message
```

Single CSS File:

```bash
$ alike main.css
                    (8) #e4e4e4                 * (71) #e3e3e3   diff: 3
                        #e4e4e4                        #e3e3e3

                   *(8) #e4e4e4                    (5) #e5e5e5   diff: 3
                        #e4e4e4                        #e5e5e5

                   (17) #454545                 * (30) #444444   diff: 3
                        #454545                           #444
                                                       #444444
```
