<?php

use Builder\BuilderFactory;
use Codeception\Test\Unit;

class BuilderTest extends Unit
{

    use \Codeception\Specify;

    public function testLoadBuilder()
    {
        $builder = BuilderFactory::get(\Builder\Drivers\SqlQueryDriver::class);
        $this->assertInstanceOf(Builder\SimpleQueryBuilderInterface::class, $builder);
    }

    public function testLoadWrongBuilder()
    {
        $this->expectException(Builder\Exceptions\BadDriverException::class);
        BuilderFactory::get('unknown');
    }

    public function testSelectFrom()
    {
        $builder = BuilderFactory::get(\Builder\Drivers\SqlQueryDriver::class);
        $this->specify('Test simple', function() use($builder) {

            $query = $builder->select('user')
                ->from('users')
                ->build();
            $this->assertNotEmpty($query);
            $this->assertStringMatchesFormat('SELECT %s FROM %s', $query);
        });


        $this->specify('Test params array', function() use($builder) {
            $queryArray = $builder->select([
                    'user',
                    'last_name'
                ])
                ->from('users')
                ->build();
            $this->assertNotEmpty($queryArray);
            $this->assertStringMatchesFormat('SELECT %s, %s FROM %s', $queryArray);
        });


        $this->specify('Test inner sub query', function() use($builder) {


            $builderSubQuery2 = BuilderFactory::get(\Builder\Drivers\SqlQueryDriver::class);
            $querySubQuery2 = $builderSubQuery2->select('message')
                ->from('comment');

            $builderSubQuery = BuilderFactory::get(\Builder\Drivers\SqlQueryDriver::class);
            $querySubQuery = $builderSubQuery->select('post')
                ->from([
                'posts',
                $querySubQuery2
            ]);


            $querySub = $builder->select([
                    'user',
                    'last_name'
                ])
                ->from([
                    'users',
                    $querySubQuery
                ])
                ->build();

            $this->assertNotEmpty($querySub);
            $this->assertStringMatchesFormat('SELECT %s, %s FROM %s, (SELECT %s FROM %s, (%s) as t_1) as t_1', $querySub);
        });

        $this->specify('Test inner sub query', function() use($builder) {


            $builderSubQuery2 = BuilderFactory::get(\Builder\Drivers\SqlQueryDriver::class);
            $querySubQuery2 = $builderSubQuery2->select('message')
                ->from('comment');

            $builderSubQuery = BuilderFactory::get(\Builder\Drivers\SqlQueryDriver::class);
            $querySubQuery = $builderSubQuery->select('post')
                ->from([
                'posts',
            ]);


            $querySub = $builder->select([
                    'user',
                    'last_name'
                ])
                ->from([
                    $querySubQuery2,
                    $querySubQuery
                ])
                ->build();

            $this->assertNotEmpty($querySub);
            $this->assertStringMatchesFormat('SELECT %s, %s FROM (%s) as t_1, (%s) as t_2', $querySub);
        });
    }

    public function testWhere()
    {
        $builder = BuilderFactory::get(\Builder\Drivers\SqlQueryDriver::class);
        $this->specify('Test simple params', function() use($builder) {
            $query = $builder->select('user')
                ->from('users')
                ->where('user.active = 1')
                ->build();
            $this->assertNotEmpty($query);
            $this->assertStringMatchesFormat('SELECT %s FROM %s WHERE %s', $query);
        });


        $this->specify('Test arrray params', function() use($builder) {
            $queryArray = $builder->select('user')
                ->from('users')
                ->where([
                    'user.active = 1',
                    'user.email = some'
                    ]
                )
                ->build();
            $this->assertNotEmpty($queryArray);
            $this->assertStringMatchesFormat('SELECT %s FROM %s WHERE %s AND %s', $queryArray);
        });
    }

    public function testGroupBy()
    {
        $builder = BuilderFactory::get(\Builder\Drivers\SqlQueryDriver::class);

        $this->specify('Test simple params', function() use($builder) {
            $query = $builder->select('user')
                ->from('users')
                ->where('user.active = 1')
                ->groupBy('status')
                ->build();
            $this->assertNotEmpty($query);
            $this->assertStringMatchesFormat('SELECT %s FROM %s WHERE %s GROUP BY %s', $query);
        });

        $this->specify('Test arrray params', function() use($builder) {
            $queryArray = $builder->select('user')
                ->from('users')
                ->where('user.active = 1')
                ->groupBy([
                    'status',
                    'name'
                ])
                ->build();
            $this->assertNotEmpty($queryArray);
            $this->assertStringMatchesFormat('SELECT %s FROM %s WHERE %s GROUP BY %s, %s', $queryArray);
        });
    }

    public function testHaving()
    {
        $builder = BuilderFactory::get(\Builder\Drivers\SqlQueryDriver::class);

        $this->specify('Test simple params', function() use($builder) {
            $query = $builder->select('user')
                ->from('users')
                ->where('user.active = 1')
                ->groupBy('status')
                ->having('SUM(price) = 1')
                ->build();
            $this->assertNotEmpty($query);
            $this->assertStringMatchesFormat('SELECT %s FROM %s WHERE %s GROUP BY %s HAVING %s', $query);
        });

        $this->specify('Test arrray params', function() use($builder) {
            $queryArray = $builder->select('user')
                ->from('users')
                ->where('user.active = 1')
                ->groupBy([
                    'status',
                    'name'
                ])
                ->having([
                    'SUM(price) = 1',
                    'SUM(tax) > 2'
                ])
                ->build();
            $this->assertNotEmpty($queryArray);
            $this->assertStringMatchesFormat('SELECT %s FROM %s WHERE %s GROUP BY %s, %s HAVING %s AND %s', $queryArray);
        });
    }

    public function testOrderBy()
    {
        $builder = BuilderFactory::get(\Builder\Drivers\SqlQueryDriver::class);

        $this->specify('Test simple params', function() use($builder) {
            $query = $builder->select('user')
                ->from('users')
                ->where('user.active = 1')
                ->groupBy('status')
                ->orderBy('dt')
                ->build();
            $this->assertNotEmpty($query);
            $this->assertStringMatchesFormat('SELECT %s FROM %s WHERE %s GROUP BY %s ORDER BY %s', $query);
        });

        $this->specify('Test arrray params', function() use($builder) {
            $queryArray = $builder->select('user')
                ->from('users')
                ->where('user.active = 1')
                ->groupBy([
                    'status',
                    'name'
                ])
                ->orderBy([
                    'dt',
                    'ut'
                ])
                ->build();
            $this->assertNotEmpty($queryArray);
            $this->assertStringMatchesFormat('SELECT %s FROM %s WHERE %s GROUP BY %s, %s ORDER BY %s, %s', $queryArray);
        });
    }

    public function testQueryBuilderTotal()
    {
        $builder = BuilderFactory::get(\Builder\Drivers\SqlQueryDriver::class);
        $query = $builder->select('user')
            ->from('users')
            ->where('user.enable = 1')
            ->groupBy('status')
            ->having('MAX(id) > 3')
            ->orderBy('name')
            ->limit('10')
            ->offset('5')
            ->build();
        $this->assertNotEmpty($query);
        $this->assertStringMatchesFormat('SELECT %s FROM %s WHERE %s GROUP BY %s HAVING %s ORDER BY %s LIMIT %s OFFSET %s', $query);
    }

    public function testQueryBuilderCount()
    {
        $builder = BuilderFactory::get(\Builder\Drivers\SqlQueryDriver::class);
        $this->specify('Test count with field', function() use($builder) {
            $query = $builder->select('user')
                ->from('users')
                ->where('user.enable = 1')
                ->buildCount();
            $this->assertNotEmpty($query);
            $this->assertStringMatchesFormat('SELECT %s, COUNT(%s) FROM %s WHERE %s', $query);
        });

        $this->specify('Test just count ', function() use($builder) {
            $query = $builder->from('users')
                ->buildCount();
            $this->assertNotEmpty($query);
            $this->assertStringMatchesFormat('SELECT COUNT(%s) FROM %s', $query);
        });
    }

    public function testWrongQueryBuilder()
    {
        $this->expectException(Builder\Exceptions\LogicException::class);
        $builder = BuilderFactory::get(\Builder\Drivers\SqlQueryDriver::class);

        $this->specify('Test throw exception when wrong order for build', function() use($builder) {
            $builder->from('users')
                ->where('user.enable = 1')
                ->groupBy('status')
                ->having('MAX(id) > 3')
                ->orderBy('name')
                ->limit('10')
                ->offset('5')
                ->build();
        });

        $this->specify('Test throw exception when wrong order for build count', function() use($builder) {
            $builder->from('users')
                ->where('user.enable = 1')
                ->groupBy('status')
                ->having('MAX(id) > 3')
                ->orderBy('name')
                ->limit('10')
                ->offset('5')
                ->buildCount();
        });
    }
}
