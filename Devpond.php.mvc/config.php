<?php

//$webdir = $_SERVER['web_dir'];
//$webdir = $_SERVER['DOCUMENT_ROOT'];
//$root = (!empty($_SERVER['HTTPS']) ? 'https' : 'http') . '://' . $_SERVER['HTTP_HOST'] . '/';
//$root = $_SERVER['DOCUMENT_ROOT'];

//define("SITE_ROOT", $root);
//define("SITE_INCLUDES", $root . '/libs');
//define("VIEW_PARTIALS", $root . '/views/partials');

// Set the SQL providers
$con = array(
    'DATABASE_PROVIDER' => 'MSSQLOCALDB',
    'SQL_PLUGIN' => 'MsSql.php'
);
//define($GLOBALS["CONFIG"]["DATABASE_PROVIDER"], "MSSQLLOCALDB");
//define($GLOBALS["CONFIG"]["SQL_PLUGIN"], "MsSql.php");

//define($GLOBALS["CONFIG"]["DATABASE_PROVIDER"], "MSSQL");
//define($GLOBALS["CONFIG"]["SQL_PLUGIN"], "MsSql.php");

//define($GLOBALS["CONFIG"]["DATABASE_PROVIDER"], 'MYSQL');
//define($GLOBALS["CONFIG"]["SQL_PLUGIN"], "MySql.php");

//define($GLOBALS["CONFIG"]["DATABASE_PROVIDER"], 'POSTGRESQL');
//define($GLOBALS["CONFIG"]["SQL_PLUGIN"], "PostGreSql.php");

//define($GLOBALS["CONFIG"]["DATABASE_PROVIDER"], 'ORACLE');
//define($GLOBALS["CONFIG"]["SQL_PLUGIN"], "OracleSql.php");