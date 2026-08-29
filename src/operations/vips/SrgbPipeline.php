<?php

/**
 * @copyright Frederic G. Østby
 * @license   http://www.makoframework.com/license
 */

namespace mako\pixel\image\operations\vips;

use Jcupitt\Vips\Image;
use Jcupitt\Vips\Interpretation;
use mako\pixel\image\operations\SrgbPipeline as SrgbPipelineOperation;
use Override;

/**
 * {@inheritDoc}
 */
class SrgbPipeline extends SrgbPipelineOperation
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
