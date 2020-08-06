<?php
namespace Builder\Drivers;

use Builder\Exceptions\LogicException;
use Builder\SimpleQueryBuilderInterface;

class SqlQueryDriver implements SimpleQueryBuilderInterface
{

    private const SELECT = 'SELECT';
    private const FROM = 'FROM';
    private const WHERE = 'WHERE';
    private const GROUPBY = 'GROUP BY';
    private const HAVING = 'HAVING';
    private const ORDERBY = 'ORDER BY';
    private const LIMIT = 'LIMIT';
    private const OFFSET = 'OFFSET';

    private $queryMap = [
        self::SELECT => '',
        self::FROM => '',
        self::WHERE => '',
        self::GROUPBY => '',
        self::HAVING => '',
        self::ORDERBY => '',
        self::LIMIT => '',
        self::OFFSET => ''
    ];

    public function build(): string
    {
        $this->validateLogic();
        $query = $this->prepareQuery();
        $this->clearQueryMap();
        return $query;
    }

    public function buildCount(): string
    {
        $this->validateLogicCount();

        if ($this->queryMap[self::SELECT]) {
            $this->queryMap[self::SELECT] = $this->queryMap[self::SELECT] . ', COUNT(*)';
        } else {
            $this->queryMap[self::SELECT] = self::SELECT . ' COUNT(*)';
        }

        $query = $this->prepareQuery();
        $this->clearQueryMap();
        return $query;
    }

    public function from($tables): SimpleQueryBuilderInterface
    {
        $query = self::FROM;
        if (is_array($tables)) {
            $query .= $this->expandSubQuery($tables);
        } else {
            $query .= $this->expandSubQuery([$tables]);
        }

        $this->queryMap[self::FROM] = $query;

        return $this;
    }

    private function expandSubQuery($tables): string
    {
        $query = '';
        $countInnerTabel = 1;

        foreach ($tables as $tabel) {
            if ($tabel instanceof \Builder\SimpleQueryBuilderInterface) {
                $query .= ' (' . $tabel->build() . ') as t_' . $countInnerTabel;
                $countInnerTabel++;
            } else {
                $query .= ' ' . $tabel;
            }
            $query .= ',';
        }
        return rtrim($query, ',');
    }

    public function groupBy($fields): SimpleQueryBuilderInterface
    {
        $query = self::GROUPBY;
        $this->queryMap[self::GROUPBY] = $query .= $this->expand($fields, ', ');
        return $this;
    }

    public function having($conditions): SimpleQueryBuilderInterface
    {
        $query = self::HAVING;
        $this->queryMap[self::HAVING] = $query .= $this->expand($conditions, ' AND ');
        return $this;
    }

    public function limit($limit): SimpleQueryBuilderInterface
    {
        $query = self::LIMIT;
        $this->queryMap[self::LIMIT] = $query .= ' ' . $limit;
        return $this;
    }

    public function offset($offset): SimpleQueryBuilderInterface
    {
        $query = self::OFFSET;
        $this->queryMap[self::OFFSET] = $query .= ' ' . $offset;
        return $this;
    }

    public function orderBy($fields): SimpleQueryBuilderInterface
    {
        $query = self::ORDERBY;
        $this->queryMap[self::ORDERBY] = $query .= $this->expand($fields, ', ');
        return $this;
    }

    public function select($fields): SimpleQueryBuilderInterface
    {
        $query = self::SELECT;
        $this->queryMap[self::SELECT] = $query .= $this->expand($fields, ', ');
        return $this;
    }

    public function where($conditions): SimpleQueryBuilderInterface
    {
        $query = self::WHERE;
        $this->queryMap[self::WHERE] = $query .= $this->expand($conditions, ' AND ');
        return $this;
    }

    private function expand($fields, string $concat): string
    {
        if (is_array($fields)) {
            return ' ' . implode($concat, $fields);
        } else {
            return ' ' . $fields;
        }
    }

    private function validateLogic()
    {

        if (!$this->queryMap[self::SELECT]) {
            throw new LogicException('Miss operator select');
        }

        if (!$this->queryMap[self::FROM]) {
            throw new LogicException('Miss operator from');
        }

        if ($this->queryMap[self::OFFSET] && !$this->queryMap[self::LIMIT]) {
            throw new LogicException('Miss operator limit');
        }
    }

    private function validateLogicCount()
    {

        if ($this->queryMap[self::OFFSET] || $this->queryMap[self::LIMIT] || $this->queryMap[self::ORDERBY]) {
            throw new LogicException('Found extra operator');
        }

        if (!$this->queryMap[self::FROM]) {
            throw new LogicException('Miss operator from');
        }
    }

    private function prepareQuery(): string
    {
        $queryResult = '';
        foreach ($this->queryMap as $query) {
            if ($query) {
                $queryResult .= $query . ' ';
            }
        }

        //remove space
        return trim($queryResult);
    }

    private function clearQueryMap()
    {
        $this->queryMap = array_map(create_function('$n', 'return \'\';'), $this->queryMap);
    }
}
