<?php

/**
 * @copyright Frederic G. Østby
 * @license   http://www.makoframework.com/license
 */

namespace mako\pixel\image\vips;

use Jcupitt\Vips\Access;

/**
 * Libvips access modes.
 */
enum AccessMode: string
{
	case Random = Access::RANDOM;
	case Sequential = Access::SEQUENTIAL;
	case SequentialUnbuffered = Access::SEQUENTIAL_UNBUFFERED;
}
