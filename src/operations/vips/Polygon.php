<?php

/**
 * @copyright Frederic G. Østby
 * @license   http://www.makoframework.com/license
 */

namespace mako\pixel\image\operations\vips;

use Jcupitt\Vips\Image;
use mako\pixel\image\operations\Polygon as PolygonOperation;
use mako\pixel\image\operations\vips\traits\SvgTrait;
use Override;

use function implode;

/**
 * {@inheritDoc}
 */
class Polygon extends PolygonOperation
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
			'<polygon points="%s" fill="%s" stroke="%s" stroke-width="%d"/>',
			implode(' ', $points),
			$this->fill?->toRgbaString() ?? 'none',
			$this->stroke?->toRgbaString() ?? 'none',
			$this->stroke !== null ? $this->strokeWidth : 0
		);
	}
}
