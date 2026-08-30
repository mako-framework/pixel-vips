<?php

/**
 * @copyright Frederic G. Østby
 * @license   http://www.makoframework.com/license
 */

namespace mako\pixel\image\operations\vips;

use Jcupitt\Vips\Image as VipsImage;
use mako\pixel\image\operations\Gamma as GammaOperation;
use Override;

/**
 * {@inheritDoc}
 */
class Gamma extends GammaOperation
{
	/**
	 * {@inheritDoc}
	 *
	 * @param VipsImage &$imageResource
	 */
	#[Override]
	public function apply(object &$imageResource): void
	{
		if ($this->gamma === 1.0) {
			return;
		}

		if (!$imageResource->hasAlpha()) {
			$imageResource = $imageResource->gamma(['exponent' => $this->gamma]);

			return;
		}

		$bands = $imageResource->bands - 1;

		$color = $imageResource->extract_band(0, ['n' => $bands])->gamma(['exponent' => $this->gamma]);

		$alpha = $imageResource->extract_band($bands);

		$imageResource = $color->bandjoin($alpha);
	}
}
