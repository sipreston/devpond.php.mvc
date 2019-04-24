<?php

spl_autoload_register(function ($className)
{
    $mappedPath = getMappedPath($className);
    if(!includeFile($mappedPath))
    {
        legacyIncludeFile($className);
    }
});

function includeFile($file)
{
    $docRoot = getDocumentRoot();
    if(file_exists( $docRoot . $file))
    {
        include($docRoot . $file);
        return true;
    }
    foreach(getLibPaths() as $folder) {
        if (file_exists($docRoot . $folder . $file)) {
            include($docRoot . $folder . $file);
            return true;
        }
    }
    return false;
}

function getMappedPath($className)
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

function getLibPaths()
{
    return array(
        '/libs',
        '/classes',
        '/external'
    );
}

function getDocumentRoot()
{
    if(!empty($_SERVER['DOCUMENT_ROOT']))
    {
        return $_SERVER['DOCUMENT_ROOT'];
    }
    return __DIR__; // this won't work if the autoloader is not in root. Is there a foolproof method?
}

function legacyIncludeFile($className)
{
    $docRoot = getDocumentRoot();
    $className = getFileName($className);
    if ($className == 'Startup') {
        require  $docRoot . '/Startup.php';
    } elseif (file_exists( $docRoot . '/controllers/' . $className . '.php')) {
        includeFile( '/controllers/' . $className . '.php');
    } elseif (file_exists( $docRoot . '/models/' . $className . '.php')) {
        includeFile( '/models/' . $className . '.php');
    } elseif (file_exists( $docRoot . '/libs/' . $className . '.php')) {
        includeFile( '/libs/' . $className . '.php');
    } elseif (file_exists( $docRoot . '/classes/' . $className . '.php')) {
        includeFile( '/classes/' . $className . '.php');
    } elseif (file_exists( $docRoot . '/resolvers/' . $className . '.php')) {
        includeFile( '/resolvers/' . $className . '.php');
    }
}

function getFileName($className)
{
    $parts = explode('\\', $className);
    $fileName = end($parts);
    return $fileName;
}