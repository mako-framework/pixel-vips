<?php

/**
 * @copyright Frederic G. Østby
 * @license   http://www.makoframework.com/license
 */

namespace mako\pixel\image\operations\vips;

use Jcupitt\Vips\Image;
use mako\pixel\image\geometry\Dimensions;
use mako\pixel\image\operations\Watermark as WatermarkOperation;
use mako\pixel\image\Vips;
use Override;

/**
 * {@inheritDoc}
 *
 * @extends WatermarkOperation<Vips>
 */
class Watermark extends WatermarkOperation
{
	/**
	 * {@inheritDoc}
	 *
	 * @param Image &$imageResource
	 */
	#[Override]
	public function apply(object &$imageResource): void
	{
		if ($this->opacity < 100) {
			$this->image->apply(new Opacity($this->opacity));
		}

		$point = $this->position->resolvePosition(
			new Dimensions($imageResource->width, $imageResource->height),
			$this->image->getDimensions(),
			$this->margin
		);

		new Composite($this->image, $point)->apply($imageResource);
	}
}
