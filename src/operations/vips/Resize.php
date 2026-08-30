<?php

/**
 * @copyright Frederic G. Østby
 * @license   http://www.makoframework.com/license
 */

namespace mako\pixel\image\operations\vips;

use Jcupitt\Vips\Image;
use Jcupitt\Vips\Kernel;
use mako\pixel\image\operations\Resize as ResizeOperation;
use mako\pixel\image\operations\traits\ResizeTrait;
use Override;

/**
 * {@inheritDoc}
 */
class Resize extends ResizeOperation
{
	use ResizeTrait;

	/**
	 * {@inheritDoc}
	 *
	 * @param Image &$imageResource
	 */
	#[Override]
	public function apply(object &$imageResource): void
	{
		$oldWidth = $imageResource->width;
		$oldHeight = $imageResource->height;

		[$newWidth, $newHeight] = $this->calculateNewDimensions(
			$this->dimensions->width,
			$this->dimensions->height,
			$oldWidth,
			$oldHeight,
			$this->aspectRatio
		);

		$hScale = $newWidth / $oldWidth;
		$vScale = $newHeight / $oldHeight;

		$imageResource = $imageResource->resize(
			$hScale,
			['vscale' => $vScale, 'kernel' => Kernel::NEAREST]
		);
	}
}
