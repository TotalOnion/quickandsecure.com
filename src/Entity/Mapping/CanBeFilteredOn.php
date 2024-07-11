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
    const ACCEPTS_AN_ARRAY        = true;
    const DOES_NOT_ACCEPT_AN_ARRY = false;

    public function __construct( private bool $acceptsAnArray = false ) { }

    public function canAcceptAnArray():bool
    {
        return $this->acceptsAnArray;
    }
}
