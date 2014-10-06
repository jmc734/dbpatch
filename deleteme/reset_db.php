<?php
// if we weren't supplied a wrapper from an include, define our own path
// use this path everywhere
if (!defined('DBPATCH_BASE_PATH')) define('DBPATCH_BASE_PATH', dirname(__FILE__));

require_once("Console/Getopt.php");

// get overridden db class
require_once (DBPATCH_BASE_PATH  . "/db.php");

require_once(dirname(__FILE__) . "/app/PatchEngine.php");
require_once(dirname(__FILE__) . "/app/PatchFileBundler.php");
require_once (dirname(__FILE__) . '/trackers/TrackerInterface.php');
require_once (dirname(__FILE__) . '/trackers/XmlFileVersionTracker.php');
require_once (dirname(__FILE__) . '/trackers/FileTrackerFactory.php');

$masterConfig = new db();
$singleDbConfigs = $masterConfig->getSingleDbConfigs();

foreach ($singleDbConfigs as $db) {
    /* @var $db DbPatch_Config_SingleDb */
    if (isset($db->dbType)) {
        require_once(dirname(__FILE__) . '/database_drivers/' . $db->dbType . '_database.php');
    } else {
        require_once(dirname(__FILE__) . '/database_drivers/' . $db->dbClassFile);
    }
}

require_once(dirname(__FILE__) . "/printers/cli.php");

// set up the printer
$printLevel = 1; // used with quiet and verbose
$printer = new printer($printLevel);

foreach ($singleDbConfigs as $config) {
    // for MySQL, we need to use the MySQL client to avoid problems with DELIMITER, etc.
    $CreateHere = ($config->use_cli_client_for_reset && ($config->dbType == 'mysql'));

    $app = new Patch_Engine($config, $printer, DBPATCH_BASE_PATH, $CreateHere);
    // get db connection
    $db = $app->getDb();
    $db->clearError();

    // reset database
    echo "Dropping database '" . $config->dbName . "'" . PHP_EOL;
    $db->execute('DROP DATABASE IF EXISTS `' . $config->dbName . '`');
    if ($db->has_error()) die('error: ' . $db->getConnection()->error);

    if ($CreateHere) {
      echo "Creating database '" . $config->dbName . "'" . PHP_EOL;
      $db->execute('CREATE DATABASE `' . $config->dbName . '`');
      if ($db->has_error()) die('error');

      // import base sql manually
      $basepath = realpath(DBPATCH_BASE_PATH . "/" . $config->basepath);
      $baseschema = realpath($basepath . "/" . $config->basefile);
      echo "Importing database '" . $config->dbName . "' from '" . $baseschema . "' using MySQL client" . PHP_EOL;
      $output = array();
      $cmd = 'mysql -h ' . $config->dbHost . ' -u ' . $config->dbUsername . ' --password="' . $config->dbPassword . '" ' . $config->dbName . ' < ' . $baseschema;
      $retval = null;
      exec($cmd, $output, $retval);
      if ($retval != 0) {
          echo 'failed to import base sql' . PHP_EOL;
      } else {
          echo 'database imported successfully' . PHP_EOL;
      }
      echo implode(PHP_EOL, $output);
      if ($retval != 0) die();
    }

    // reinit so it reselects the database
    // and creates if necessary
    unset($app);
    $app = new Patch_Engine($config, $printer, DBPATCH_BASE_PATH, false);

    // import patches
    $app->apply_patches();

    echo "Database " . $config->dbName . " updated." . PHP_EOL;
}