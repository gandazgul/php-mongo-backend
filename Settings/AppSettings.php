<?php namespace Settings;

/**
 * Class AppSettings
 * Global app settings
 *
 * @package Settings
 */
class AppSettings implements SettingsInterface
{
    public static $base_url = 'backend.local';

    public static function init($environment) { }
}