<?php

/**
 * @copyright Frederic G. Østby
 * @license   http://www.makoframework.com/license
 */

namespace mako\pixel\image\operations\vips;

use Jcupitt\Vips\Image;
use mako\pixel\image\operations\OperationInterface;
use mako\pixel\image\operations\traits\NormalizeTrait;
use Override;

/**
 * Adjusts the opacity of the image.
 */
class Opacity implements OperationInterface
{
	use NormalizeTrait;

	public function __construct(
		protected int $opacity
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
		if (!$imageResource->hasAlpha()) {
			$imageResource = $imageResource->bandjoin_const([255]);
		}

		$bands = $imageResource->bands;

		$factor = $this->normalizePercent($this->opacity) / 100;

		$colorBands = $imageResource->extract_band(0, ['n' => $bands - 1]);

		$alphaBand = $imageResource->extract_band($bands - 1)->linear($factor, 0);

		$imageResource = $colorBands->bandjoin($alphaBand);
	}
}
