<?php

/**
 * @copyright Frederic G. Østby
 * @license   http://www.makoframework.com/license
 */

namespace mako\pixel\image\operations\vips;

use Jcupitt\Vips\Align;
use Jcupitt\Vips\BandFormat;
use Jcupitt\Vips\BlendMode;
use Jcupitt\Vips\Image;
use mako\pixel\image\operations\TextBox as TextBoxOperation;
use mako\pixel\image\operations\vips\traits\SvgTrait;
use Override;

use function htmlspecialchars;

/**
 * {@inheritDoc}
 */
class TextBox extends TextBoxOperation
{
	use SvgTrait;

	/**
	 * Draws the box.
	 */
	protected function drawBox(Image $imageResource): Image
	{
		$this->compositeSvg(
			$imageResource,
			'<rect x="%d" y="%d" width="%d" height="%d" fill="%s" stroke="%s" stroke-width="%d"/>',
			$this->position->x,
			$this->position->y,
			$this->dimensions->width,
			$this->dimensions->height,
			$this->fill?->toRgbaString() ?? 'none',
			$this->stroke?->toRgbaString() ?? 'none',
			$this->stroke !== null ? $this->strokeWidth : 0
		);

		return $imageResource;
	}

	/**
	 * Draws the text.
	 */
	protected function drawText(Image $imageResource): Image
	{
		// Render the text as a centered, multi-line alpha mask

		$mask = Image::text(htmlspecialchars($this->text), [
			'fontfile' => $this->font->path,
			'dpi' => (int) ($this->font->size * 72 / 10),
			'align' => Align::CENTRE,
			'width' => $this->dimensions->width,
			'rgba' => true,
		]);

		// Colorize the mask using the font color

		$color = $this->font->color;

		$alpha = $mask->extract_band(3)->multiply($color->alpha / 255);

		$text = $mask
		->newFromImage([$color->red, $color->green, $color->blue])
		->bandjoin($alpha);

		// Composite centered within the box

		$x = $this->position->x + (int) (($this->dimensions->width - $mask->width) / 2);
		$y = $this->position->y + (int) (($this->dimensions->height - $mask->height) / 2);

		return $imageResource->composite2($text->cast(BandFormat::UCHAR), BlendMode::OVER, ['x' => $x, 'y' => $y]);
	}

	/**
	 * {@inheritDoc}
	 *
	 * @param Image &$imageResource
	 */
	#[Override]
	public function apply(object &$imageResource): void
	{
		if (!$imageResource->hasAlpha()) {
			$imageResource = $imageResource->bandjoin_const(255);
		}

		if ($this->fill !== null || $this->stroke !== null) {
			$imageResource = $this->drawBox($imageResource);
		}

		$imageResource = $this->drawText($imageResource);
	}
}
