<?php namespace Settings;

/**
 * Class Settings
 * Settings initializer
 *
 * @package Settings
 */
class Settings
{
    private static function get_php_file_paths($directory)
    {
        $files = [];
        if ($dir_list = scandir($directory))
        {
            foreach ($dir_list as $file_path)
            {
                // skip dot directories
                if ($file_path != '.' && $file_path != '..')
                {
                    $full_path = $directory . '/' . $file_path;
                    if (substr($file_path, -4) == '.php')
                    {
                        $files[] = $full_path;
                    }
                    elseif (is_dir($full_path))
                    {
                        $files = array_merge($files, self::get_php_file_paths($full_path));
                    }
                }
            }
        }

        return $files;
    }

    public static function init($environment)
    {
        $base_dir = ROOT . "/Settings";
        $settings_files = self::get_php_file_paths($base_dir);

        foreach ($settings_files as $file)
        {
            // $file is the full filesystem path
            // strip everything up to /$folder_name, and the .php extension
            $rel_path = substr($file, strlen(ROOT), -4);

            // convert / to \ so that it's a php namespace
            /** @var SettingsInterface $ns_path */
            $ns_path = str_replace("/", "\\", $rel_path);
            $oReflectionClass = new \ReflectionClass($ns_path);
            if (!$oReflectionClass->isAbstract() && $oReflectionClass->isSubclassOf('\\Settings\\SettingsInterface'))
            {
                $ns_path::init($environment);
            }
        }
    }
}