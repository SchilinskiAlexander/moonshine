@props([
    'name' => 'default',
    'open' => $isOpen ?? false,
    'left' => $isLeft ?? false,
    'wide' => $isWide ?? false,
    'full' => $isFull ?? false,
    'title' => '',
    'async' => false,
    'asyncUrl' => '',
    'toggler' => null,
])
<div x-data="offCanvas(
    `{{ $open }}`,
    `{{ $async ? str_replace('&amp;', '&', $asyncUrl) : ''}}`
)"
    {{ $attributes }}
>
    @if($toggler?->isNotEmpty())
        <x-moonshine::link-button
            :attributes="$toggler->attributes?->merge([
                '@click.prevent' => 'toggleCanvas',
            ])"
        >
            {{ $toggler ?? '' }}
        </x-moonshine::link-button>
    @endif

    <template x-teleport="body">
        <div class="offcanvas-template"
             @defineEvent('off_canvas_toggled', $name, 'toggleCanvas')
        >
            <div
                x-show="open"
                x-bind="dismissCanvas"
                @if($left)
                    x-transition:enter="transition ease-out duration-300"
                    x-transition:enter-start="opacity-0 -translate-x-full"
                    x-transition:enter-end="opacity-100 translate-x-0"
                    x-transition:leave="transition ease-in duration-150"
                    x-transition:leave-start="opacity-100 translate-x-0"
                    x-transition:leave-end="opacity-0 -translate-x-full"
                @else
                    x-transition:enter="transition ease-out duration-300"
                    x-transition:enter-start="opacity-0 translate-x-full"
                    x-transition:enter-end="opacity-100 translate-x-0"
                    x-transition:leave="transition ease-in duration-150"
                    x-transition:leave-start="opacity-100 translate-x-0"
                    x-transition:leave-end="opacity-0 translate-x-full"
                @endif
                @class([
                    'offcanvas',
                    'offcanvas-left' => $left,
                    'offcanvas-right' => !$left,
                    'w-full max-w-none' => $full,
                    'w-4/5 max-w-none' => $wide && !$full,
                ])
                aria-modal="true"
                role="dialog"
            >
                <div class="offcanvas-header">
                    <h5 class="offcanvas-title">{{ $title }}</h5>
                    <button type="button" class="btn btn-close" @click.prevent="toggleCanvas" aria-label="Close">
                        <x-moonshine::icon icon="x-mark" size="6" />
                    </button>
                </div>
                <div class="offcanvas-body">
                    @if($async)
                        <div :id="id">
                            <x-moonshine::loader />
                        </div>
                    @endif

                    {{ $slot ?? '' }}
                </div>
            </div>
            <div x-show="open" x-transition.opacity class="offcanvas-backdrop"></div>
        </div>
    </template>
</div>
