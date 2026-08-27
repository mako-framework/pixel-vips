<?php

/**
 * @copyright Frederic G. Østby
 * @license   http://www.makoframework.com/license
 */

namespace mako\pixel\image\inspectors\vips;

use Jcupitt\Vips\Image;
use Jcupitt\Vips\Interpretation;
use mako\pixel\image\ColorSpace as ColorSpaceEnum;
use mako\pixel\image\inspectors\InspectorInterface;
use Override;

/**
 * Returns the color space of the image.
 *
 * @implements InspectorInterface<ColorSpaceEnum>
 */
class ColorSpace implements InspectorInterface
{
	/**
	 * {@inheritDoc}
	 *
	 * @param Image &$imageResource
	 */
	#[Override]
	public function inspect(object &$imageResource): mixed
	{
		return match ($imageResource->interpretation) {
			Interpretation::B_W, Interpretation::GREY16 => ColorSpaceEnum::Gray,
			Interpretation::CMYK => ColorSpaceEnum::Cmyk,
			Interpretation::LAB, Interpretation::LABQ, Interpretation::LABS => ColorSpaceEnum::Lab,
			Interpretation::LCH => ColorSpaceEnum::Lch,
			Interpretation::RGB16, Interpretation::SCRGB => ColorSpaceEnum::Rgb,
			Interpretation::SRGB => ColorSpaceEnum::Srgb,
			Interpretation::XYZ => ColorSpaceEnum::Xyz,
			Interpretation::YXY => ColorSpaceEnum::Yxy,
			default => ColorSpaceEnum::Undefined,
		};
	}
}
