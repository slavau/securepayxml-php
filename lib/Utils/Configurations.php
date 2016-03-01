<?php

namespace SecurePay\XMLAPI\Utils;

/**
 * A singleton class which provides configuration details to other classes.
 *
 * Class Configurations
 * @author Beng Lim <benglim92@gmail.com>
 * @package SecurePay\XMLAPI\Utils
 */
class Configurations
{
    /**
     * @var array The array of configurations
     */
    private $configArray;

    /**
     * @var Configurations The static instance of the singleton class.
     */
    private static $instance;

    /**
     * Configurations constructor.
     * Loads the config.ini file in the SecurePay/XMLAPI folder.
     */
    private function __construct() {
        $this->configArray = parse_ini_file(dirname(__FILE__) . "/../../config.ini");

    }

    /**
     * Gets the value of a config parameter
     *
     * @param string $key The key of the config parameter
     * @return mixed The value of the config parameter
     */
    public static function getConfig($key) {
       return self::getInstance()->configArray[$key];
    }

    /**
     * Used internally for the getConfig function.
     *
     * @return Configurations The configurations instance.
     */
    private static function getInstance() {
        if (self::$instance == null) {
            self::$instance = new self();
        }
        return self::$instance;
    }
}