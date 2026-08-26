<?php

/**
 * @copyright Frederic G. Østby
 * @license   http://www.makoframework.com/license
 */

namespace mako\pixel\image\operations\vips;

use Jcupitt\Vips\Image as VipsImage;
use mako\pixel\image\operations\OperationInterface;
use Override;

use function array_map;
use function array_sum;

/**
 * Sharpens the image.
 */
class Sharpen implements OperationInterface
{
	/**
	 * {@inheritDoc}
	 *
	 * @param VipsImage &$imageResource
	 */
	#[Override]
	public function apply(object &$imageResource): void
	{
		$sharpen = [[-2, -1.6, -2], [-1.6, 22, -1.6], [-2, -1.6, -2]];

		$scale = array_sum(array_map(array_sum(...), $sharpen));

		$mask = VipsImage::newFromArray($sharpen, $scale);

		if (!$imageResource->hasAlpha()) {
			$imageResource = $imageResource->conv($mask);

			return;
		}

		$bands = $imageResource->bands - 1;

		$color = $imageResource->extract_band(0, ['n' => $bands])->conv($mask);

		$alpha = $imageResource->extract_band($bands);

		$imageResource = $color->bandjoin($alpha);
	}
}
