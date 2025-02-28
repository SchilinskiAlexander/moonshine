<?php

declare(strict_types=1);

namespace MoonShine\Support\DTOs\Select;

use Closure;
use Illuminate\Contracts\Support\Arrayable;
use Illuminate\Support\Collection;
use JsonException;
use UnitEnum;

final readonly class Options implements Arrayable
{
    /**
     * @param  array<int|string,string|Option|OptionGroup|array<int|string,string>>  $values
     * @param  mixed|null  $value
     * @param  array<OptionProperty>|Closure  $properties
     */
    public function __construct(
        private array $values = [],
        private mixed $value = null,
        private array|Closure $properties = []
    ) {
    }

    public function getValues(): Collection
    {
        return collect($this->values)
            ->map(function (array|string|OptionGroup|Option $labelOrValues, int|string $valueOrLabel): OptionGroup|Option {
                if ($labelOrValues instanceof Option) {
                    return $labelOrValues;
                }

                $toOption = fn (string $label, string $value): Option => new Option(
                    label: $label,
                    value: $value,
                    selected: $this->isSelected($value),
                    properties: $this->getProperties($value),
                );

                if ($labelOrValues instanceof OptionGroup) {
                    return $labelOrValues;
                }

                if (\is_array($labelOrValues)) {
                    $options = [];

                    foreach ($labelOrValues as $value => $label) {
                        $options[] = $toOption($label, (string) $value);
                    }

                    return new OptionGroup(
                        label: $valueOrLabel,
                        values: new Options($options)
                    );
                }

                return $toOption($labelOrValues, (string) $valueOrLabel);
            });
    }

    public function getValue(): mixed
    {
        return $this->value;
    }

    public function getProperties(string $value): OptionProperty
    {
        $properties = data_get(value($this->properties), $value, []);

        if ($properties instanceof OptionProperty) {
            return $properties;
        }

        return new OptionProperty(...$properties ?? []);
    }

    /**
     * @throws JsonException
     */
    public function isSelected(string $value): bool
    {
        $current = $this->getValue();

        if ($current instanceof UnitEnum) {
            $current = $current->value ?? $current->name ?? null;
        }

        if (\is_string($current) && str($current)->isJson()) {
            $current = json_decode(
                $current,
                true,
                512,
                JSON_THROW_ON_ERROR
            );
        }

        if (is_iterable($current)) {
            return match (true) {
                $current instanceof Collection => $current->contains(
                    $value
                ),
                default => \in_array($value, (array) $current),
            };
        }

        return (string) $current === $value;
    }

    public function flatten(): array
    {
        return $this->getValues()
            ->mapWithKeys(static fn (OptionGroup|Option $optionOrGroup): array => $optionOrGroup instanceof OptionGroup
                ? $optionOrGroup->getValues()->flatten() :
                [$optionOrGroup->getValue() => $optionOrGroup])
            ->toArray();
    }

    public function toArray(): array
    {
        return $this->getValues()->toArray();
    }

    /**
     * @return array{options: array, properties: array}
     */
    public function toRaw(): array
    {
        $values = $this->getValues();

        $options = $values->mapWithKeys(function (Option|OptionGroup $option): array {
            if ($option instanceof OptionGroup) {
                return [$option->getLabel() => collect($option->getValues()->toArray())->pluck('label', 'value')->toArray()];
            }

            return [$option->getValue() => $option->getLabel()];
        })->toArray();

        $properties = collect($this->flatten())->pluck('properties', 'value')->toArray();

        return [
            'options' => $options,
            'properties' => $properties,
        ];
    }
}
