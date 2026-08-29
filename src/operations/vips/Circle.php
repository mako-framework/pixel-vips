<?php

/**
 * @copyright Frederic G. Østby
 * @license   http://www.makoframework.com/license
 */

namespace mako\pixel\image\operations\vips;

use Jcupitt\Vips\Image;
use mako\pixel\image\operations\Circle as CircleOperation;
use mako\pixel\image\operations\vips\traits\SvgTrait;
use Override;

/**
 * {@inheritDoc}
 */
class Circle extends CircleOperation
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
			'<circle cx="%d" cy="%d" r="%d" fill="%s" stroke="%s" stroke-width="%d"/>',
			$this->center->x,
			$this->center->y,
			$this->radius,
			$this->fill?->toRgbaString() ?? 'none',
			$this->stroke?->toRgbaString() ?? 'none',
			$this->stroke !== null ? $this->strokeWidth : 0
		);
	}
}
