<?php

/**
 * @author Ben Keen <ben.keen@gmail.com>
 * @package Core
 */
class Schema {
    private $link;

    public function __construct($dbHostname, $dbUsername, $dbPassword, $dbName) {
        try {
            $this->link = mysql_connect($dbHostname, $dbUsername, $dbPassword);
            mysql_select_db($dbName, $this->link);
        } catch (Exception $e) {
             die("Couldn't connect to database: " . mysql_error());
        }

        try {
            mysql_set_charset('utf8', $this->link);
        } catch (Exception $e) {
         //  die ("couldn't find database '$g_db_name': " . mysql_error());
        }
    }

    public function getListDatabases(){
        $sql = sprintf("SHOW DATABASES");

        $results = mysql_query($sql) or die (mysql_error());
        $databases = array();
        while ($field = mysql_fetch_assoc($results)) {
            $databases[] = $field['Database']; 
        }
        return $databases;
    }

    public function getListTables($dbName){
        $sql = sprintf("SELECT TABLE_NAME from INFORMATION_SCHEMA.TABLES WHERE TABLE_SCHEMA = '%s'", $dbName);

        $results = mysql_query($sql) or die (mysql_error());
        $tables = array();
        while ($field = mysql_fetch_assoc($results)) {
            $tables[] = $field['TABLE_NAME']; 
        }
        return $tables;
    }

    public function getListColumns($dbName, $tableName){
        $sql = sprintf("SELECT * FROM INFORMATION_SCHEMA.COLUMNS WHERE TABLE_SCHEMA = '%s' AND TABLE_NAME = '%s'", $dbName, $tableName);
        $results = mysql_query($sql) or die (mysql_error());
        $columns = array();
        while ($field = mysql_fetch_assoc($results)) {
            $columns[] = array('name' => $field['COLUMN_NAME'],
                               'data_type' => $this->getDataType($field)); 
        }
        return $columns;
    }

    public function getDataType($field){
        $dataType = array("name" => "TextRandom", "options" => "");
        switch ($field['DATA_TYPE']){
            case "int":
                        if ($field["EXTRA"] == "auto_increment" || $field["COLUMN_KEY"] == "PRI"){
                            $dataType["name"] = "AutoIncrement";
                        }else{
                            $dataType["name"] = "NumberRange";
                        }
                        break;
            case "tinyint":
                        $dataType["name"] = "NumberRange";
                        $dataType["options"][] = array("name" => "NumRangeMin",
                                                       "value" => "0");
                        $dataType["options"][] = array("name" => "NumRangeMax",
                                                       "value" => "0");
                        break;
            case "date": 
                        $dataType["name"] = "Date";
                        $dataType["options"][] = array("name" => "Option",
                                                       "value" => "Y-m-d");
                        break;
            case "datetime":
                        $dataType["name"] = "Date";
                        $dataType["options"][] = array("name" => "Option",
                                                       "value" => "Y-m-d H:i:s");
                        break;
            case "varchar":
                        if ($field["COLUMN_NAME"] == "email"){
                            $dataType["name"] = "Email";
                        }elseif ($field["COLUMN_NAME"] == "first_name" || $field["COLUMN_NAME"] == "last_name"){
                            $dataType["name"] = "Names";
                            $dataType["options"][] = array("name" => "Option",
                                                       "value" => "Name");
                        }
                        break;
            case "enum":
                        $dataType["name"] = "List";
                        break;
        }
        return $dataType;
    }
}


