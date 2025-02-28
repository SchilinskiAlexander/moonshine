<?php

declare(strict_types=1);

namespace MoonShine\Laravel\Buttons;

use MoonShine\Contracts\UI\ActionButtonContract;
use MoonShine\Contracts\UI\FormBuilderContract;
use MoonShine\Laravel\Enums\Ability;
use MoonShine\Laravel\Enums\Action;
use MoonShine\Laravel\Resources\CrudResource;
use MoonShine\Support\Enums\HttpMethod;
use MoonShine\UI\Components\ActionButton;

final class MassDeleteButton
{
    public static function for(
        CrudResource $resource,
        ?string $componentName = null,
        ?string $redirectAfterDelete = null,
        bool $isAsync = true,
        string $modalName = 'resource-mass-delete-modal',
    ): ActionButtonContract {
        $action = static fn (): string => $resource->getRoute('crud.massDelete', query: [
            ...$redirectAfterDelete
                ? ['_redirect' => $redirectAfterDelete]
                : [],
        ]);

        return ActionButton::make(
            '',
            url: $action
        )
            ->name('mass-delete-button')
            ->bulk($componentName ?? $resource->getListComponentName())
            ->withConfirm(
                method: HttpMethod::DELETE,
                formBuilder: static fn (FormBuilderContract $formBuilder): FormBuilderContract => $formBuilder->when(
                    $isAsync || $resource->isAsync(),
                    static fn (FormBuilderContract $form): FormBuilderContract => $form->async(
                        events: $resource->getListEventName(
                            $componentName ?? $resource->getListComponentName(),
                            $isAsync ? array_filter([
                                'page' => request()->getScalar('page'),
                                'sort' => request()->getScalar('sort'),
                            ]) : []
                        )
                    )
                ),
                name: $modalName
            )
            ->canSee(
                static fn (): bool => $resource->hasAction(Action::MASS_DELETE)
                  && $resource->can(Ability::MASS_DELETE)
            )
            ->error()
            ->icon('trash')
            ->showInLine();
    }
}
