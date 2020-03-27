<?php

// autoload_static.php @generated by Composer

namespace Composer\Autoload;

class ComposerStaticInit30145bc9408c5219f52e020f1c1b494a
{
    public static $files = array (
        '979dffec6fa5205cabd2c2cd1e9e6b3a' => __DIR__ . '/..' . '/algolia/algoliasearch-client-php/src/Http/Psr7/functions.php',
        '6783aef8c489bbc166eee2536fe605d5' => __DIR__ . '/..' . '/algolia/algoliasearch-client-php/src/functions.php',
    );

    public static $prefixLengthsPsr4 = array (
        'P' => 
        array (
            'Psr\\SimpleCache\\' => 16,
            'Psr\\Log\\' => 8,
            'Psr\\Http\\Message\\' => 17,
        ),
        'M' => 
        array (
            'Monolog\\' => 8,
        ),
        'C' => 
        array (
            'Cocur\\Slugify\\' => 14,
        ),
        'A' => 
        array (
            'Algolia\\AlgoliaSearch\\' => 22,
        ),
    );

    public static $prefixDirsPsr4 = array (
        'Psr\\SimpleCache\\' => 
        array (
            0 => __DIR__ . '/..' . '/psr/simple-cache/src',
        ),
        'Psr\\Log\\' => 
        array (
            0 => __DIR__ . '/..' . '/psr/log/Psr/Log',
        ),
        'Psr\\Http\\Message\\' => 
        array (
            0 => __DIR__ . '/..' . '/psr/http-message/src',
        ),
        'Monolog\\' => 
        array (
            0 => __DIR__ . '/..' . '/monolog/monolog/src/Monolog',
        ),
        'Cocur\\Slugify\\' => 
        array (
            0 => __DIR__ . '/..' . '/cocur/slugify/src',
        ),
        'Algolia\\AlgoliaSearch\\' => 
        array (
            0 => __DIR__ . '/..' . '/algolia/algoliasearch-client-php/src',
        ),
    );

    public static function getInitializer(ClassLoader $loader)
    {
        return \Closure::bind(function () use ($loader) {
            $loader->prefixLengthsPsr4 = ComposerStaticInit30145bc9408c5219f52e020f1c1b494a::$prefixLengthsPsr4;
            $loader->prefixDirsPsr4 = ComposerStaticInit30145bc9408c5219f52e020f1c1b494a::$prefixDirsPsr4;

        }, null, ClassLoader::class);
    }
}
