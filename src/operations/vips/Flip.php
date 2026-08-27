<?php

/**
 * @copyright Frederic G. Østby
 * @license   http://www.makoframework.com/license
 */

namespace mako\pixel\image\operations\vips;

use Jcupitt\Vips\Direction;
use Jcupitt\Vips\Image;
use mako\pixel\image\operations\Flip as FlipDirection;
use mako\pixel\image\operations\OperationInterface;
use Override;

/**
 * Flips the image.
 */
class Flip implements OperationInterface
{
	/**
	 * Constructor.
	 */
	public function __construct(
		protected FlipDirection $direction = FlipDirection::Horizontal
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
		$imageResource = $imageResource->flip(
			$this->direction === FlipDirection::Vertical ? Direction::VERTICAL : Direction::HORIZONTAL
		);
	}
}
