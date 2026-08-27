<?php

/**
 * @copyright Frederic G. Østby
 * @license   http://www.makoframework.com/license
 */

namespace mako\pixel\image\operations\vips;

use Jcupitt\Vips\Image;
use Jcupitt\Vips\Interpretation;
use mako\pixel\image\operations\OperationInterface;
use mako\pixel\image\operations\traits\NormalizeTrait;
use Override;

/**
 * Adjusts the color saturation.
 */
class Saturation implements OperationInterface
{
	use NormalizeTrait;

	/**
	 * Constructor.
	 */
	public function __construct(
		protected int $level = 0
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
		if ($this->level === 0) {
			return;
		}

		// Map the normalized level (-100 to 100) to a chroma multiplier (0 to 2)

		$multiplier = (100 + $this->normalizeLevel($this->level)) / 100;

		$alpha = null;

		if ($imageResource->hasAlpha()) {
			$alpha = $imageResource->extract_band($imageResource->bands - 1);

			$imageResource = $imageResource->extract_band(0, ['n' => $imageResource->bands - 1]);
		}

		// Scale the chroma band in LCh colourspace (L and h are left untouched)

		$imageResource = $imageResource
		->colourspace(Interpretation::LCH)
		->linear([1, $multiplier, 1], [0, 0, 0])
		->colourspace(Interpretation::SRGB)
		->cast('uchar');

		if ($alpha !== null) {
			$imageResource = $imageResource->bandjoin($alpha);
		}
	}
}
