<?php

/**
 * @copyright Frederic G. Østby
 * @license   http://www.makoframework.com/license
 */

namespace mako\pixel\image;

use Jcupitt\Vips\Exception as VipsException;
use Jcupitt\Vips\Image as VipsImage;
use Jcupitt\Vips\Interpretation;
use mako\pixel\image\exceptions\ImageException;
use mako\pixel\image\geometry\Dimensions;
use Override;

use function fwrite;
use function pathinfo;
use function round;
use function sprintf;
use function str_contains;
use function stream_get_contents;
use function strtolower;

/**
 * Vips.
 *
 * @property ?VipsImage $imageResource
 */
class Vips extends Image
{
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
		->cast('uchar')
		->copy(['interpretation' => Interpretation::SRGB]);
	}

	/**
	 * Returns the mime type of the image based on the vips loader that was used to load it.
	 */
	protected function getMimeTypeFromLoader(string $loader): string
	{
		switch (true) {
			case str_contains($loader, 'jpeg'):
				$mimeType = 'image/jpeg';
				break;
			case str_contains($loader, 'png'):
				$mimeType = 'image/png';
				break;
			case str_contains($loader, 'gif'):
				$mimeType = 'image/gif';
				break;
			case str_contains($loader, 'webp'):
				$mimeType = 'image/webp';
				break;
			case str_contains($loader, 'avif'):
				$mimeType = 'image/avif';
				break;
			case str_contains($loader, 'bmp'):
				$mimeType = 'image/bmp';
				break;
			default:
				$mimeType = 'application/octet-stream';
		}

		return $this->normalizeMimeType($mimeType);
	}

	/**
	 * {@inheritDoc}
	 */
	#[Override]
	protected function createImageResourceFromPath(string $imagePath): object
	{
		$this->imagePath = $imagePath;

		try {
			$imageResource = VipsImage::newFromFile($imagePath, ['access' => 'sequential']);
		}
		catch (VipsException $e) {
			throw new ImageException(sprintf('Unable to process the image [ %s ].', $imagePath), previous: $e);
		}

		$this->mimeType = $this->getMimeTypeFromLoader($imageResource->get('vips-loader'));

		return $imageResource;
	}

	/**
	 * {@inheritDoc}
	 */
	#[Override]
	protected function createImageResourceFromBlob(string $blob): object
	{
		try {
			$imageResource = VipsImage::newFromBuffer($blob);
		}
		catch (VipsException $e) {
			throw new ImageException('Unable to process the image.', previous: $e);
		}

		$this->mimeType = $this->getMimeTypeFromLoader($imageResource->get('vips-loader'));

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
	 * {@inheritDoc}
	 */
	#[Override]
	protected function getImageResourceAsBlob(?string $type, int $quality): string
	{
		$type ??= $this->mimeType;

		switch (strtolower($type)) {
			case 'gif':
			case 'image/gif':
				return $this->imageResource->writeToBuffer('.gif');
			case 'jpg':
			case 'jpeg':
			case 'image/jpg':
			case 'image/jpeg':
				return $this->imageResource->writeToBuffer('.jpg', ['Q' => $quality]);
			case 'png':
			case 'image/png':
				return $this->imageResource->writeToBuffer('.png', ['compression' => (int) (9 - (round(($quality / 100) * 9)))]);
			case 'webp':
			case 'image/webp':
				return $this->imageResource->writeToBuffer('.webp', ['Q' => $quality]);
			case 'avif':
			case 'image/avif':
				return $this->imageResource->writeToBuffer('.avif', ['Q' => $quality]);
			case 'bmp':
			case 'image/bmp':
				return $this->imageResource->writeToBuffer('.bmp');
			default:
				throw new ImageException(sprintf('Unsupported image type [ %s ].', $type));
		}
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
		$extension = pathinfo($imagePath, PATHINFO_EXTENSION);

		switch (strtolower($extension)) {
			case 'gif':
				$this->imageResource->writeToFile($imagePath);
				break;
			case 'jpg':
			case 'jpeg':
				$this->imageResource->writeToFile($imagePath, ['Q' => $quality]);
				break;
			case 'png':
				$this->imageResource->writeToFile($imagePath, ['compression' => (int) (9 - (round(($quality / 100) * 9)))]);
				break;
			case 'webp':
				$this->imageResource->writeToFile($imagePath, ['Q' => $quality]);
				break;
			case 'avif':
				$this->imageResource->writeToFile($imagePath, ['Q' => $quality]);
				break;
			case 'bmp':
				$this->imageResource->writeToFile($imagePath);
				break;
			default:
				throw new ImageException(sprintf('Unable to save as [ %s ]. Unsupported image format.', $extension));
		}
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
