<x-dynamic-component
    :component="$getFieldWrapperView()"
    :field="$field"
>
    @php
        $jsId = str_replace(['.', '[', ']','-'], '_', $getId());
    @endphp
    <div id="file-preview-{{ $getId() }}" style="margin-bottom:1rem;width:100%"></div>
    <button id="browse-btn-{{ $getId() }}" type="button" class="drag-drop-zone w-full"
            style="width:100%;display:flex;align-items:center;justify-content:center;padding:2rem;font-size:1.2rem;"
            onclick="window.openFileManagerPicker_{{ str_replace(['.', '[', ']'], '_', $getId()) }}()"
            @if($isDisabled()) disabled style="opacity:0.7;cursor:not-allowed;" @endif
    >Browse
    </button>
    <input
        type="text"
        readonly
        id="{{ $getId() }}"
        name="{{ $getName() }}"
    {{ $applyStateBindingModifiers('wire:model') }}="{{ $getStatePath() }}"
    {{ $attributes->merge(['class' => 'filament-input w-full', 'style' => 'display:none;']) }}
    data-return="{{ $attributes->get('data-return') ?? 'path' }}"
    value="{{ $getState() }}"
    @if($isDisabled())
        disabled
    @endif
    >

</x-dynamic-component>

@push('styles')
    <style>
        .drag-drop-zone {
            border: 2px dashed #e5e7eb;
            border-radius: 0.5rem;
            background: #fafafa;
            transition: border-color 0.2s, background 0.2s;
            min-height: 80px;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 2rem;
            cursor: pointer;
            margin-bottom: 0.5rem;
        }

        .drag-drop-zone:hover, .drag-drop-zone.border-primary-500 {
            border-color: #ff9800;
            background: #fff7e6;
        }

        .browse-link {
            color: #ff9800;
            font-weight: 600;
            text-decoration: underline;
        }

        .browse-btn {
            display: inline-block;
            background: #ff9800;
            color: #fff;
            font-weight: 600;
            border: none;
            border-radius: 0.5rem;
            padding: 0.7rem 2.2rem;
            cursor: pointer;
            font-size: 1rem;
            transition: background 0.2s;
            margin-bottom: 0.5rem;
        }

        .browse-btn:hover {
            background: #fb8c00;
        }
    </style>
@endpush

@push('scripts')
    <script>
        function base64url(str) {
            return btoa(unescape(encodeURIComponent(str))).replace(/\+/g, '-').replace(/\//g, '_').replace(/=+$/, '');
        }

        function windowOpenFileManagerModal_{{ $jsId }}(onSelect) {
            const url = `{{ route("filament-filemanager.file-manager") }}`;
            const win = window.open(url, 'FileManager', 'width=900,height=600');
            window.__fileManagerSelectCallback = (value) => {
                onSelect(value);
                win.close();
            };
        }

        function showFilePreview_{{ $jsId }}(fileValue) {
            var previewEl = document.getElementById('file-preview-{{ $getId() }}');
            var browseBtn = document.getElementById('browse-btn-{{ $getId() }}');
            if (!fileValue) {
                previewEl.innerHTML = '';
                if (browseBtn) browseBtn.style.display = '';
                return;
            }
            var url = '/filament-filemanager/file-preview/' + base64url(fileValue);
            var fileName = fileValue.split('/').pop();
            var ext = fileValue.split('.').pop().toLowerCase();
            var imgExts = ['jpg', 'jpeg', 'png', 'gif', 'webp', 'bmp', 'svg'];
            var videoExts = ['mp4', 'webm', 'ogg', 'mov'];
            var audioExts = ['mp3', 'wav', 'ogg', 'aac'];
            var previewContent = '';
            var fileSize = '';
            if (window.__lastFileManagerPayload && window.__lastFileManagerPayload.size) {
                var size = window.__lastFileManagerPayload.size;
                if (size > 1024 * 1024) fileSize = (size / 1024 / 1024).toFixed(1) + ' MB';
                else if (size > 1024) fileSize = (size / 1024).toFixed(1) + ' KB';
                else fileSize = size + ' B';
            }
            var removeBtn = '<button type="button" onclick="removeFilePreview_{{ $jsId }}()" style="background:none;border:none;/*position:absolute;*/top:0;right:14px;font-size:22px;color:#fff;cursor:pointer;">&times;</button>';
            var infoBar = '<div style="background:linear-gradient(90deg,#1fa463,#1fa463 60%,#222 100%);color:#fff;padding:8px 16px 4px 8px;border-top-left-radius:14px;border-top-right-radius:14px;display:flex;align-items:center;justify-content:space-between;position:relative;">'
                + '<div style="font-size:15px;font-weight:600;overflow:hidden;text-overflow:ellipsis;white-space:nowrap;max-width:60%;">' + fileName + '</div>'
                + '<div style="font-size:13px;font-weight:400;opacity:0.9;">' + (fileSize ? fileSize : '') + '</div>'
                + removeBtn
                + '</div>';
            var body = '';
            if (imgExts.includes(ext)) {
                body = '<img src="' + url + '" style="display:block;margin:0 auto;max-width:100%;max-height:210px;">';
            } else if (videoExts.includes(ext)) {
                body = '<video controls style="display:block;margin:0 auto;max-width:100%;max-height:210px;"><source src="' + url + '" type="video/' + ext + '"/></video>';
            } else if (audioExts.includes(ext)) {
                body = '<audio controls style="width:100%"><source src="' + url + '" type="audio/' + ext + '"/></audio>';
            } else {
                body = '<div style="padding:2rem;text-align:center;color:#888;">Previewing this file is not supported.</div>';
            }
            previewContent = '<div style="background:#222;border-radius:16px;box-shadow:0 4px 16px #0002;overflow:hidden;max-width:100%;position:relative;">' + infoBar + '<div style="padding:16px;">' + body + '</div></div>';
            previewEl.innerHTML = previewContent;
            if (browseBtn) browseBtn.style.display = 'none';
        }

        function removeFilePreview_{{ $jsId }}() {
            var inputEl = document.getElementById('{{ $getId() }}');
            inputEl.value = '';
            inputEl.dispatchEvent(new Event('input', {bubbles: true}));
            inputEl.dispatchEvent(new Event('change', {bubbles: true}));
            document.getElementById('file-preview-{{ $getId() }}').innerHTML = '';
            var browseBtn = document.getElementById('browse-btn-{{ $getId() }}');
            if (browseBtn) browseBtn.style.display = '';
        }

        window.openFileManagerPicker_{{ $jsId }} = function () {
            windowOpenFileManagerModal_{{ $jsId }}(function (selected) {
                var inputEl = document.getElementById('{{ $getId() }}');
                inputEl.value = selected.path || selected;
                inputEl.dispatchEvent(new Event('input', {bubbles: true}));
                inputEl.dispatchEvent(new Event('change', {bubbles: true}));
                showFilePreview_{{ $jsId }}(selected.path || selected);
                window.__lastFileManagerPayload = selected;
            });
        };

        document.addEventListener('DOMContentLoaded', function () {
            var inputEl = document.getElementById('{{ $getId() }}');
            if (inputEl && inputEl.value) {
                showFilePreview_{{ $jsId }}(inputEl.value);
            }
        });

        document.addEventListener("livewire:initialized", function () {
            if (window.Livewire) {
                Livewire.hook('commit', ({component, succeed}) => {
                    succeed(() => {
                        setTimeout(() => {
                            var inputEl = document.getElementById('{{ $getId() }}');
                            if (inputEl && inputEl.value) {
                                showFilePreview_{{ $jsId }}(inputEl.value);
                            }
                        }, 0);
                    });
                });
            }
        });
    </script>
@endpush
