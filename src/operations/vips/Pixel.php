<?php

/**
 * @copyright Frederic G. Østby
 * @license   http://www.makoframework.com/license
 */

namespace mako\pixel\image\operations\vips;

use Jcupitt\Vips\BandFormat;
use Jcupitt\Vips\Image;
use Jcupitt\Vips\Interpretation;
use mako\pixel\image\operations\Pixel as PixelOperation;
use mako\pixel\image\traits\PixelValidationTrait;
use Override;

/**
 * {@inheritDoc}
 */
class Pixel extends PixelOperation
{
	use PixelValidationTrait;

	/**
	 * {@inheritDoc}
	 *
	 * @param Image &$imageResource
	 */
	#[Override]
	public function apply(object &$imageResource): void
	{
		$this->validatePixel($this->pixel, $imageResource->width, $imageResource->height);

		if (!$imageResource->hasAlpha()) {
			$imageResource = $imageResource->bandjoin_const(255);
		}

		$pixel = Image::black(1, 1)
		->add([$this->color->red, $this->color->green, $this->color->blue, $this->color->alpha])
		->cast(BandFormat::UCHAR)
		->copy(['interpretation' => Interpretation::SRGB]);

		$imageResource = $imageResource->insert($pixel, $this->pixel->x, $this->pixel->y);
	}
}
