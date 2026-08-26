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
 * Draws a Bézier curve on the image.
 */
class Bezier implements OperationInterface
{
	/**
	 * Number of line segments used to approximate higher-order curves.
	 */
	protected const int SEGMENTS = 100;

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
			throw new InvalidArgumentException('A Bézier curve requires at least 2 points.');
		}

		if ($this->strokeWidth < 1) {
			throw new InvalidArgumentException('Stroke width must be greater than 0.');
		}
	}

	/**
	 * Calculates a point on the Bézier curve at position t using De Casteljau's algorithm.
	 */
	protected function calculatePoint(array $points, float $t): array
	{
		while (($count = count($points)) > 1) {
			$next = [];

			for ($i = 0, $last = $count - 1; $i < $last; ++$i) {
				$next[] = [
					'x' => $points[$i]['x'] + ($points[$i + 1]['x'] - $points[$i]['x']) * $t,
					'y' => $points[$i]['y'] + ($points[$i + 1]['y'] - $points[$i]['y']) * $t,
				];
			}

			$points = $next;
		}

		return $points[0];
	}

	/**
	 * Builds the SVG path data for the curve.
	 */
	protected function buildPath(array $points): string
	{
		$count = count($points);

		$path = sprintf('M %s %s', $points[0]['x'], $points[0]['y']);

		// Lines, quadratic and cubic curves are supported natively by SVG

		if ($count === 2) {
			return $path . sprintf(' L %s %s', $points[1]['x'], $points[1]['y']);
		}

		if ($count === 3) {
			return $path . sprintf(' Q %s %s %s %s', $points[1]['x'], $points[1]['y'], $points[2]['x'], $points[2]['y']);
		}

		if ($count === 4) {
			return $path . sprintf(
				' C %s %s %s %s %s %s',
				$points[1]['x'], $points[1]['y'],
				$points[2]['x'], $points[2]['y'],
				$points[3]['x'], $points[3]['y']
			);
		}

		// Higher-order curves are flattened into line segments

		$segments = [];

		for ($i = 1; $i <= static::SEGMENTS; $i++) {
			$point = $this->calculatePoint($points, $i / static::SEGMENTS);

			$segments[] = sprintf('L %s %s', $point['x'], $point['y']);
		}

		return $path . ' ' . implode(' ', $segments);
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
			$points[] = [
				'x' => $point->x + $this->position->x,
				'y' => $point->y + $this->position->y,
			];
		}

		$svg = sprintf(<<<'SVG'
			<svg xmlns="http://www.w3.org/2000/svg" width="%d" height="%d">
				<path d="%s" fill="none" stroke="%s" stroke-width="%d" stroke-linejoin="round" stroke-linecap="round"/>
			</svg>
			SVG,
			$imageResource->width,
			$imageResource->height,
			$this->buildPath($points),
			$this->stroke->toRgbaString(),
			$this->strokeWidth
		);

		$overlay = Image::svgload_buffer($svg);

		if (!$imageResource->hasAlpha()) {
			$imageResource = $imageResource->bandjoin_const(255);
		}

		$imageResource = $imageResource->composite2($overlay, BlendMode::OVER);

		$alpha = $imageResource->extract_band($imageResource->bands - 1);

		$imageResource = $imageResource
		->extract_band(0, ['n' => $imageResource->bands - 1])
		->bandjoin($alpha->more(0)->ifthenelse(255, 0));
	}
}
