<?php

/**
 * @copyright Frederic G. Østby
 * @license   http://www.makoframework.com/license
 */

namespace mako\tests\unit\pixel\image\operations\vips;

use mako\pixel\image\inspectors\vips\TopColors;
use mako\pixel\image\operations\vips\Bitonal;
use mako\pixel\image\operations\vips\Invert;
use mako\pixel\image\Vips;
use mako\tests\TestCase;
use PHPUnit\Framework\Attributes\Group;
use PHPUnit\Framework\Attributes\RequiresPhpExtension;

#[Group('unit')]
#[RequiresPhpExtension('ffi')]
class InvertTest extends TestCase
{
	/**
	 *
	 */
	public function testNegate(): void
	{
		$image = new Vips(__DIR__ . '/../../fixtures/002.jpg');

		$image->apply(new Bitonal);
		$image->apply(new Invert);

		$colors = $image->inspect(new TopColors);

		$this->assertSame(2, count($colors));

		$this->assertSame('#000000', $colors[0]->toHexString());
		$this->assertSame('#FFFFFF', $colors[1]->toHexString());
	}
}
