<?php

/**
 * @copyright Frederic G. Østby
 * @license   http://www.makoframework.com/license
 */

namespace mako\pixel\image\operations\vips;

use Jcupitt\Vips\BlendMode;
use Jcupitt\Vips\Image;
use Jcupitt\Vips\Interpretation;
use mako\pixel\image\operations\Composite as CompositeOperation;
use mako\pixel\image\Vips;
use Override;

/**
 * {@inheritDoc}
 *
 * @extends CompositeOperation<Vips>
 */
class Composite extends CompositeOperation
{
	/**
	 * {@inheritDoc}
	 *
	 * @param Image &$imageResource
	 */
	#[Override]
	public function apply(object &$imageResource): void
	{
		if ($imageResource->interpretation === Interpretation::MULTIBAND) {
			$imageResource = $imageResource->colourspace(Interpretation::SRGB);
		}

		$composite = $this->image->getImageResource();

		if ($composite->interpretation === Interpretation::MULTIBAND) {
			$composite = $composite->colourspace(Interpretation::SRGB);
		}

		$blended = $imageResource->composite2(
			$composite,
			BlendMode::OVER,
			['x' => $this->position->x, 'y' => $this->position->y]
		);

		// Force the alpha to 255 wherever the composited image has
		// coverage, so semi-transparent pixels over fully transparent
		// areas survive GIF's 1-bit alpha threshold.

		if ($composite->hasAlpha()) {
			$mask = $composite
			->extract_band($composite->bands - 1)
			->more(0)
			->embed($this->position->x, $this->position->y, $blended->width, $blended->height);

			$alpha = $mask->ifthenelse(255, $blended->extract_band($blended->bands - 1));

			$blended = $blended->extract_band(0, ['n' => $blended->bands - 1])->bandjoin($alpha);
		}

		$imageResource = $blended;
	}
}
