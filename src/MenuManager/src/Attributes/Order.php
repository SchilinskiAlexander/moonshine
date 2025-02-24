<?php

declare(strict_types=1);

namespace MoonShine\MenuManager\Attributes;

use Attribute;

#[Attribute(Attribute::TARGET_CLASS)]
final readonly class Order
{
    public function __construct(
        public int $value
    ) {
    }
}
