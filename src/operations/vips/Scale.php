<?php

/**
 * @copyright Frederic G. Østby
 * @license   http://www.makoframework.com/license
 */

namespace mako\pixel\image\operations\vips;

use InvalidArgumentException;
use Jcupitt\Vips\Image;
use mako\pixel\image\operations\OperationInterface;
use Override;

/**
 * Scales the image.
 */
class Scale implements OperationInterface
{
	/**
	 * Constructor.
	 */
	public function __construct(
		protected int $percent {
			set(int $value) {
				if ($value <= 0) {
					throw new InvalidArgumentException('Scale percentage must be greater than zero.');
				}
				$this->percent = $value;
			}
		}
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
		if ($this->percent === 100) {
			return;
		}

		$imageResource = $imageResource->resize(
			$this->percent / 100,
			['kernel' => 'nearest']
		);
	}
}
