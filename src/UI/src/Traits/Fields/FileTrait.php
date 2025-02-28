<?php

declare(strict_types=1);

namespace MoonShine\UI\Traits\Fields;

use Closure;
use Illuminate\Support\Collection;
use MoonShine\Contracts\UI\ComponentAttributesBagContract;
use MoonShine\Support\Components\MoonShineComponentAttributeBag;
use MoonShine\Support\DTOs\FileItemExtra;
use MoonShine\UI\Contracts\FileableContract;
use MoonShine\UI\Traits\WithStorage;

trait FileTrait
{
    use WithStorage;

    protected array $allowedExtensions = [];

    protected bool $disableDownload = false;

    protected bool $keepOriginalFileName = false;

    /** @var null|Closure(mixed, static): string */
    protected ?Closure $customName = null;

    /** @var null|Closure(string, int): string */
    protected ?Closure $names = null;

    /** @var null|Closure(string, int): array */
    protected ?Closure $itemAttributes = null;

    /** @var null|Closure(string, int): ?FileItemExtra */
    protected ?Closure $extraAttributes = null;

    /** @var null|Closure(static): array */
    protected ?Closure $dropzoneAttributes = null;

    /** @var null|Closure(static): Collection */
    protected ?Closure $remainingValuesResolver = null;

    protected ?Collection $remainingValues = null;

    public function dropzoneAttributes(Closure $attributes): static
    {
        $this->dropzoneAttributes = $attributes;

        return $this;
    }

    public function getDropzoneAttributes(): ComponentAttributesBagContract
    {
        $attributes = new MoonShineComponentAttributeBag(
            $this->dropzoneAttributes === null ? [] : \call_user_func($this->dropzoneAttributes, $this),
        );

        if (! $attributes->has('x-data')) {
            $attributes = $attributes->merge([
                'x-data' => 'sortable',
                'data-handle' => '.dropzone-item',
            ]);
        }

        return $attributes;
    }

    /**
     * @param  string|Closure(static): string  $url
     */
    public function reorderable(string|Closure $url, ?string $group = null): static
    {
        return $this->dropzoneAttributes(static function (FileableContract $ctx) use ($url, $group): array {
            $url = value($url, $ctx);

            return [
                'x-data' => "sortable(`$url`, `$group`)",
                'data-handle' => '.dropzone-item',
            ];
        });
    }

    /**
     * @param  Closure(string $filename, int $index): string  $callback
     */
    public function names(Closure $callback): static
    {
        $this->names = $callback;

        return $this;
    }

    /** @return Closure(string, int, static): string */
    public function resolveNames(): Closure
    {
        return function (string $filename, int $index = 0): string {
            if (\is_null($this->names)) {
                return $filename;
            }

            return \call_user_func($this->names, $filename, $index);
        };
    }

    /**
     * @param  Closure(string $filename, int $index): array  $callback
     */
    public function itemAttributes(Closure $callback): static
    {
        $this->itemAttributes = $callback;

        return $this;
    }

    /**
     * @return Closure(string $filename, int $index, static): ComponentAttributesBagContract
     */
    public function resolveItemAttributes(): Closure
    {
        return function (string $filename, int $index = 0): ComponentAttributesBagContract {
            if (\is_null($this->itemAttributes)) {
                return new MoonShineComponentAttributeBag();
            }

            return new MoonShineComponentAttributeBag(
                (array) \call_user_func($this->itemAttributes, $filename, $index),
            );
        };
    }

    /**
     * @param  Closure(string $filename, int $index): ?FileItemExtra  $callback
     */
    public function extraAttributes(Closure $callback): static
    {
        $this->extraAttributes = $callback;

        return $this;
    }

    /**
     * @return Closure(string $filename, int $index, static): ?FileItemExtra
     */
    public function resolveExtraAttributes(): Closure
    {
        return function (string $filename, int $index = 0): ?FileItemExtra {
            if (\is_null($this->extraAttributes)) {
                return null;
            }

            return \call_user_func($this->extraAttributes, $filename, $index);
        };
    }

    public function keepOriginalFileName(): static
    {
        $this->keepOriginalFileName = true;

        return $this;
    }

    public function isKeepOriginalFileName(): bool
    {
        return $this->keepOriginalFileName;
    }

    /**
     * @param  Closure(mixed $file, static $ctx): string  $name
     */
    public function customName(Closure $name): static
    {
        $this->customName = $name;

        return $this;
    }

    /**
     * @return null|Closure(mixed $file, static $ctx): string
     */
    public function getCustomName(): ?Closure
    {
        return $this->customName;
    }

    public function allowedExtensions(array $allowedExtensions): static
    {
        $this->allowedExtensions = $allowedExtensions;

        if ($allowedExtensions !== []) {
            $this->setAttribute('accept', $this->getAcceptExtension());
        }

        return $this;
    }

    public function getAcceptExtension(): string
    {
        $extensions = array_map(
            static fn ($val): string => '.' . $val,
            $this->allowedExtensions,
        );

        return implode(',', $extensions);
    }

    public function disableDownload(Closure|bool|null $condition = null): static
    {
        $this->disableDownload = value($condition, $this) ?? true;

        return $this;
    }

    public function canDownload(): bool
    {
        return ! $this->disableDownload;
    }

    public function getPathWithDir(string $value): string
    {
        return $this->getPath($this->getPrependedDir($value));
    }

    public function getPath(string $value): string
    {
        return $this->getStorageUrl($value);
    }

    public function getPrependedDir(string $value): string
    {
        $dir = empty($this->getDir()) ? '' : $this->getDir() . '/';

        return str($value)->remove($dir)
            ->prepend($dir)
            ->value();
    }

    public function getHiddenRemainingValuesKey(): string
    {
        $column = str($this->getColumn())->explode('.')->last();
        $hiddenColumn = str($this->getVirtualColumn())->explode('.')->last();

        return str($this->getRequestNameDot())
            ->replaceLast($column, "hidden_$hiddenColumn")
            ->value();
    }

    public function getHiddenRemainingValuesName(): string
    {
        $column = str($this->getColumn())->explode('.')->last();
        $hiddenColumn = str($this->getVirtualColumn())->explode('.')->last();

        return str($this->getNameAttribute())
            ->replaceLast($column, "hidden_$hiddenColumn")
            ->value();
    }

    public function getHiddenAttributes(): ComponentAttributesBagContract
    {
        return $this->getAttributes()->only(['data-level'])->merge([
            'name' => $this->getHiddenRemainingValuesName(),
            'data-name' => $this->getHiddenRemainingValuesName(),
        ]);
    }

    /**
     * @param  Closure(static $ctx): Collection  $callback
     */
    public function remainingValuesResolver(Closure $callback): static
    {
        $this->remainingValuesResolver = $callback;

        return $this;
    }

    public function setRemainingValues(iterable $values): void
    {
        $this->remainingValues = collect($values);
    }

    public function getRemainingValues(): Collection
    {
        if (! \is_null($this->remainingValues)) {
            $values = $this->remainingValues;

            $this->remainingValues = null;

            return $values;
        }


        if (! \is_null($this->remainingValuesResolver)) {
            return \call_user_func($this->remainingValuesResolver, $this);
        }

        return collect(
            $this->getCore()->getRequest()->get(
                $this->getHiddenRemainingValuesKey(),
            ),
        );
    }

    public function isAllowedExtension(string $extension): bool
    {
        return empty($this->getAllowedExtensions())
            || \in_array($extension, $this->getAllowedExtensions(), true);
    }

    public function getAllowedExtensions(): array
    {
        return $this->allowedExtensions;
    }

    protected function resolveValue(): mixed
    {
        if ($this->isMultiple() && ! $this->toValue(false) instanceof Collection) {
            return collect($this->toValue(false));
        }

        return parent::resolveValue();
    }

    public function getFullPathValues(): array
    {
        $values = $this->toFormattedValue();

        if (! $values) {
            return [];
        }

        return $this->isMultiple()
            ? collect($values)
                ->map(fn ($value): string => $this->getPathWithDir($value))
                ->toArray()
            : [$this->getPathWithDir($values)];
    }

    public function removeExcludedFiles(): void
    {
        $values = collect(
            $this->toValue(withDefault: false),
        );

        $values->diff($this->getRemainingValues())->each(fn (?string $file) => $file !== null ? $this->deleteFile($file) : null);
    }
}
