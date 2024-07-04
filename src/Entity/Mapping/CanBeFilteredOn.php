<?php

declare(strict_types=1);

namespace App\Entity\Mapping;

use Attribute;

/**
 * @Annotation
 * @Target("PROPERTY")
 */
#[Attribute(Attribute::TARGET_PROPERTY)]
final class CanBeFilteredOn
{
}
