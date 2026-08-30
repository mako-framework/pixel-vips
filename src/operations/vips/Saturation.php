<?php

/**
 * @copyright Frederic G. Østby
 * @license   http://www.makoframework.com/license
 */

namespace mako\pixel\image\operations\vips;

use Jcupitt\Vips\BandFormat;
use Jcupitt\Vips\Image;
use Jcupitt\Vips\Interpretation;
use mako\pixel\image\operations\Saturation as SaturationOperation;
use Override;

/**
 * {@inheritDoc}
 */
class Saturation extends SaturationOperation
{
	/**
	 * {@inheritDoc}
	 *
	 * @param Image &$imageResource
	 */
	#[Override]
	public function apply(object &$imageResource): void
	{
		if ($this->level === 0) {
			return;
		}

		// Map the normalized level (-100 to 100) to a chroma multiplier (0 to 2)

		$multiplier = (100 + $this->level) / 100;

		$alpha = null;

		if ($imageResource->hasAlpha()) {
			$alpha = $imageResource->extract_band($imageResource->bands - 1);

			$imageResource = $imageResource->extract_band(0, ['n' => $imageResource->bands - 1]);
		}

		// Scale the chroma band in LCh colourspace (L and h are left untouched)

		$imageResource = $imageResource
		->colourspace(Interpretation::LCH)
		->linear([1, $multiplier, 1], [0, 0, 0])
		->colourspace(Interpretation::SRGB)
		->cast(BandFormat::UCHAR);

		if ($alpha !== null) {
			$imageResource = $imageResource->bandjoin($alpha);
		}
	}
}
