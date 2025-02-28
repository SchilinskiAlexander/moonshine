@props([
    'route',
    'logOutRoute',
    'avatar',
    'name' => '',
    'username' => '',
    'withBorder' => false,
    'before',
    'after',
])
@if($withBorder) <div {{ $attributes->merge(['class' => 'mt-2 border-t border-dark-200']) }}> @endif
    {{ $before ?? '' }}

    @if(isset($slot) && $slot->isNotEmpty())
        {{ $slot }}
    @else
        <div class="profile">
            <a href="{{ $route }}"
               class="profile-main"
            >
                @if($avatar)
                    <div class="profile-photo">
                        <img class="h-full w-full object-cover"
                             src="{{ $avatar }}"
                             alt="{{ $nameOfUser }}"
                        />
                    </div>
                @endif

                <div class="profile-info">
                    <h5 class="name">{{ $nameOfUser }}</h5>
                    <div class="email">{{ $username }}</div>
                </div>
            </a>

            @if($logOutRoute)
                <a href="{{ $logOutRoute }}"
                   class="profile-exit"
                   title="Logout"
                >
                    <x-moonshine::icon
                        icon="power"
                        color="gray"
                        size="6"
                    />
                </a>
            @endif
        </div>
    @endif

    {{ $after ?? '' }}
@if($withBorder) </div> @endif
