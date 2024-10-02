<?php

declare(strict_types=1);

namespace App\Config;

class Config
{
    public static int $defaultOptionId = 13; // option "Размер"
    public static int $defaultLanguageId = 1; // RUS
    public static string $productFile = 'json/products.json';
    public static string $categoryFile = 'json/categories.json';
    public static string $sizeFile = 'json/sizes.json';
    public static string $ocsImgPath = 'catalog';
    public static int $errorListLength = 5000;
    public static string $databaseName = 'dbname';
    public static string $databaseUser = 'root';
    public static string $databasePassword = 'password';
    public static string $databaseHost = 'host';
    public static string $databaseDriver = 'pdo_mysql';

    public static function initialize(): void
    {
        $configFileName = '.env';
        $loadedConfig = [];
        if (file_exists($configFileName)) {
            $loadedConfig = parse_ini_file($configFileName);
        }

        self::$defaultOptionId = (int)$loadedConfig['DEFAULT_OPTION_ID'] ?? self::$defaultOptionId;
        self::$defaultLanguageId = (int)$loadedConfig['DEFAULT_LANGUAGE_ID'] ?? self::$defaultLanguageId;
        self::$productFile = $loadedConfig['PRODUCT_FILE'] ?? self::$productFile;
        self::$categoryFile = $loadedConfig['CATEGORY_FILE'] ?? self::$categoryFile;
        self::$sizeFile = $loadedConfig['SIZE_FILE'] ?? self::$sizeFile;
        self::$ocsImgPath = $loadedConfig['OCS_IMG_PATH'] ?? self::$ocsImgPath;
        self::$errorListLength = (int)$loadedConfig['ERROR_LIST_LENGTH'] ?? self::$errorListLength;
        self::$databaseName = $loadedConfig['DATABASE_NAME'] ?? self::$databaseName;
        self::$databaseUser = $loadedConfig['DATABASE_USER'] ?? self::$databaseUser;
        self::$databasePassword = $loadedConfig['DATABASE_PASSWORD'] ?? self::$databasePassword;
        self::$databaseHost = $loadedConfig['DATABASE_HOST'] ?? self::$databaseHost;
        self::$databaseDriver = $loadedConfig['DATABASE_DRIVER'] ?? self::$databaseDriver;
    }

    public static function getDbConnParams(): array
    {
        return [
            'host' => self::$databaseHost,
            'dbname' => self::$databaseName,
            'user' => self::$databaseUser,
            'password' => self::$databasePassword,
            'driver' => self::$databaseDriver,
        ];
    }
}
