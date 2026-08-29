<?php

/**
 * @copyright Frederic G. Østby
 * @license   http://www.makoframework.com/license
 */

namespace mako\pixel\image\operations\vips;

use Jcupitt\Vips\Image;
use mako\pixel\image\operations\RoundedRectangle as RoundedRectangleOperation;
use mako\pixel\image\operations\vips\traits\SvgTrait;
use Override;

/**
 * {@inheritDoc}
 */
class RoundedRectangle extends RoundedRectangleOperation
{
	use SvgTrait;

	/**
	 * {@inheritDoc}
	 *
	 * @param Image &$imageResource
	 */
	#[Override]
	public function apply(object &$imageResource): void
	{
		$this->compositeSvg(
			$imageResource,
			'<rect x="%d" y="%d" width="%d" height="%d" rx="%d" fill="%s" stroke="%s" stroke-width="%d"/>',
			$this->position->x,
			$this->position->y,
			$this->dimensions->width,
			$this->dimensions->height,
			$this->radius,
			$this->fill?->toRgbaString() ?? 'none',
			$this->stroke?->toRgbaString() ?? 'none',
			$this->stroke !== null ? $this->strokeWidth : 0
		);
	}
}
