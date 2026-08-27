<?php

/**
 * @copyright Frederic G. Østby
 * @license   http://www.makoframework.com/license
 */

namespace mako\pixel\image\operations\vips;

use Jcupitt\Vips\Image;
use Jcupitt\Vips\Interpretation;
use mako\pixel\image\operations\Pipeline;
use Override;

/**
 * Temporarily converts the image to sRGB while applying the pipelined operations,
 * then restores the original color space.
 */
class SrgbPipeline extends Pipeline
{
	/**
	 * {@inheritDoc}
	 *
	 * @param Image &$imageResource
	 */
	#[Override]
	public function apply(object &$imageResource): void
	{
		$originalInterpretation = $imageResource->interpretation;

		$needsConversion = $originalInterpretation !== Interpretation::SRGB;

		if ($needsConversion) {
			$imageResource = $imageResource->colourspace(Interpretation::SRGB);
		}

		try {
			parent::apply($imageResource);
		}
		finally {
			if ($needsConversion) {
				$imageResource = $imageResource->colourspace($originalInterpretation);
			}
		}
	}
}
