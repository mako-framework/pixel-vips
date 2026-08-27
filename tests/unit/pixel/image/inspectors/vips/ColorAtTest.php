<?php

/**
 * @copyright Frederic G. Østby
 * @license   http://www.makoframework.com/license
 */

namespace mako\tests\unit\pixel\image\inspectors\vips;

use mako\pixel\image\Color;
use mako\pixel\image\exceptions\ImageException;
use mako\pixel\image\geometry\Dimensions;
use mako\pixel\image\geometry\Point;
use mako\pixel\image\inspectors\vips\ColorAt;
use mako\pixel\image\Vips;
use mako\tests\TestCase;
use PHPUnit\Framework\Attributes\Group;
use PHPUnit\Framework\Attributes\RequiresPhpExtension;

#[Group('unit')]
#[RequiresPhpExtension('ffi')]
class ColorAtTest extends TestCase
{
	/**
	 *
	 */
	public function testColorAt(): void
	{
		$image = new Vips(__DIR__ . '/../../fixtures/001.png');

		$color = $image->inspect(new ColorAt(new Point(0, 0)));

		$this->assertInstanceOf(Color::class, $color);

		$this->assertSame('#B51700', $color->toHexString());

		$color = $image->inspect(new ColorAt(new Point(0, 100)));

		$this->assertInstanceOf(Color::class, $color);

		$this->assertSame('#0376BB', $color->toHexString());

		$color = $image->inspect(new ColorAt(new Point(0, 275)));

		$this->assertInstanceOf(Color::class, $color);

		$this->assertSame('#047101', $color->toHexString());
	}

	/**
	 *
	 */
	public function testColorAtFromCreated(): void
	{
		$image = Vips::create(new Dimensions(1, 1));

		$color = $image->inspect(new ColorAt(new Point(0, 0)));

		$this->assertInstanceOf(Color::class, $color);

		$this->assertSame('#00000000', $color->toHexaString());

		//

		$image = Vips::create(new Dimensions(1, 1), new Color(0, 0, 0));

		$color = $image->inspect(new ColorAt(new Point(0, 0)));

		$this->assertInstanceOf(Color::class, $color);

		$this->assertSame('#000000FF', $color->toHexaString());
	}

	/**
	 *
	 */
	public function testColorAtWithInvalidPosition(): void
	{
		$this->expectException(ImageException::class);
		$this->expectExceptionMessageIs('Pixel coordinates [ 1000, 100 ] are outside image bounds [ 4 x 4 ].');

		$image = Vips::create(new Dimensions(4, 4));

		$image->inspect(new ColorAt(new Point(1000, 100)));
	}
}
