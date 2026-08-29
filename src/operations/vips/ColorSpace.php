<?php

/**
 * @copyright Frederic G. Østby
 * @license   http://www.makoframework.com/license
 */

namespace mako\pixel\image\operations\vips;

use Jcupitt\Vips\Image;
use Jcupitt\Vips\Interpretation;
use mako\pixel\image\ColorSpace as ColorSpaceEnum;
use mako\pixel\image\exceptions\ImageException;
use mako\pixel\image\operations\ColorSpace as ColorSpaceOperation;
use Override;

use function sprintf;

/**
 * {@inheritDoc}
 */
class ColorSpace extends ColorSpaceOperation
{
	/**
	 * {@inheritDoc}
	 *
	 * @param Image &$imageResource
	 */
	#[Override]
	public function apply(object &$imageResource): void
	{
		$interpretation = match ($this->colorSpace) {
			ColorSpaceEnum::Cmyk => Interpretation::CMYK,
			ColorSpaceEnum::Gray => Interpretation::B_W,
			ColorSpaceEnum::Lab => Interpretation::LAB,
			ColorSpaceEnum::Rgb => Interpretation::RGB16,
			ColorSpaceEnum::Srgb => Interpretation::SRGB,
			ColorSpaceEnum::Xyz => Interpretation::XYZ,
			default => null,
		};

		if ($interpretation === null) {
			throw new ImageException(sprintf(
				'The [ %s ] color space is not supported by the VIPS driver.',
				$this->colorSpace->value
			));
		}

		$imageResource = $imageResource->colourspace($interpretation);
	}
}
