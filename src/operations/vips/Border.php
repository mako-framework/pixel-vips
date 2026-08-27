<?php

/**
 * @copyright Frederic G. Østby
 * @license   http://www.makoframework.com/license
 */

namespace mako\pixel\image\operations\vips;

use Jcupitt\Vips\Image;
use mako\pixel\image\Color;
use mako\pixel\image\operations\OperationInterface;
use mako\pixel\image\operations\vips\traits\SvgTrait;
use Override;

use function max;

/**
 * Adds a border to the image.
 */
class Border implements OperationInterface
{
	use SvgTrait;
	/**
	 * Constructor.
	 */
	public function __construct(
		protected Color $color = new Color(0, 0, 0),
		protected int $width = 4
	) {
		$this->width = max(0, $this->width);
	}

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
