<?php

$tests_dir = dirname(__FILE__);
$base_dir = dirname($tests_dir);

TestFiles::$baseDir = realpath("/tmp");
if (TestFiles::$baseDir === false) {
    throw new exception("baseDir is invalid");
}


require_once($base_dir . DIRECTORY_SEPARATOR . 'vendor' . DIRECTORY_SEPARATOR . 'autoload.php');

use uarsoftware\dbpatch\App\Config;
use uarsoftware\dbpatch\App\Patch;
use uarsoftware\dbpatch\App\DatabaseInterface;


class TestFiles {

    static public $baseDir;

    public static function setUpFiles() {

        // set up basic patches
        $sqlPath =  self::$baseDir . DIRECTORY_SEPARATOR . 'sql';
        $initPath = self::$baseDir . DIRECTORY_SEPARATOR . 'sql' . DIRECTORY_SEPARATOR . 'init';
        $schemaPath = self::$baseDir . DIRECTORY_SEPARATOR . 'sql' . DIRECTORY_SEPARATOR . 'schema';
        $dataPath = self::$baseDir . DIRECTORY_SEPARATOR . 'sql' . DIRECTORY_SEPARATOR . 'data';
        $scriptPath = self::$baseDir . DIRECTORY_SEPARATOR . 'sql' . DIRECTORY_SEPARATOR . 'script';

        $file1 = $schemaPath . DIRECTORY_SEPARATOR . "1test.sql";
        $file2 = $schemaPath . DIRECTORY_SEPARATOR . "2test.sql";
        $file3 = $dataPath . DIRECTORY_SEPARATOR . "3test.sql";

        mkdir($sqlPath);
        mkdir($initPath);
        mkdir($schemaPath);
        mkdir($dataPath);
        mkdir($scriptPath);

        file_put_contents($file1,"create table mytest2 (id int);");
        file_put_contents($file2,"alter table mytest2 add name char(1);");
        file_put_contents($file3,"insert into mytest2 values (1,'a');");

        // set up some test dirs for configFullPath loading
        mkdir(normalizeDirectory(self::$baseDir . '/level1'),0777,true);
        mkdir(normalizeDirectory(self::$baseDir . '/level1/level2'),0777,true);
        mkdir(normalizeDirectory(self::$baseDir . '/level1/level2/level3'),0777,true);

        self::writeConfigFile(normalizeDirectory(self::$baseDir . '/level1/level1.php'));
        self::writeConfigFile(normalizeDirectory(self::$baseDir . '/level1/level2/level2.php'));
        self::writeConfigFile(normalizeDirectory(self::$baseDir . '/level1/level2/config.php'));

    }

    public static function tearDownFiles() {

        $sqlPath = self::$baseDir . DIRECTORY_SEPARATOR . 'sql';
        $initPath = self::$baseDir . DIRECTORY_SEPARATOR . 'sql' . DIRECTORY_SEPARATOR . 'init';
        $schemaPath = self::$baseDir . DIRECTORY_SEPARATOR . 'sql' . DIRECTORY_SEPARATOR . 'schema';
        $dataPath = self::$baseDir . DIRECTORY_SEPARATOR . 'sql' . DIRECTORY_SEPARATOR . 'data';
        $scriptPath = self::$baseDir . DIRECTORY_SEPARATOR . 'sql' . DIRECTORY_SEPARATOR . 'script';

        $file1 = $schemaPath . DIRECTORY_SEPARATOR . "1test.sql";
        $file2 = $schemaPath . DIRECTORY_SEPARATOR . "2test.sql";
        $file3 = $dataPath . DIRECTORY_SEPARATOR . "3test.sql";


        unlink($file3);
        unlink($file2);
        unlink($file1);

        rmdir($scriptPath);
        rmdir($dataPath);
        rmdir($schemaPath);
        rmdir($initPath);
        rmdir($sqlPath);


        unlink(normalizeDirectory(self::$baseDir . '/level1/level2/config.php'));
        unlink(normalizeDirectory(self::$baseDir . '/level1/level2/level2.php'));
        unlink(normalizeDirectory(self::$baseDir . '/level1/level1.php'));

        rmdir(normalizeDirectory(self::$baseDir . '/level1/level2/level3'));
        rmdir(normalizeDirectory(self::$baseDir . '/level1/level2'));
        rmdir(normalizeDirectory(self::$baseDir . '/level1'));

    }

    public static function writeConfigFile($file) {
        $contents = '<?php

        $test_config = new \uarsoftware\dbpatch\App\Config("test","mysql","localhost","test","root","root");
        $test_config->setPort(3306);
        $test_config->disableTrackingPatchesInFile();

        return $test_config;';

        file_put_contents($file,$contents);
    }
}

function normalizeDirectory($dir) {
    return str_replace(array("/","\\"),DIRECTORY_SEPARATOR,$dir);
}

class MockDatabase implements DatabaseInterface {

    protected $appliedPatches;

    public function __construct(Config $config) {
        $this->appliedPatches = array();
    }

    public function setAppliedPatches(Array $list) {
        $this->appliedPatches = array();
        foreach ($list as $item) {
            $patch = new Patch($item);
            $this->appliedPatches[] = $patch;
        }
    }

    public function getAppliedPatches() {
        return $this->appliedPatches;
    }

}