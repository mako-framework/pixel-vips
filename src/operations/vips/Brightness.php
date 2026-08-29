<?php

/**
 * @copyright Frederic G. Østby
 * @license   http://www.makoframework.com/license
 */

namespace mako\pixel\image\operations\vips;

use Jcupitt\Vips\Image;
use mako\pixel\image\operations\Brightness as BrightnessOperation;
use mako\pixel\image\operations\traits\NormalizeTrait;
use Override;

/**
 * {@inheritDoc}
 */
class Brightness extends BrightnessOperation
{
	use NormalizeTrait;

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

		$alpha = null;

		if ($imageResource->hasAlpha()) {
			$alpha = $imageResource->extract_band($imageResource->bands - 1);

			$imageResource = $imageResource->extract_band(0, ['n' => $imageResource->bands - 1]);
		}

		// Map the normalized level (-100 to 100) to an offset of -255 to 255

		$offset = $this->normalizeLevel($this->level) / 100 * 255;

		$imageResource = $imageResource->linear(1, $offset)->cast('uchar');

		if ($alpha !== null) {
			$imageResource = $imageResource->bandjoin($alpha);
		}
	}
}
