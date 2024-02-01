<?php

return [

    /**
     * Where should we look to see if assets are still referenced or not?
     * If you're referencing assets elsewhere in your codebase, add those paths here.
     * All paths are relative to the base_path()
     */
    'scan_folders' => [
        'content',
        'users',
    ],

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
     * You can use "*" as a wildcard. eg: "IMAGE*.jpg" will ignore IMAGE1.jpg IMAGE23.jpg etc...
     */
    'ignore_filenames' => [
        //
    ],

    /**
     * You might not want to delete very fresh assets, as perhaps you want to use them soon.
     * Use this config to only detect and delete files older than x days.
     */
    'minimum_age_in_days' => 0,
];
