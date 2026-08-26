<?php

/**
 * @copyright Frederic G. Østby
 * @license   http://www.makoframework.com/license
 */

namespace mako\pixel\image\operations\vips;

use InvalidArgumentException;
use Jcupitt\Vips\Image as VipsImage;
use mako\pixel\image\operations\OperationInterface;
use Override;

/**
 * Adjusts the gamma level of the image.
 */
class Gamma implements OperationInterface
{
	/**
	 * Constructor.
	 */
	public function __construct(
		protected float $gamma
	) {
		if ($gamma <= 0) {
			throw new InvalidArgumentException('Gamma must be greater than 0.');
		}
	}

	/**
	 * {@inheritDoc}
	 *
	 * @param VipsImage &$imageResource
	 */
	#[Override]
	public function apply(object &$imageResource): void
	{
		if ($this->gamma === 1.0) {
			return;
		}

		$exponent = 1.0 / ($this->gamma * 1.18);

		if (!$imageResource->hasAlpha()) {
			$imageResource = $imageResource->gamma(['exponent' => $exponent]);

			return;
		}

		$bands = $imageResource->bands - 1;

		$color = $imageResource->extract_band(0, ['n' => $bands])->gamma(['exponent' => $exponent]);

		$alpha = $imageResource->extract_band($bands);

		$imageResource = $color->bandjoin($alpha);
	}
}
