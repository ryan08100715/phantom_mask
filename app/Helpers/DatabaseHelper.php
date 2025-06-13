<?php

namespace App\Helpers;

use Illuminate\Support\Facades\DB;
use PDO;

class DatabaseHelper
{
    /**
     * 檢查是否為 SQLite 資料庫
     */
    public static function isSqlite(?string $connection = null): bool
    {
        return self::getDriverName($connection) === 'sqlite';
    }

    /**
     * 取得指定連接的資料庫驅動程式名稱
     *
     * @param  string|null  $connection  連接名稱，為 null 時使用預設連接
     */
    public static function getDriverName(?string $connection = null): string
    {
        return DB::connection($connection)
            ->getPdo()
            ->getAttribute(PDO::ATTR_DRIVER_NAME);
    }

    /**
     * 檢查是否為 SQL Server 資料庫
     */
    public static function isSqlServer(?string $connection = null): bool
    {
        return self::getDriverName($connection) === 'sqlsrv';
    }

    /**
     * 檢查是否支援 Fulltext
     */
    public static function isSupportFulltext(?string $connection = null): bool
    {
        return self::isMysql($connection) || self::isPostgres($connection);
    }

    /**
     * 檢查是否為 MySQL 資料庫
     */
    public static function isMysql(?string $connection = null): bool
    {
        return self::getDriverName($connection) === 'mysql';
    }

    /**
     * 檢查是否為 PostgreSQL 資料庫
     */
    public static function isPostgres(?string $connection = null): bool
    {
        return self::getDriverName($connection) === 'pgsql';
    }
}
