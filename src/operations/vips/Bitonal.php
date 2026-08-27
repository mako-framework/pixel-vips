<?php

/**
 * @copyright Frederic G. Østby
 * @license   http://www.makoframework.com/license
 */

namespace mako\pixel\image\operations\vips;

use Jcupitt\Vips\Image;
use Jcupitt\Vips\Interpretation;
use mako\pixel\image\operations\OperationInterface;
use Override;

/**
 * Turns the image into bitonal.
 */
class Bitonal implements OperationInterface
{
	/**
	 * Luminance threshold.
	 */
	protected const int THRESHOLD = 128;

	/**
	 * {@inheritDoc}
	 *
	 * @param Image &$imageResource
	 */
	#[Override]
	public function apply(object &$imageResource): void
	{
		$alpha = null;

		if ($imageResource->hasAlpha()) {
			$alpha = $imageResource->extract_band($imageResource->bands - 1);

			$imageResource = $imageResource->extract_band(0, ['n' => $imageResource->bands - 1]);
		}

		// Convert to greyscale and threshold to pure black/white

		$imageResource = $imageResource
		->colourspace(Interpretation::B_W)
		->moreEq(static::THRESHOLD)
		->ifthenelse(255, 0);

		if ($alpha !== null) {
			$imageResource = $imageResource->bandjoin($alpha);
		}
	}
}
