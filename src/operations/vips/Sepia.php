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
 * Turns the image into sepia.
 */
class Sepia implements OperationInterface
{
	/**
	 * {@inheritDoc}
	 *
	 * @param Image &$imageResource
	 */
	#[Override]
	public function apply(object &$imageResource): void
	{
		$imageResource = $imageResource->colourspace(Interpretation::SRGB);

		$alpha = null;

		if ($imageResource->hasAlpha()) {
			$alpha = $imageResource->extract_band($imageResource->bands - 1);

			$imageResource = $imageResource->extract_band(0, ['n' => $imageResource->bands - 1]);
		}

		$matrix = Image::newFromArray([
			[0.393 * 0.85, 0.769 * 0.85, 0.189 * 0.85],
			[0.349 * 0.85, 0.686 * 0.85, 0.168 * 0.85],
			[0.272 * 0.85, 0.534 * 0.85, 0.131 * 0.85],
		]);

		$imageResource = $imageResource->recomb($matrix)->cast('uchar');

		if ($alpha !== null) {
			$imageResource = $imageResource->bandjoin($alpha);
		}
	}
}
