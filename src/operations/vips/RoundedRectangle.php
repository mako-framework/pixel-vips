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

use function floor;
use function min;

/**
 * Draws a rounded rectangle on the image.
 */
class RoundedRectangle implements OperationInterface
{
	use SvgTrait;

	/**
	 * Constructor.
	 */
	public function __construct(
		protected Dimensions $dimensions,
		protected int $radius,
		protected ?Color $fill = null,
		protected ?Color $stroke = null,
		protected int $strokeWidth = 1,
		protected Point $position = new Point(0, 0)
	) {
		if ($this->fill === null && $this->stroke === null) {
			throw new InvalidArgumentException('A rounded rectangle requires a fill, a stroke, or both.');
		}

		if ($this->stroke !== null && $this->strokeWidth < 1) {
			throw new InvalidArgumentException('Stroke width must be greater than 0.');
		}

		// Clamp the radius so that it never exceeds half the width or height

		$this->radius = min(
			$this->radius,
			(int) floor($this->dimensions->width / 2),
			(int) floor($this->dimensions->height / 2),
		);
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
