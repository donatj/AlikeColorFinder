# Alike Color Finder

Finds similar (Alike) colors in CSS-like data within a set likeness threshold.

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