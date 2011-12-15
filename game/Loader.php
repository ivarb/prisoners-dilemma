<?php
class Loader
{
    private static $dir = 'strategies/';
    private static $if  = 'Interface_Strategy';
    private static $strategies;

    public static function load()
    {
        if (is_null(self::$strategies)) {
            $files = self::getFiles();
            if (is_array($files) && count($files)) {
                $objects = self::loadObjects($files);
                if (!count($objects)) {
                    throw new Exception('No strategy objects found');
                }
                self::$strategies = $objects;
                unset($objects,$files);
            }
        }
        return self::$strategies;
    }

    private static function getFiles()
    {
        return glob(self::$dir . '*.strategy.php');
    }

    private static function loadObjects(array $files)
    {
        $objects = array();
        foreach ($files as $file) {
            $full  = $file;
            $class = ucfirst(substr($file, strlen(self::$dir), strpos($file, '.') - strlen(self::$dir)));
            if ($class && file_exists($full) && is_readable($full)) {
                require_once($full);
                if (class_exists($class)) {
                    $obj = new $class();
                    if ($obj instanceof self::$if) {
                        $objects[] = $obj;
                    }
                    unset($obj);
                }
            }
            unset($full,$class,$obj);
        }
        return $objects;
    }
}