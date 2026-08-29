<?php

/**
 * @copyright Frederic G. Østby
 * @license   http://www.makoframework.com/license
 */

namespace mako\pixel\image\operations\vips;

use Jcupitt\Vips\Direction;
use Jcupitt\Vips\Image;
use mako\pixel\image\operations\Flip as FlipOperation;
use mako\pixel\image\operations\FlipDirection;
use Override;

/**
 * {@inheritDoc}
 */
class Flip extends FlipOperation
{
	/**
	 * {@inheritDoc}
	 *
	 * @param Image &$imageResource
	 */
	#[Override]
	public function apply(object &$imageResource): void
	{
		$imageResource = $imageResource->flip(
			$this->direction === FlipDirection::Vertical ? Direction::VERTICAL : Direction::HORIZONTAL
		);
	}
}
