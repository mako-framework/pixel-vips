<?php

/**
 * @copyright Frederic G. Østby
 * @license   http://www.makoframework.com/license
 */

namespace mako\pixel\image\operations\vips;

use InvalidArgumentException;
use Jcupitt\Vips\BlendMode;
use Jcupitt\Vips\Image;
use mako\pixel\image\Color;
use mako\pixel\image\geometry\Point;
use mako\pixel\image\geometry\Points;
use mako\pixel\image\operations\OperationInterface;
use Override;

use function count;
use function implode;
use function sprintf;

/**
 * Draws a polyline on the image.
 */
class Polyline implements OperationInterface
{
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

		$svg = sprintf(<<<'SVG'
			<svg xmlns="http://www.w3.org/2000/svg" width="%d" height="%d">
				<polyline points="%s" fill="none" stroke="%s" stroke-width="%d"/>
			</svg>
			SVG,
			$imageResource->width,
			$imageResource->height,
			implode(' ', $points),
			$this->stroke->toRgbaString(),
			$this->strokeWidth
		);

		$overlay = Image::svgload_buffer($svg);

		if (!$imageResource->hasAlpha()) {
			$imageResource = $imageResource->bandjoin_const(255);
		}

		$imageResource = $imageResource->composite2($overlay, BlendMode::OVER);
	}
}
