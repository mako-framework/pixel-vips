# Vips for Pixel

[![Static analysis](https://github.com/mako-framework/pixel-vips/actions/workflows/static-analysis.yml/badge.svg)](https://github.com/mako-framework/pixel-vips/actions/workflows/static-analysis.yml)

A [libvips](https://www.libvips.org/) driver for the Mako Pixel image processing library.

The package provides a high-performance libvips backend for common image transformation and processing tasks, while integrating with Pixel's shared API for images, colors, geometry, operations, and inspectors.

## Requirements

- [FFI](https://www.php.net/manual/en/book.ffi.php) enabled
- [libvips](https://www.libvips.org/) installed on the system

This package is built on top of [`jcupitt/vips`](https://packagist.org/packages/jcupitt/vips), which uses FFI to communicate with libvips.

libvips is not bundled with this package and must be installed separately.

> Note that the libvips package name varies between operating systems and distributions. The following are examples and may differ depending on your system and the version of libvips available.

### Debian / Ubuntu

```bash
sudo apt-get install --no-install-recommends libvips42
```

### macOS

```bash
brew install vips
```

See the [libvips installation documentation](https://www.libvips.org/install.html) for further instructions.

## Installation

```bash
composer require mako/pixel-vips
```

## Usage

The Vips driver works just like the GD and ImageMagick drivers and shares the exact same API.

```php
$image = new Vips('image.png');

$image->apply(new Pipeline(
	new Sharpen,
	new Border(new Color(0, 0, 0, 127), width: 10),
));

$image->save();
```

### Access mode

Images are loaded using random access by default, which supports all operations and inspectors. If you're only doing single-pass processing (load, transform, save) then you can switch to sequential access for better performance and lower memory usage.

```php
Vips::setAccessMode(AccessMode::Sequential);
```

> Note that operations and inspectors that read pixels out of order (e.g. pixel inspection or rotation by arbitrary angles) will fail when using sequential access.
