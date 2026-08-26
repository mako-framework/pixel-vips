<?php

/**
 * @copyright Frederic G. Østby
 * @license   http://www.makoframework.com/license
 */

namespace mako\pixel\image\operations\vips;

use Jcupitt\Vips\Image;
use mako\pixel\image\geometry\Dimensions;
use mako\pixel\image\operations\AspectRatio;
use mako\pixel\image\operations\OperationInterface;
use mako\pixel\image\operations\traits\ResizeTrait;
use Override;

/**
 * Resizes the image.
 */
class Resize implements OperationInterface
{
	use ResizeTrait;

	/**
	 * Constructor.
	 */
	public function __construct(
		protected Dimensions $dimensions,
		protected AspectRatio $aspectRatio = AspectRatio::Auto
	) {
	}

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
			['vscale' => $vScale, 'kernel' => 'nearest']
		);
	}
}
