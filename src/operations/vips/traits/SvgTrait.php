<?php

/**
 * @copyright Frederic G. Østby
 * @license   http://www.makoframework.com/license
 */

namespace mako\pixel\image\operations\vips\traits;

use Jcupitt\Vips\BlendMode;
use Jcupitt\Vips\Image;

use function sprintf;

/**
 * Common SVG drawing methods.
 */
trait SvgTrait
{
	/**
	 * Composites an SVG overlay onto the image.
	 *
	 * The SVG is built from the provided sprintf template and parameters,
	 * sized to match the image dimensions. An alpha channel is added to the
	 * image if missing, and the resulting alpha is flattened to binary
	 * (fully opaque or fully transparent) to avoid artifacts in formats
	 * with 1-bit alpha such as GIF.
	 */
	protected function compositeSvg(Image &$imageResource, string $template, mixed ...$params): void
	{
		$svg = sprintf(<<<'SVG'
			<svg xmlns="http://www.w3.org/2000/svg" width="%d" height="%d">
				%s
			</svg>
			SVG,
			$imageResource->width,
			$imageResource->height,
			sprintf($template, ...$params)
		);

		$overlay = Image::svgload_buffer($svg);

		if (!$imageResource->hasAlpha()) {
			$imageResource = $imageResource->bandjoin_const(255);
		}

		$imageResource = $imageResource->composite2($overlay, BlendMode::OVER);

		// GIF only supports 1-bit alpha, so partially transparent (anti-aliased)
		// stroke pixels would be quantized away. Make alpha binary instead.

		$alpha = $imageResource->extract_band($imageResource->bands - 1);

		$imageResource = $imageResource
		->extract_band(0, ['n' => $imageResource->bands - 1])
		->bandjoin($alpha->more(0)->ifthenelse(255, 0));
	}
}
