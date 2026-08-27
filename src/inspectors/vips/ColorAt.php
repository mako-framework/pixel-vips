<?php

/**
 * @copyright Frederic G. Østby
 * @license   http://www.makoframework.com/license
 */

namespace mako\pixel\image\inspectors\vips;

use Jcupitt\Vips\Image;
use Jcupitt\Vips\Interpretation;
use mako\pixel\image\Color;
use mako\pixel\image\geometry\Point;
use mako\pixel\image\inspectors\InspectorInterface;
use mako\pixel\image\traits\PixelValidationTrait;
use Override;

/**
 * Returns the color of the specified pixel.
 *
 * @implements InspectorInterface<Color>
 */
class ColorAt implements InspectorInterface
{
	use PixelValidationTrait;

	/**
	 * Constructor.
	 */
	public function __construct(
		protected Point $pixel
	) {
	}

	/**
	 * {@inheritDoc}
	 *
	 * @param Image &$imageResource
	 */
	#[Override]
	public function inspect(object &$imageResource): mixed
	{
		$this->validatePixel($this->pixel, $imageResource->width, $imageResource->height);

		$image = $imageResource->copyMemory();

		if ($image->interpretation !== Interpretation::SRGB) {
			$image = $image->colourspace(Interpretation::SRGB);
		}

		$pixel = $image->getpoint($this->pixel->x, $this->pixel->y);

		return new Color(
			(int) $pixel[0],
			(int) $pixel[1],
			(int) $pixel[2],
			(int) ($pixel[3] ?? 255)
		);
	}
}
