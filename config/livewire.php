<?php

return [
    'temporary_file_upload' => [
        'disk' => 'local',        // Usar el disco local (storage/app/private)
        'directory' => 'livewire-tmp',
        'middleware' => 'throttle:60,1',
        'preview_mimes' => [
            'png', 'gif', 'bmp', 'svg', 'wav', 'mp4',
            'mov', 'avi', 'wmv', 'mp3', 'm4a',
            'jpg', 'jpeg', 'mpga', 'webp',
        ],
        'check_mime_types' => true,
    ],
];
