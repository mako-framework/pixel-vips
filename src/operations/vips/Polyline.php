<?php

/**
 * @copyright Frederic G. Østby
 * @license   http://www.makoframework.com/license
 */

namespace mako\pixel\image\operations\vips;

use InvalidArgumentException;
use Jcupitt\Vips\Image;
use mako\pixel\image\Color;
use mako\pixel\image\geometry\Point;
use mako\pixel\image\geometry\Points;
use mako\pixel\image\operations\OperationInterface;
use mako\pixel\image\operations\vips\traits\SvgTrait;
use Override;

use function count;
use function implode;

/**
 * Draws a polyline on the image.
 */
class Polyline implements OperationInterface
{
	use SvgTrait;

	/**
	 * Constructor.
	 */
	public function __construct(
		protected Points $points,
		protected Color $stroke,
		protected int $strokeWidth = 1,
		protected Point $position = new Point(0, 0)
	) {
		if (count($points) < 2) {
			throw new InvalidArgumentException('A polyline requires at least 2 points.');
		}

		if ($this->strokeWidth < 1) {
			throw new InvalidArgumentException('Stroke width must be greater than 0.');
		}
	}

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
