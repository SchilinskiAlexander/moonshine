@props([
    'components' => []
])
@foreach($components as $component)
    {!! $component->render() !!}
@endforeach
