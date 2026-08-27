<?php

/**
 * @copyright Frederic G. Østby
 * @license   http://www.makoframework.com/license
 */

namespace mako\pixel\image\operations\vips;

use Jcupitt\Vips\Image;
use mako\pixel\image\operations\OperationInterface;
use Override;

/**
 * Inverts the colors of the image.
 */
class Invert implements OperationInterface
{
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

		$imageResource = $imageResource->invert();

		if ($alpha !== null) {
			$imageResource = $imageResource->bandjoin($alpha);
		}
	}
}
