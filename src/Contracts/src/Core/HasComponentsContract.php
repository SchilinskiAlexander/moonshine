<?php

declare(strict_types=1);

namespace MoonShine\Contracts\Core;

use MoonShine\Contracts\UI\Collection\ComponentsContract;

interface HasComponentsContract
{
    public function setComponents(iterable $components): static;

    public function hasComponents(): bool;

    public function getComponents(): ComponentsContract;
}
