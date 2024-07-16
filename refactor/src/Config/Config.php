<?php

declare(strict_types=1);

namespace App\Config;

class Config
{
    public const OCS_DB = "mysql:host=localhost;dbname=dbname;charset=utf8";
    public const OCS_DB_USER = "root";
    public const OCS_DB_PASS = "password";

    public const DEFAULT_OPTION_ID = 13;
    public const DEFAULT_LANGUAGE_ID = 1;

    //new
    public const PRODUCT_FILE = 'json/cards.json';
    public const CATEGORY_FILE = 'json/classif.json';
    public const SIZE_FILE = 'json/sizes.json';
    public const OCS_IMG_PATH = "catalog";
    public const ERROR_LIST_LENGTH = 5000;
}
