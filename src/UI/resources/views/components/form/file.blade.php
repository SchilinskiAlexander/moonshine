@props([
    'files' => [],
    'download' => false,
    'removable' => true,
    'removableAttributes' => null,
    'hiddenAttributes' => null,
    'dropzoneAttributes' => null,
    'imageable' => true,
])
<div class="form-group form-group-dropzone">
    <x-moonshine::form.input
        type="file"
        {{ $attributes->merge(['class' => 'form-file-upload'])->except(['id'])}}
    />

    @if($files !== [])
        <div class="dropzone">
            <div {{ $dropzoneAttributes?->merge(['class' => 'dropzone-items']) ?? "class=dropzone-items" }}>
                @foreach($files as $index => $file)
                    <x-moonshine::form.file-item
                        :attributes="$attributes"
                        :itemAttributes="$file['attributes']?->merge(['data-id' => $index])"
                        :filename="$file['name']"
                        :raw="$file['raw_value']"
                        :file="$file['full_path']"
                        :download="$download"
                        :removable="$removable"
                        :removableAttributes="$removableAttributes"
                        :hiddenAttributes="$hiddenAttributes"
                        :imageable="$imageable"
                    />
                @endforeach
            </div>
        </div>
    @endif
</div>
