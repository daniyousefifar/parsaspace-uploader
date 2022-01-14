<?php

/**
 * Require Composer AutoLoad
 */
require __DIR__ . '../vendor/autoload.php';

/**
 * Require ParsaSpace SDK Class
 */
require_once 'class-parsaspace-sdk.php';

/**
 * Create a new instance from ParsaSpace class
 */
$class = new ParsaSpace_SDK();

/**
 * File Path
 */
$file_path = '/path/your/file.jpg';

/**
 * Upload a file
 */
$uploader = $class->uploader($file_path);

/**
 * Echo download link link
 */
echo $uploader->link;