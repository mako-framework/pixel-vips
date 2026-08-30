<?php

/**
 * @copyright Frederic G. Østby
 * @license   http://www.makoframework.com/license
 */

namespace mako\pixel\image\operations\vips;

use Jcupitt\Vips\Image as VipsImage;
use mako\pixel\image\operations\Contrast as ContrastOperation;
use Override;

use function array_fill;
use function tan;

/**
 * {@inheritDoc}
 */
class Contrast extends ContrastOperation
{
	/**
	 * {@inheritDoc}
	 *
	 * @param VipsImage &$imageResource
	 */
	#[Override]
	public function apply(object &$imageResource): void
	{
		if ($this->level === 0) {
			return;
		}

		$ratio = tan((($this->level + 100) / 200.0) * (M_PI / 2));

		$offset = 127 * (1.0 - $ratio);

		$hasAlpha = $imageResource->hasAlpha();

		$colorBands = $hasAlpha ? $imageResource->bands - 1 : $imageResource->bands;

		$a = array_fill(0, $colorBands, $ratio);
		$b = array_fill(0, $colorBands, $offset);

		if ($hasAlpha) {
			$a[] = 1.0;
			$b[] = 0.0;
		}

		$imageResource = $imageResource->linear($a, $b);
	}
}
