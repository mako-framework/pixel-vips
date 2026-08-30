<?php

/**
 * @copyright Frederic G. Østby
 * @license   http://www.makoframework.com/license
 */

namespace mako\pixel\image\operations\vips;

use Jcupitt\Vips\BandFormat;
use Jcupitt\Vips\BlendMode;
use Jcupitt\Vips\Image;
use Jcupitt\Vips\Interpretation;
use mako\pixel\image\operations\Text as TextOperation;
use Override;

use function htmlspecialchars;

/**
 * {@inheritDoc}
 */
class Text extends TextOperation
{
	/**
	 * {@inheritDoc}
	 *
	 * @param Image &$imageResource
	 */
	#[Override]
	public function apply(object &$imageResource): void
	{
		$color = $this->font->color;

		// Render the text as an alpha mask

		$mask = Image::text(htmlspecialchars($this->text), [
			'fontfile' => $this->font->path,
			'dpi' => (int) ($this->font->size * 72 / 10),
		]);

		// Colorize the mask: RGB from the font color, alpha from the mask scaled by the color alpha

		$text = $mask
		->newFromImage([$color->red, $color->green, $color->blue])
		->bandjoin($mask->multiply($color->alpha / 255))
		->cast(BandFormat::UCHAR)
		->copy(['interpretation' => Interpretation::SRGB]);

		if (!$imageResource->hasAlpha()) {
			$imageResource = $imageResource->bandjoin_const(255);
		}

		$imageResource = $imageResource->composite2($text, BlendMode::OVER, [
			'x' => $this->position->x,
			'y' => $this->position->y - $mask->height,
		]);
	}
}
