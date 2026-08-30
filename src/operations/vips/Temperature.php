<?php

/**
 * @copyright Frederic G. Østby
 * @license   http://www.makoframework.com/license
 */

namespace mako\pixel\image\operations\vips;

use Jcupitt\Vips\BandFormat;
use Jcupitt\Vips\Image;
use Jcupitt\Vips\Interpretation;
use mako\pixel\image\operations\Temperature as TemperatureOperation;
use Override;

/**
 * {@inheritDoc}
 */
class Temperature extends TemperatureOperation
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

		$imageResource = $imageResource->colourspace(Interpretation::SRGB);

		$alpha = null;

		if ($imageResource->hasAlpha()) {
			$alpha = $imageResource->extract_band($imageResource->bands - 1);

			$imageResource = $imageResource->extract_band(0, ['n' => $imageResource->bands - 1]);
		}

		$shift = $this->level * 0.0022;

		// Warm: boost red, reduce blue - Cool: the opposite

		$imageResource = $imageResource
		->linear([1 + $shift, 1, 1 - $shift], [0, 0, 0])
		->cast(BandFormat::UCHAR);

		if ($alpha !== null) {
			$imageResource = $imageResource->bandjoin($alpha);
		}
	}
}
