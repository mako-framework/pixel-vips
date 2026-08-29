<?php

/**
 * @copyright Frederic G. Østby
 * @license   http://www.makoframework.com/license
 */

namespace mako\pixel\image\operations\vips;

use Jcupitt\Vips\Image;
use mako\pixel\image\operations\Polyline as PolylineOperation;
use mako\pixel\image\operations\vips\traits\SvgTrait;
use Override;

use function implode;

/**
 * {@inheritDoc}
 */
class Polyline extends PolylineOperation
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
		$points = [];

		foreach ($this->points as $point) {
			$points[] = ($point->x + $this->position->x) . ',' . ($point->y + $this->position->y);
		}

		$this->compositeSvg(
			$imageResource,
			'<polyline points="%s" fill="none" stroke="%s" stroke-width="%d"/>',
			implode(' ', $points),
			$this->stroke->toRgbaString(),
			$this->strokeWidth
		);
	}
}
