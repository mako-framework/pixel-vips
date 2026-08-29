<?php

/**
 * @copyright Frederic G. Østby
 * @license   http://www.makoframework.com/license
 */

namespace mako\pixel\image\operations\vips;

use Jcupitt\Vips\Image;
use mako\pixel\image\operations\Border as BorderOperation;
use mako\pixel\image\operations\vips\traits\SvgTrait;
use Override;

/**
 * {@inheritDoc}
 */
class Border extends BorderOperation
{
	use SvgTrait;

	/**
	 * {@inheritDoc}
	 *
	 * @param Image &$imageResource
	 */
	#[Override]
	public function apply(object &$imageResource): void
	{
		if ($this->width === 0) {
			return;
		}

		$this->compositeSvg(
			$imageResource,
			'<rect x="%s" y="%s" width="%s" height="%s" fill="none" stroke="%s" stroke-width="%d"/>',
			$this->width / 2,
			$this->width / 2,
			$imageResource->width - $this->width,
			$imageResource->height - $this->width,
			$this->color->toRgbaString(),
			$this->width
		);
	}
}
