<?php
namespace Builder;

use Builder\Drivers\SqlQueryDriver;
use Builder\Exceptions\BadDriverException;

class BuilderFactory
{

    private static $map = [
        SqlQueryDriver::class
    ];

    static public function get(string $driverQueryClass): SimpleQueryBuilderInterface
    {
        if (!in_array($driverQueryClass, self::$map)) {
            throw new BadDriverException('Cant find driver: ' . $driverQueryClass);
        }
        $driver = new $driverQueryClass();

        if (!$driver instanceof SimpleQueryBuilderInterface) {
            throw new BadDriverException('Wrong implementation: ' . $driverQueryClass);
        }
        return $driver;
    }
}
