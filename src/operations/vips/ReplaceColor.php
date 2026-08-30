<?php

/**
 * @copyright Frederic G. Østby
 * @license   http://www.makoframework.com/license
 */

namespace mako\pixel\image\operations\vips;

use Jcupitt\Vips\BandFormat;
use Jcupitt\Vips\Image;
use Jcupitt\Vips\Interpretation;
use mako\pixel\image\operations\ReplaceColor as ReplaceColorOperation;
use Override;

use function array_shift;
use function max;

/**
 * {@inheritDoc}
 */
class ReplaceColor extends ReplaceColorOperation
{
	/**
	 * Converts tolerance percentage (0-100) to 8-bit channel range (0-255).
	 */
	protected function normalizeTolerance(): float
	{
		return ($this->tolerance / 100) * 255.0;
	}

	/**
	 * {@inheritDoc}
	 *
	 * @param Image &$imageResource
	 */
	#[Override]
	public function apply(object &$imageResource): void
	{
		$imageResource = $imageResource->colourspace(Interpretation::SRGB);

		if (!$imageResource->hasAlpha()) {
			$imageResource = $imageResource->bandjoin_const(255);
		}

		// Per-channel absolute differences from the "from" color

		$difference = $imageResource
		->subtract([$this->from->red, $this->from->green, $this->from->blue, $this->from->alpha])
		->abs();

		// Reduce to a single-band Chebyshev distance (per-pixel max across bands)

		$bands = $difference->bandsplit();

		$distance = array_shift($bands);

		foreach ($bands as $band) {
			$distance = $distance->more($band)->ifthenelse($distance, $band);
		}

		$tolerance = $this->normalizeTolerance();

		$mask = $this->invertMatch
			? $distance->more($tolerance)
			: $distance->lessEq($tolerance);

		// Replace matching pixels with the "to" color

		$imageResource = $mask
		->ifthenelse([$this->to->red, $this->to->green, $this->to->blue, $this->to->alpha], $imageResource)
		->cast(BandFormat::UCHAR);
	}
}
