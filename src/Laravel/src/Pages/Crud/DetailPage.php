<?php

declare(strict_types=1);

namespace MoonShine\Laravel\Pages\Crud;

use MoonShine\Contracts\Core\TypeCasts\DataWrapperContract;
use MoonShine\Contracts\UI\ComponentContract;
use MoonShine\Core\Exceptions\PageException;
use MoonShine\Core\Exceptions\ResourceException;
use MoonShine\Laravel\Collections\Fields;
use MoonShine\Laravel\Components\Fragment;
use MoonShine\Laravel\Enums\Ability;
use MoonShine\Laravel\Enums\Action;
use MoonShine\Laravel\Fields\Relationships\ModelRelationField;
use MoonShine\Laravel\Resources\CrudResource;
use MoonShine\Support\Enums\PageType;
use MoonShine\UI\Components\ActionGroup;
use MoonShine\UI\Components\Heading;
use MoonShine\UI\Components\Layout\Box;
use MoonShine\UI\Components\Layout\LineBreak;
use MoonShine\UI\Components\Table\TableBuilder;
use MoonShine\UI\Exceptions\MoonShineComponentException;
use Throwable;

/**
 * @method CrudResource getResource()

 */
class DetailPage extends CrudPage
{
    protected ?PageType $pageType = PageType::DETAIL;

    public function getTitle(): string
    {
        return $this->title ?: __('moonshine::ui.show');
    }

    /**
     * @return array<string, string>
     */
    public function getBreadcrumbs(): array
    {
        if (! \is_null($this->breadcrumbs)) {
            return $this->breadcrumbs;
        }

        $breadcrumbs = parent::getBreadcrumbs();

        $breadcrumbs[$this->getRoute()] = data_get($this->getResource()->getItem(), $this->getResource()->getColumn());

        return $breadcrumbs;
    }

    /**
     * @throws ResourceException
     */
    protected function prepareBeforeRender(): void
    {
        abort_if(
            ! $this->getResource()->hasAction(Action::VIEW)
            || ! $this->getResource()->can(Ability::VIEW),
            403
        );

        parent::prepareBeforeRender();
    }

    /**
     * @return list<ComponentContract>
     * @throws Throwable
     */
    protected function components(): iterable
    {
        $this->validateResource();

        if (! $this->getResource()->isItemExists()) {
            oops404();
        }

        return $this->getLayers();
    }

    /**
     * @return list<ComponentContract>
     * @throws Throwable
     */
    protected function mainLayer(): array
    {
        $resource = $this->getResource();
        $item = $resource->getCastedData();

        return [
            Box::make([
                ...$this->getDetailComponents($item),
                LineBreak::make(),
                ...$this->getPageButtons(),
            ]),
        ];
    }

    /**
     * @return list<ComponentContract>
     * @throws Throwable
     */
    protected function bottomLayer(): array
    {
        $components = [];
        $item = $this->getResource()->getItem();

        if (! $this->getResource()->isItemExists()) {
            return $components;
        }

        $outsideFields = $this->getResource()->getDetailFields(onlyOutside: true);

        if ($outsideFields->isNotEmpty()) {
            $components[] = LineBreak::make();

            /** @var ModelRelationField $field */
            foreach ($outsideFields as $field) {
                $field->fillCast(
                    $item,
                    $field->getResource()?->getCaster()
                );

                $components[] = LineBreak::make();

                $blocks = [
                    Heading::make($field->getLabel()),
                    $field,
                ];

                if ($field->isToOne()) {
                    $field
                        ->withoutWrapper()
                        ->previewMode();

                    $blocks = [
                        Box::make($field->getLabel(), [$field]),
                    ];
                }

                $components[] = Fragment::make($blocks)
                    ->name($field->getRelationName());
            }
        }

        $components = array_merge($components, $this->getEmptyModals());

        return array_merge($components, $this->getResource()->getDetailPageComponents());
    }

    protected function getDetailComponent(?DataWrapperContract $item, Fields $fields): ComponentContract
    {
        return TableBuilder::make($fields)
            ->cast($this->getResource()->getCaster())
            ->items([$item])
            ->vertical(
                title: $this->getResource()->isDetailInModal() ? 3 : 2,
                value: $this->getResource()->isDetailInModal() ? 9 : 10,
            )
            ->simple()
            ->preview()
            ->class('table-divider');
    }

    /**
     * @return list<ComponentContract>
     * @throws MoonShineComponentException
     * @throws PageException
     * @throws Throwable
     */
    protected function getDetailComponents(?DataWrapperContract $item): array
    {
        return [
            Fragment::make([
                $this->getResource()->modifyDetailComponent(
                    $this->getDetailComponent($item, $this->getResource()->getDetailFields())
                ),
            ])->name('crud-detail'),
        ];
    }

    protected function getPageButtons(): array
    {
        return [
            ActionGroup::make(
                $this->getResource()->getDetailButtons()
            )
                ->fill($this->getResource()->getCastedData())
                ->class('justify-end'),
        ];
    }
}
