<?php

/**
 * @copyright Frederic G. Østby
 * @license   http://www.makoframework.com/license
 */

namespace mako\pixel\image\operations\vips;

use Jcupitt\Vips\Image;
use mako\pixel\image\operations\Crop as CropOperation;
use Override;

use function min;

/**
 * {@inheritDoc}
 */
class Crop extends CropOperation
{
	/**
	 * {@inheritDoc}
	 *
	 * @param Image &$imageResource
	 */
	#[Override]
	public function apply(object &$imageResource): void
	{
		$imageResource = $imageResource->crop(
			$this->position->x,
			$this->position->y,
			min($this->dimensions->width, $imageResource->width - $this->position->x),
			min($this->dimensions->height, $imageResource->height - $this->position->y)
		);
	}
}
