<?php
namespace Builder;

interface SimpleQueryBuilderInterface
{

    /**
     * @param array|string $fields
     * @return SimpleQueryBuilderInterface
     */
    public function select($fields): SimpleQueryBuilderInterface;

    /**
     * @param string|SimpleQueryBuilderInterface|array<string|SimpleQueryBuilderInterface> $tables
     * @return SimpleQueryBuilderInterface
     */
    public function from($tables): SimpleQueryBuilderInterface;

    /**
     * @param string|array $conditions
     * @return SimpleQueryBuilderInterface
     */
    public function where($conditions): SimpleQueryBuilderInterface;

    /**
     * @param string|array $fields
     * @return SimpleQueryBuilderInterface
     */
    public function groupBy($fields): SimpleQueryBuilderInterface;

    /**
     * @param string|array $conditions
     * @return SimpleQueryBuilderInterface
     */
    public function having($conditions): SimpleQueryBuilderInterface;

    /**
     * @param string|array $fields
     * @return SimpleQueryBuilderInterface
     */
    public function orderBy($fields): SimpleQueryBuilderInterface;

    /**
     * @param int $limit
     * @return SimpleQueryBuilderInterface
     */
    public function limit($limit): SimpleQueryBuilderInterface;

    /**
     * @param int $offset
     * @return SimpleQueryBuilderInterface
     */
    public function offset($offset): SimpleQueryBuilderInterface;

    /**
     * @throws LogicException
     * @return string
     */
    public function build(): string;

    /**
     * @throws LogicException
     * @return string
     */
    public function buildCount(): string;
}
