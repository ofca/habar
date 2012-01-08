<?php

// Load class loader
if ( ! class_exists('Symfony\Component\ClassLoader\UniversalClassLoader'))
{
	require_once HABAR_DIR.'../Symfony/src/Symfony/Component/ClassLoader/UniversalClassLoader.php';
}

use Symfony\Component\ClassLoader\UniversalClassLoader;

$loader = new UniversalClassLoader();

// Register namespaces
$loader->registerNamespace('Habar', HABAR_DIR.'/src');
$loader->registerNamespace('Kohana', HABAR_DIR.'/../Kohana/src');
$loader->registerNamespace('Symfony', HABAR_DIR.'/../Symfony/src');

$loader->register();