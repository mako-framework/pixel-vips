<?php

/**
 * @copyright Frederic G. Østby
 * @license   http://www.makoframework.com/license
 */

namespace mako\pixel\image\operations\vips;

use Jcupitt\Vips\Image as VipsImage;
use mako\pixel\image\operations\OperationInterface;
use mako\pixel\image\operations\traits\NormalizeTrait;
use Override;

use function array_fill;
use function tan;

/**
 * Adjusts the image contrast.
 */
class Contrast implements OperationInterface
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
	 * @param VipsImage &$imageResource
	 */
	#[Override]
	public function apply(object &$imageResource): void
	{
		if ($this->level === 0) {
			return;
		}

		$contrast = $this->normalizeLevel($this->level);

		$ratio = tan((($contrast + 100) / 200.0) * (M_PI / 2));

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
