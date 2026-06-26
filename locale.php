<?php

date_default_timezone_set('America/Bogota');

class Locale
{
    private static $translations = [];
    private static $currentLang = 'en';

    public static function init()
    {
        if (isset($_COOKIE['lang']) && in_array($_COOKIE['lang'], ['en', 'es'])) {
            self::$currentLang = $_COOKIE['lang'];
        }

        $langFile = __DIR__ . '/lang/' . self::$currentLang . '.php';
        if (file_exists($langFile)) {
            self::$translations = require $langFile;
        }
    }

    public static function setLanguage($lang)
    {
        if (in_array($lang, ['en', 'es'])) {
            self::$currentLang = $lang;
            setcookie('lang', $lang, time() + (365 * 24 * 60 * 60), '/');
            
            $langFile = __DIR__ . '/lang/' . $lang . '.php';
            if (file_exists($langFile)) {
                self::$translations = require $langFile;
            }
        }
    }

    public static function get($key, $default = null)
    {
        return self::$translations[$key] ?? $default ?? $key;
    }

    public static function getCurrentLanguage()
    {
        return self::$currentLang;
    }

    public static function getAvailableLanguages()
    {
        return [
            'en' => 'English',
            'es' => 'Español',
        ];
    }
}

Locale::init();
