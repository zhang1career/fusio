<?php

declare(strict_types=1);

namespace App\Model;

use PSX\Record\Record;
use PSX\Schema\Attribute\Description;

/**
 * @extends Record<mixed>
 */
#[Description('Opaque raw HTTP body (string); use operation incoming Rawthru with octet-stream or image/*')]
class Rawthru extends Record
{
}
