<?php

/**
 * @copyright Frederic G. Østby
 * @license   http://www.makoframework.com/license
 */

namespace mako\pixel\image\operations\vips;

use Jcupitt\Vips\Image;
use Jcupitt\Vips\Kernel;
use mako\pixel\image\operations\Scale as ScaleOperation;
use Override;

/**
 * {@inheritDoc}
 */
class Scale extends ScaleOperation
{
	/**
	 * {@inheritDoc}
	 *
	 * @param Image &$imageResource
	 */
	#[Override]
	public function apply(object &$imageResource): void
	{
		if ($this->percent === 100) {
			return;
		}

		$imageResource = $imageResource->resize(
			$this->percent / 100,
			['kernel' => Kernel::NEAREST]
		);
	}
}
