<?php

return [

    /**
     * All assets from these containers will be left alone.
     * You can see all your asset containers under content/assets folder.
     */
    'ignore_containers' => [
        'favicons',
        'social_images',
    ],


    /**
     * If we come across these filenames, we'll just leave them alone.
     */
    'ignore_assets' => [
        // 'IMAGE1.jpg'
    ],

    /**
     * Where should we look to see if assets are still referenced or not?
     * If you're referencing assets elsewhere in your codebase, add those paths here.
     *
     * All paths are relative to the base_path()
     */
    'scan_folders' => [
        'content',
        // 'resources/views'
    ],
];
