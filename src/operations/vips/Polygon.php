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
 * Draws a polygon on the image.
 */
class Polygon implements OperationInterface
{
	/**
	 * Constructor.
	 */
	public function __construct(
		protected Points $points,
		protected ?Color $fill = null,
		protected ?Color $stroke = null,
		protected int $strokeWidth = 1,
		protected Point $position = new Point(0, 0)
	) {
		if (count($points) < 3) {
			throw new InvalidArgumentException('A polygon requires at least 3 points.');
		}

		if ($this->fill === null && $this->stroke === null) {
			throw new InvalidArgumentException('A polygon requires a fill, a stroke, or both.');
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
		$points = [];

		foreach ($this->points as $point) {
			$points[] = ($point->x + $this->position->x) . ',' . ($point->y + $this->position->y);
		}

		$svg = sprintf(<<<'SVG'
			<svg xmlns="http://www.w3.org/2000/svg" width="%d" height="%d">
				<polygon points="%s" fill="%s" stroke="%s" stroke-width="%d"/>
			</svg>
			SVG,
			$imageResource->width,
			$imageResource->height,
			implode(' ', $points),
			$this->fill?->toRgbaString() ?? 'none',
			$this->stroke?->toRgbaString() ?? 'none',
			$this->stroke !== null ? $this->strokeWidth : 0
		);

		$overlay = Image::svgload_buffer($svg);

		if (!$imageResource->hasAlpha()) {
			$imageResource = $imageResource->bandjoin_const(255);
		}

		$imageResource = $imageResource->composite2($overlay, BlendMode::OVER);
	}
}
