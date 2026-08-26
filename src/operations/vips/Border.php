<?php

/**
 * @copyright Frederic G. Østby
 * @license   http://www.makoframework.com/license
 */

namespace mako\pixel\image\operations\vips;

use Jcupitt\Vips\Image;
use mako\pixel\image\Color;
use mako\pixel\image\operations\OperationInterface;
use Override;

use function in_array;
use function max;

/**
 * Adds a border to the image.
 */
class Border implements OperationInterface
{
	/**
	 * Constructor.
	 */
	public function __construct(
		protected Color $color = new Color(0, 0, 0),
		protected int $width = 5
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
		$border = max(0, $this->width);

		if ($border === 0) {
			return;
		}

		$width = $imageResource->width;
		$height = $imageResource->height;

		$hasPageHeight = in_array('page-height', $imageResource->getFields(), true);

		$pageHeight = $hasPageHeight ? (int) $imageResource->get('page-height') : $height;

		$hasAlpha = $imageResource->bands === 4;

		$color = [
			$this->color->red,
			$this->color->green,
			$this->color->blue,
			$this->color->alpha,
		];

		$innerWidth = $width - ($border * 2);
		$innerHeight = $pageHeight - ($border * 2);

		$frames = [];

		for ($top = 0; $top < $height; $top += $pageHeight) {
			$frame = $imageResource->crop(0, $top, $width, $pageHeight);

			if (!$hasAlpha) {
				$frame = $frame->bandjoin(255);
			}

			// Build the border overlay from the frame itself so it
			// inherits the correct interpretation (srgb).

			$overlay = $frame->newFromImage($color)->cast('uchar');

			if ($innerWidth > 0 && $innerHeight > 0) {
				$hole = $frame->newFromImage([0, 0, 0, 0])
				->cast('uchar')
				->crop(0, 0, $innerWidth, $innerHeight);

				$overlay = $overlay->insert($hole, $border, $border);
			}

			// Blend the semi-transparent border onto the frame, then force
			// the border ring's alpha to be at least the frame's alpha
			// (255 over transparent pixels stays blended elsewhere).

			$blended = $frame->composite2($overlay, 'over');

			$mask = $overlay->extract_band(3)->more(0);

			$alpha = $mask->ifthenelse(255, $blended->extract_band(3));

			$frames[] = $blended->extract_band(0, ['n' => 3])->bandjoin($alpha);
		}

		$imageResource = Image::arrayjoin($frames, ['across' => 1]);

		if ($hasPageHeight) {
			$imageResource->set('page-height', $pageHeight);
		}
	}
}
