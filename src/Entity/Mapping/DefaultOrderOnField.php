<?php

declare(strict_types=1);

namespace App\Entity\Mapping;

use Attribute;

/**
 * @Annotation
 * @Target("PROPERTY")
 */
#[Attribute(Attribute::TARGET_PROPERTY)]
final class DefaultOrderOnField
{
    const ORDER_ASC = 'ASC';
    const ORDER_DESC = 'DESC';
    const VALID_ORDER_DIRECTIONS = [
        self::ORDER_ASC,
        self::ORDER_DESC
    ];
    
    const ORDER_DIRECTION_WHEN_NONE_IS_SET = 'ASC';

    public function __construct( private ?string $defaultOrderDirection ) { }

    public function getDefaultOrderDirection():string
    {
        return $this->defaultOrderDirection ?? self::ORDER_DIRECTION_WHEN_NONE_IS_SET;
    }
}
