<?php

/**
 * @copyright Frederic G. Østby
 * @license   http://www.makoframework.com/license
 */

namespace mako\pixel\image\operations\vips;

use Jcupitt\Vips\Image;
use Jcupitt\Vips\Interpretation;
use mako\pixel\image\operations\Colorize as ColorizeOperation;
use Override;

/**
 * {@inheritDoc}
 */
class Colorize extends ColorizeOperation
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

		// Add the color to each channel (values are clamped to 0-255 by the cast)

		$imageResource = $imageResource
		->linear([1, 1, 1], [$this->color->red, $this->color->green, $this->color->blue])
		->cast('uchar');

		if ($alpha !== null) {
			$imageResource = $imageResource->bandjoin($alpha);
		}
	}
}
