<?php

/**
 * @copyright Frederic G. Østby
 * @license   http://www.makoframework.com/license
 */

namespace mako\pixel\image\operations\vips;

use Jcupitt\Vips\Image;
use mako\pixel\image\operations\Ellipse as EllipseOperation;
use mako\pixel\image\operations\vips\traits\SvgTrait;
use Override;

/**
 * {@inheritDoc}
 */
class Ellipse extends EllipseOperation
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
			'<ellipse cx="%d" cy="%d" rx="%s" ry="%s" fill="%s" stroke="%s" stroke-width="%d"/>',
			$this->center->x,
			$this->center->y,
			$this->dimensions->width / 2,
			$this->dimensions->height / 2,
			$this->fill?->toRgbaString() ?? 'none',
			$this->stroke?->toRgbaString() ?? 'none',
			$this->stroke !== null ? $this->strokeWidth : 0
		);
	}
}
