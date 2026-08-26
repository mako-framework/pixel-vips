<?php

/**
 * @copyright Frederic G. Østby
 * @license   http://www.makoframework.com/license
 */

namespace mako\pixel\image\operations\vips;

use InvalidArgumentException;
use Jcupitt\Vips\Align;
use Jcupitt\Vips\BlendMode;
use Jcupitt\Vips\Image;
use mako\pixel\image\Color;
use mako\pixel\image\geometry\Dimensions;
use mako\pixel\image\geometry\Point;
use mako\pixel\image\operations\Font;
use mako\pixel\image\operations\OperationInterface;
use Override;

use function htmlspecialchars;
use function sprintf;

/**
 * Draws a text box on the image.
 */
class TextBox implements OperationInterface
{
	/**
	 * Constructor.
	 */
	public function __construct(
		protected string $text,
		protected Dimensions $dimensions,
		protected Font $font,
		protected ?Color $fill = null,
		protected ?Color $stroke = null,
		protected int $strokeWidth = 1,
		protected Point $position = new Point(0, 0)
	) {
		if ($this->stroke !== null && $this->strokeWidth < 1) {
			throw new InvalidArgumentException('Stroke width must be greater than 0.');
		}
	}

	/**
	 * Draws the box.
	 */
	protected function drawBox(Image $imageResource): Image
	{
		$svg = sprintf(<<<'SVG'
			<svg xmlns="http://www.w3.org/2000/svg" width="%d" height="%d">
				<rect x="%d" y="%d" width="%d" height="%d" fill="%s" stroke="%s" stroke-width="%d"/>
			</svg>
			SVG,
			$imageResource->width,
			$imageResource->height,
			$this->position->x,
			$this->position->y,
			$this->dimensions->width,
			$this->dimensions->height,
			$this->fill?->toRgbaString() ?? 'none',
			$this->stroke?->toRgbaString() ?? 'none',
			$this->stroke !== null ? $this->strokeWidth : 0
		);

		return $imageResource->composite2(Image::svgload_buffer($svg), BlendMode::OVER);
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

		$imageResource = $imageResource->composite2($text->cast('uchar'), BlendMode::OVER, ['x' => $x, 'y' => $y]);

		// GIF only supports 1-bit alpha, so partially transparent (anti-aliased)
        // stroke pixels would be quantized away. Make alpha binary instead.

		$alpha = $imageResource->extract_band($imageResource->bands - 1);

		return $imageResource
		->extract_band(0, ['n' => $imageResource->bands - 1])
		->bandjoin($alpha->more(0)->ifthenelse(255, 0));
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
