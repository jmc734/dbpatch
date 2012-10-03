<?php

require_once 'DbPatch/Config/SingleDb.php';
require_once 'config.php';

class DbPatch_Config_Master extends config {

    /**
     * @todo Should not be used anymore
     * @deprecated
     */
    public static $basefile = "base.sql";

    /**
     * @todo Should not be used anymore
     * @deprecated
     */
    public static $basepath = "sql/base/";

    /**
     * @todo Should not be used anymore
     * @deprecated
     */
    public static $schemapath = "sql/schema/";

    /**
     * @todo Should not be used anymore
     * @deprecated
     */
    public static $datapath = "sql/data/";
    
    public static $standardized_timezone = "UTC";
    
    /**
     * Flag to prompt for root user credentials if a patch being executed
     * contains root level statements
     * @var boolean 
     */
    public static $prompt_for_root_user = false;
    
    /**
     * List of commands that require root level mysql permissions
     * @var array 
     */
    public static $root_level_commands = 
            array( "EVENT", "TRIGGER", "DROP DATABASE",  
                "SHUTDOWN", "FILE", "GRANT", "CREATE USER", "REVOKE" );

    public static function getSingleDbConfigs() {

        if (!count(self::$db))
            throw new Exception('Database array not set or is empty in config file');
        $configs = array();
        foreach (self::$db as $name => $data) {
            $config = new DbPatch_Config_SingleDb();
            $config->dbClassFile = $data['driver'];
            $config->dbHost = $data['host'];
            $config->dbName = $data['name'];
            $config->dbUsername = $data['user'];
            $config->dbPassword = $data['pass'];
            $config->dbTrackPatchesInFile = $data['track_patches_in_file'];

            $config->basepath = $name . '/' . self::$basepath;
            $config->schemapath = $name . '/' . self::$schemapath;
            $config->datapath = $name . '/' . self::$datapath;

            $config->basefile = self::$basefile;
            $config->standardized_timezone = self::$standardized_timezone;
            $config->prompt_for_root_user = self::$prompt_for_root_user;
            $config->root_level_commands = self::$root_level_commands;

            $configs[] = $config;
        }
        return $configs;
    }

}
