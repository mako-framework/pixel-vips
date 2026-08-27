<?php

/**
 * @copyright Frederic G. Østby
 * @license   http://www.makoframework.com/license
 */

namespace mako\pixel\image\operations\vips;

use Jcupitt\Vips\Image;
use Jcupitt\Vips\Interpretation;
use mako\pixel\image\Color;
use mako\pixel\image\geometry\Point;
use mako\pixel\image\operations\OperationInterface;
use mako\pixel\image\traits\PixelValidationTrait;
use Override;

/**
 * Draws a pixel on the image at the specified coordinates.
 */
class Pixel implements OperationInterface
{
	use PixelValidationTrait;

	/**
	 * Constructor.
	 */
	public function __construct(
		protected Point $pixel,
		protected Color $color
	) {
	}

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
		->cast('uchar')
		->copy(['interpretation' => Interpretation::SRGB]);

		$imageResource = $imageResource->insert($pixel, $this->pixel->x, $this->pixel->y);
	}
}
