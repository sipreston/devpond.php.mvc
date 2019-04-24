<?php

spl_autoload_register(function ($className)
{
    $mappedPath = getPath($className);
    includeFiles($mappedPath);
});

function includeFiles($file)
{
    $docRoot = getLibraryRoot();
    if(file_exists( $docRoot . $file))
    {
        include($docRoot . $file);
        return true;
    }
    foreach(getPackagePaths() as $folder) {
        if (file_exists($docRoot . $folder . $file)) {
            include($docRoot . $folder . $file);
            return true;
        }
    }
    return false;
}

function getPath($className)
{
    $ext = '.php';
    $parts = explode('\\', $className);
    if(!is_array($parts))
    {
        return $parts . $ext;
    }
    $mappedPath = '';
    for($i = 0; $i < count($parts); $i++)
    {
        $mappedPath .= '/' . $parts[$i];
    }
    $mappedPath .= $ext;
    return $mappedPath;
}

function getPackagePaths()
{
    return array(
        '/VisionDatabase',
        '/VisionFramework',
        '/VisionModel',
        '/VisionModel/Model'
    );
}

function getLibraryRoot()
{
    return __DIR__;
}
