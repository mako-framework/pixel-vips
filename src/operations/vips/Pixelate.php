<?php

/**
 * @copyright Frederic G. Østby
 * @license   http://www.makoframework.com/license
 */

namespace mako\pixel\image\operations\vips;

use Jcupitt\Vips\Extend;
use Jcupitt\Vips\Image;
use Jcupitt\Vips\Kernel;
use mako\pixel\image\operations\Pixelate as PixelateOperation;
use Override;

use function max;

/**
 * {@inheritDoc}
 */
class Pixelate extends PixelateOperation
{
	/**
	 * {@inheritDoc}
	 *
	 * @param Image &$imageResource
	 */
	#[Override]
	public function apply(object &$imageResource): void
	{
		$width = $imageResource->width;
		$height = $imageResource->height;

		// Downscale then upscale using nearest-neighbor to produce hard-edged blocks

		$imageResource = $imageResource
		->resize(max(1 / $width, 1 / $this->pixelSize))
		->resize($this->pixelSize, ['kernel' => Kernel::NEAREST]);

		// Resize back to the exact original dimensions in case of rounding drift

		if ($imageResource->width !== $width || $imageResource->height !== $height) {
			$imageResource = $imageResource
			->embed(0, 0, $width, $height, ['extend' => Extend::COPY]);
		}
	}
}
