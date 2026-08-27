<?php

/**
 * @copyright Frederic G. Østby
 * @license   http://www.makoframework.com/license
 */

namespace mako\pixel\image\operations\vips;

use InvalidArgumentException;
use Jcupitt\Vips\Image;
use mako\pixel\image\Color;
use mako\pixel\image\geometry\Dimensions;
use mako\pixel\image\geometry\Point;
use mako\pixel\image\operations\OperationInterface;
use mako\pixel\image\operations\vips\traits\SvgTrait;
use Override;

/**
 * Draws an ellipse on the image.
 */
class Ellipse implements OperationInterface
{
	use SvgTrait;

	/**
	 * Constructor.
	 */
	public function __construct(
		protected Dimensions $dimensions,
		protected ?Color $fill = null,
		protected ?Color $stroke = null,
		protected int $strokeWidth = 1,
		protected Point $center = new Point(0, 0)
	) {
		if ($this->fill === null && $this->stroke === null) {
			throw new InvalidArgumentException('An ellipse requires a fill, a stroke, or both.');
		}

		if ($this->stroke !== null && $this->strokeWidth < 1) {
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
