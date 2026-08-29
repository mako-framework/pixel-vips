<?php

/**
 * @copyright Frederic G. Østby
 * @license   http://www.makoframework.com/license
 */

namespace mako\pixel\image\operations\vips;

use Jcupitt\Vips\Image;
use mako\pixel\image\operations\Rotate as RotateOperation;
use Override;

/**
 * {@inheritDoc}
 */
class Rotate extends RotateOperation
{
	/**
	 * {@inheritDoc}
	 *
	 * @param Image &$imageResource
	 */
	#[Override]
	public function apply(object &$imageResource): void
	{
		$degrees = (($this->degrees % 360) + 360) % 360;

		if ($degrees === 0) {
			return;
		}

		// Use the fast lossless rotations for right angles

		if ($degrees % 90 === 0) {
			$imageResource = match ($degrees) {
				90 => $imageResource->rot90(),
				180 => $imageResource->rot180(),
				default => $imageResource->rot270(),
			};

			return;
		}

		// Arbitrary angles require interpolation and a transparent background

		if (!$imageResource->hasAlpha()) {
			$imageResource = $imageResource->bandjoin_const(255);
		}

		$imageResource = $imageResource->similarity([
			'angle' => $degrees,
			'background' => [0, 0, 0, 0],
		]);
	}
}
