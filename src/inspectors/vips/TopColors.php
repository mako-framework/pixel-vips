<?php

/**
 * @copyright Frederic G. Østby
 * @license   http://www.makoframework.com/license
 */

namespace mako\pixel\image\inspectors\vips;

use Jcupitt\Vips\BandFormat;
use Jcupitt\Vips\Image;
use Jcupitt\Vips\Interpretation;
use mako\pixel\image\Color;
use mako\pixel\image\inspectors\TopColors as TopColorsInspector;
use Override;

use function array_slice;
use function ord;
use function sqrt;
use function strlen;
use function usort;

/**
 * {@inheritDoc}
 */
class TopColors extends TopColorsInspector
{
	/**
	 * Maximum number of pixels to sample.
	 */
	protected const int MAX_SAMPLE_PIXELS = 65_536;

	/**
	 * {@inheritDoc}
	 *
	 * @param Image &$imageResource
	 */
	#[Override]
	public function inspect(object &$imageResource): mixed
	{
		$image = $imageResource->colourspace(Interpretation::SRGB);

		if (!$image->hasAlpha()) {
			$image = $image->bandjoin_const(255);
		}

		// Downsample large images so that at most ~MAX_SAMPLE_PIXELS pixels are inspected

		$pixels = $image->width * $image->height;

		if ($pixels > static::MAX_SAMPLE_PIXELS) {
			$image = $image->resize(sqrt(static::MAX_SAMPLE_PIXELS / $pixels), ['kernel' => 'nearest']);
		}

		// Extract the raw RGBA bytes and group similar colors by quantizing
		// each RGB channel to its two most significant bits

		$bytes = $image->cast(BandFormat::UCHAR)->writeToMemory();

		$length = strlen($bytes);

		$colorCounts = [];
		$groups = [];

		for ($i = 0; $i < $length; $i += 4) {
			$a = ord($bytes[$i + 3]);

			if ($this->ignoreTransparent && $a === 0) {
				continue;
			}

			$r = ord($bytes[$i]);
			$g = ord($bytes[$i + 1]);
			$b = ord($bytes[$i + 2]);

			$color = ($a << 24) | ($r << 16) | ($g << 8) | $b;

			$key = $color & 0x00C0C0C0;

			$colorCounts[$key][$color] ??= 0;

			$groups[$key] ??= [
				'totalCount' => 0,
				'dominantCount' => 0,
				'r' => 0,
				'g' => 0,
				'b' => 0,
				'a' => 0,
			];

			$colorCount = ++$colorCounts[$key][$color];

			$groups[$key]['totalCount']++;

			if ($colorCount > $groups[$key]['dominantCount']) {
				$groups[$key]['dominantCount'] = $colorCount;
				$groups[$key]['r'] = $r;
				$groups[$key]['g'] = $g;
				$groups[$key]['b'] = $b;
				$groups[$key]['a'] = $a;
			}
		}

		usort($groups, fn (array $a, array $b): int => $b['totalCount'] <=> $a['totalCount']);

		$colors = [];

		foreach (array_slice($groups, 0, $this->limit) as $group) {
			$colors[] = new Color(
				$group['r'],
				$group['g'],
				$group['b'],
				$group['a']
			);
		}

		return $colors;
	}
}
