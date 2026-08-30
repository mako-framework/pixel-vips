<?php

/**
 * @copyright Frederic G. Østby
 * @license   http://www.makoframework.com/license
 */

namespace mako\pixel\image;

use finfo;
use Jcupitt\Vips\BandFormat;
use Jcupitt\Vips\Exception as VipsException;
use Jcupitt\Vips\Image as VipsImage;
use Jcupitt\Vips\Interpretation;
use mako\pixel\image\exceptions\ImageException;
use mako\pixel\image\geometry\Dimensions;
use mako\pixel\image\vips\AccessMode;
use Override;

use function fwrite;
use function pathinfo;
use function round;
use function sprintf;
use function stream_get_contents;
use function strstr;
use function strtolower;

/**
 * Vips.
 *
 * @property ?VipsImage $imageResource
 */
class Vips extends Image
{
	/**
	 * Access mode.
	 */
	protected static AccessMode $access = AccessMode::Random;

	/**
	 * {@inheritDoc}
	 */
	#[Override]
	final public function __clone()
	{
		$this->imageResource = $this->imageResource->copy();
	}

	/**
	 * {@inheritDoc}
	 */
	#[Override]
	protected function createImageResource(Dimensions $dimensions, Color $fill): object
	{
		$this->mimeType = 'image/png';

		return VipsImage::black(
			$dimensions->width,
			$dimensions->height
		)
		->newFromImage([
			$fill->red,
			$fill->green,
			$fill->blue,
			$fill->alpha,
		])
		->cast(BandFormat::UCHAR)
		->copy(['interpretation' => Interpretation::SRGB]);
	}

	/**
	 * Detects the mime type of the image.
	 *
	 * Uses the vips loader where possible and falls back to finfo for
	 * ambiguous loaders such as magickload, heifload and openslideload.
	 */
	protected function detectMimeType(VipsImage $imageResource, string $source, bool $isBlob): void
	{
		$type = match (strstr($imageResource->get('vips-loader'), 'load', true)) {
			'gif'   => 'gif',
			'jp2k'  => 'jp2',
			'jpeg'  => 'jpeg',
			'jxl'   => 'jxl',
			'png'   => 'png',
			'tiff'  => 'tiff',
			'webp'  => 'webp',
			default => null,
		};

		if ($type !== null) {
			$this->mimeType = $this->normalizeMimeType($type);

			return;
		}

		$finfo = new finfo(FILEINFO_MIME_TYPE);

		$this->mimeType = $isBlob ? $finfo->buffer($source) : $finfo->file($source);
	}

	/**
	 * {@inheritDoc}
	 */
	#[Override]
	protected function createImageResourceFromPath(string $imagePath): object
	{
		$this->imagePath = $imagePath;

		try {
			$imageResource = VipsImage::newFromFile($imagePath, ['access' => static::$access->value]);
		}
		catch (VipsException $e) {
			throw new ImageException(sprintf('Unable to process the image [ %s ].', $imagePath), previous: $e);
		}

		$this->detectMimeType($imageResource, $imagePath, false);

		return $imageResource;
	}

	/**
	 * {@inheritDoc}
	 */
	#[Override]
	protected function createImageResourceFromBlob(string $blob): object
	{
		try {
			$imageResource = VipsImage::newFromBuffer($blob, options: ['access' => static::$access->value]);
		}
		catch (VipsException $e) {
			throw new ImageException('Unable to process the image.', previous: $e);
		}

		$this->detectMimeType($imageResource, $blob, true);

		return $imageResource;
	}

	/**
	 * {@inheritDoc}
	 */
	#[Override]
	protected function createImageResourceFromStream(mixed $stream): object
	{
		return $this->createImageResourceFromBlob(stream_get_contents($stream));
	}

	/**
	 * {@inheritDoc}
	 */
	#[Override]
	protected function destroyImageResource(): void
	{
		$this->imageResource = null;
	}

	/**
	 * Returns the vips file suffix and save options for the specified image type or extension.
	 *
	 * @return array{string, array<string, mixed>}
	 */
	protected function getSuffixAndSaveOptions(string $type, int $quality): array
	{
		return match (strtolower($type)) {
			'avif', 'image/avif'                       => ['.avif', ['Q' => $quality]],
			'bmp', 'image/bmp', 'image/x-ms-bmp'       => ['.bmp', []],
			'gif', 'image/gif'                         => ['.gif', []],
			'heic', 'heif', 'image/heic', 'image/heif' => ['.heic', ['Q' => $quality]],
			'jp2', 'image/jp2'                         => ['.jp2', ['Q' => $quality]],
			'jpg', 'jpeg', 'image/jpg', 'image/jpeg'   => ['.jpg', ['Q' => $quality]],
			'jxl', 'image/jxl'                         => ['.jxl', ['Q' => $quality]],
			'png', 'image/png'                         => ['.png', ['compression' => (int) (9 - (round(($quality / 100) * 9)))]],
			'tif', 'tiff', 'image/tiff'                => ['.tif', ['Q' => $quality]],
			'webp', 'image/webp'                       => ['.webp', ['Q' => $quality]],
			default                                    => throw new ImageException(sprintf('Unsupported image type [ %s ].', $type)),
		};
	}

	/**
	 * {@inheritDoc}
	 */
	#[Override]
	protected function getImageResourceAsBlob(?string $type, int $quality): string
	{
		[$suffix, $options] = $this->getSuffixAndSaveOptions($type ?? $this->mimeType, $quality);

		return $this->imageResource->writeToBuffer($suffix, $options);
	}

	/**
	 * {@inheritDoc}
	 */
	#[Override]
	protected function writeImageResourceToStream(mixed $stream, ?string $type = null, int $quality = 95): void
	{
		fwrite($stream, $this->getImageResourceAsBlob($type, $quality));
	}

	/**
	 * {@inheritDoc}
	 */
	#[Override]
	protected function saveImageResource(string $imagePath, int $quality): void
	{
		[, $options] = $this->getSuffixAndSaveOptions(pathinfo($imagePath, PATHINFO_EXTENSION), $quality);

		$this->imageResource->writeToFile($imagePath, $options);
	}

	/**
	 * Sets the libvips access mode.
	 *
	 * Random access (the default) supports all operations and inspectors.
	 * Sequential access is faster and uses less memory but only supports
	 * single-pass pipelines; operations that read pixels out of order
	 * (e.g. pixel inspection or arbitrary rotation) will fail.
	 */
	public static function setAccessMode(AccessMode $access): void
	{
		static::$access = $access;
	}

	/**
	 * {@inheritDoc}
	 */
	#[Override]
	public function getWidth(): int
	{
		return $this->imageResource->width;
	}

	/**
	 * {@inheritDoc}
	 */
	#[Override]
	public function getHeight(): int
	{
		return $this->imageResource->height;
	}
}
