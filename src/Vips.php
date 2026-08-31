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
use mako\pixel\image\operations\OperationInterface;
use mako\pixel\image\vips\AccessMode;
use Override;

use function fwrite;
use function in_array;
use function intdiv;
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
	 * Is the image animated?
	 */
	protected bool $isAnimated = false;

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
	 * Returns the load options for the specified vips loader.
	 *
	 * The "n" option is only supported by loaders that support paged
	 * (potentially animated) images.
	 *
	 * @return array<string, mixed>
	 */
	protected function getLoadOptions(?string $loader): array
	{
		$options = ['access' => static::$access->value];

		if (in_array($loader, [
			'VipsForeignLoadGifFile', 'VipsForeignLoadGifBuffer',
			'VipsForeignLoadNsgifFile', 'VipsForeignLoadNsgifBuffer',
			'VipsForeignLoadWebpFile', 'VipsForeignLoadWebpBuffer',
			'VipsForeignLoadTiffFile', 'VipsForeignLoadTiffBuffer',
			'VipsForeignLoadHeifFile', 'VipsForeignLoadHeifBuffer',
		], true)) {
			$options['n'] = -1;
		}

		return $options;
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
	 * Detects whether the image is animated.
	 */
	protected function detectAnimation(VipsImage $imageResource): void
	{
		$this->isAnimated = $imageResource->getType('page-height') !== 0
			&& $imageResource->get('page-height') < $imageResource->height;

		if ($this->isAnimated && static::$access === AccessMode::Sequential) {
			throw new ImageException('Animated images require the random access mode.');
		}
	}

	/**
	 * {@inheritDoc}
	 */
	#[Override]
	protected function createImageResourceFromPath(string $imagePath): object
	{
		$this->imagePath = $imagePath;

		try {
			$imageResource = VipsImage::newFromFile(
				$imagePath,
				$this->getLoadOptions(VipsImage::findLoad($imagePath))
			);
		}
		catch (VipsException $e) {
			throw new ImageException(sprintf('Unable to process the image [ %s ].', $imagePath), previous: $e);
		}

		$this->detectMimeType($imageResource, $imagePath, false);
		$this->detectAnimation($imageResource);

		return $imageResource;
	}

	/**
	 * {@inheritDoc}
	 */
	#[Override]
	protected function createImageResourceFromBlob(string $blob): object
	{
		try {
			$imageResource = VipsImage::newFromBuffer(
				$blob,
				options: $this->getLoadOptions(VipsImage::findLoadBuffer($blob))
			);
		}
		catch (VipsException $e) {
			throw new ImageException('Unable to process the image.', previous: $e);
		}

		$this->detectMimeType($imageResource, $blob, true);
		$this->detectAnimation($imageResource);

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
	 * Returns true if the specified vips file suffix supports animation and false if not.
	 */
	protected function suffixSupportsAnimation(string $suffix): bool
	{
		return $suffix === '.gif' || $suffix === '.webp';
	}

	/**
	 * Returns the image resource prepared for saving with the specified suffix.
	 *
	 * Animated images are reduced to their first frame when saving
	 * to a format that doesn't support animation.
	 */
	protected function getSaveableImageResource(string $suffix): VipsImage
	{
		if ($this->isAnimated && !$this->suffixSupportsAnimation($suffix)) {
			return $this->imageResource->crop(
				0,
				0,
				$this->imageResource->width,
				$this->imageResource->get('page-height')
			);
		}

		return $this->imageResource;
	}

	/**
	 * {@inheritDoc}
	 */
	#[Override]
	protected function getImageResourceAsBlob(?string $type, int $quality): string
	{
		[$suffix, $options] = $this->getSuffixAndSaveOptions($type ?? $this->mimeType, $quality);

		return $this->getSaveableImageResource($suffix)->writeToBuffer($suffix, $options);
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
		[$suffix, $options] = $this->getSuffixAndSaveOptions(pathinfo($imagePath, PATHINFO_EXTENSION), $quality);

		$this->getSaveableImageResource($suffix)->writeToFile($imagePath, $options);
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
		if ($this->isAnimated) {
			return $this->imageResource->get('page-height');
		}

		return $this->imageResource->height;
	}

	/**
	 * {@inheritDoc}
	 */
	#[Override]
	public function apply(OperationInterface $operation): static
	{
		if (!$this->isAnimated) {
			return parent::apply($operation);
		}

		$pageHeight = $this->imageResource->get('page-height');
		$frameCount = intdiv($this->imageResource->height, $pageHeight);
		$width = $this->imageResource->width;

		$frames = [];

		for ($i = 0; $i < $frameCount; $i++) {
			$frame = $this->imageResource->crop(0, $i * $pageHeight, $width, $pageHeight);

			$operation->apply($frame);

			$frames[] = $frame;
		}

		$this->imageResource = VipsImage::arrayjoin($frames, ['across' => 1])->copy();

		$this->imageResource->set('page-height', $frames[0]->height);

		return $this;
	}
}
