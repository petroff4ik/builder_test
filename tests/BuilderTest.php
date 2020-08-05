<?php

use Builder\BuilderFactory;
use Codeception\Test\Unit;

class BuilderTest extends Unit
{

    /**
     * @var UnitTester
     */
    protected $tester;

    // tests
    public function testSomeFeature()
    {
        $driverQueryClass = '';
        $builder = BuilderFactory::get($driverQueryClass);
    }
}
