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
    public static $secret = '388a2b306d2888440a040526ee19ff1d69a617c6f448330a49badcd114484534';
    /** @var string Time in minutes */
    public static $token_lifetime = '10';

    public static function init($environment) { }
}