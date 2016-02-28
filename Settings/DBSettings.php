<?php namespace Settings;

/**
 * Class DBSettings
 * Database settings
 *
 * @package Settings
 */
class DBSettings implements SettingsInterface
{
    public static $db_host = '127.0.0.1';
    public static $db_port = 27017;
    public static $db_database = 'backend';
    public static $db_username = 'backend';
    public static $db_password = 'TBD';

    public static function init($environment) { }
}