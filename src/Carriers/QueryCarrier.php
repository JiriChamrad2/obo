<?php

/**
 * This file is part of the Obo framework for application domain logic.
 * Obo framework is based on voluntary contributions from different developers.
 *
 * @link https://github.com/obophp/obo
 * @license http://www.apache.org/licenses/LICENSE-2.0 Apache License, Version 2.0
 */

namespace obo\Carriers;

class QueryCarrier extends \obo\Carriers\QuerySpecification implements \obo\Interfaces\IQuerySpecification {

    /**
     * @var string
     */
    protected $defaultEntityClassName = null;

    /**
     * @var \obo\Carriers\EntityInformationCarrier
     */
    protected $defaultEntityEntityInformation = null;

    /**
     * @var array
     */
    protected $select = ["query" => "", "data" => []];

    /**
     * @var array
     */
    protected $from = ["query" => "", "data" => []];

    /**
     * @var array
     */
    protected $join = ["query" => "", "data" => []];

    /**
     * @var bool
     */
    protected $distinct = false;

    /**
     * @return string
     */
    public function getDefaultEntityClassName() {
        return $this->defaultEntityClassName;
    }

    /**
     * @param string $defaultEntityClassName
     * @return string
     */
    public function setDefaultEntityClassName($defaultEntityClassName) {
        $this->defaultEntityClassName = $defaultEntityClassName;
        $this->defaultEntityEntityInformation = $defaultEntityClassName::entityInformation();
    }

    /**
     * @return \obo\Carriers\EntityInformationCarrier
     */
    public function getDefaultEntityEntityInformation() {
        return $this->defaultEntityEntityInformation;
    }

    /**
     * @return array
     */
    public function getSelect() {
        return $this->select;
    }

    /**
     * @return \obo\Carriers\QueryCarrier
     */
    public function select() {
        $this->processArguments(func_get_args(), $this->select, " ", ",");
        return $this;
    }

    /**
     * @return \obo\Carriers\QueryCarrier
     */
    public function rewriteSelect() {
        $this->select = ["query" => "", "data" => []];
        return $this->select(func_get_args());
    }

    /**
     * @return array
     */
    public function getFrom() {
        return $this->from;
    }

    /**
     * @return \obo\Carriers\QueryCarrier
     */
    public function from() {
        $this->processArguments(func_get_args(), $this->from, " ");
        return $this;
    }

    /**
     * @return array
     */
    public function getJoin() {
        return $this->join;
    }

    /**
     * @return \obo\Carriers\QueryCarrier
     */
    public function join() {
        $this->processArguments(\func_get_args(), $this->join, " ");
        return $this;
    }

    /**
     * @param bool $flag
     * @return \obo\Carriers\QueryCarrier
     */
    public function distinct($flag = true) {
        $this->distinct = (bool) $flag;
        return $this;
    }

    /**
     * @return bool
     */
    public function getDistinct() {
        return $this->distinct;
    }

    /**
     * @return \obo\Carriers\QueryCarrier
     */
    public function rewriteJoin() {
        $this->join = ["query" => "", "data" => []];
        return $this->join(\func_get_args());
    }

    /**
     * @param \obo\Carriers\QueryCarrier $queryCarrier
     * @return \obo\Carriers\QueryCarrier
     */
    public function addQueryCarrier(\obo\Carriers\QueryCarrier $queryCarrier) {
        if ($this->defaultEntityClassName === null OR $queryCarrier->defaultEntityClassName === null) throw new \obo\Exceptions\Exception("Cannot merge queryCarriers because some of the queryCarriers have no default entity");
        if ($this->defaultEntityClassName !== $queryCarrier->defaultEntityClassName) throw new \obo\Exceptions\Exception("Cannot merge queryCarriers because queryCarriers does not originate from the same source");

        parent::addSpecification($queryCarrier);

        $join = $queryCarrier->getJoin();
        $this->join["query"] .= $join["query"];
        $this->join["data"] = \array_merge($this->join["data"], $join["data"]);

        return $this;
    }

    /**
     * @param \obo\Interfaces\IQuerySpecification $specification
     * @return \obo\Carriers\QueryCarrier
     */
    public function addSpecification(\obo\Interfaces\IQuerySpecification $specification) {
        return $specification instanceof \obo\Carriers\QueryCarrier ? $this->addQueryCarrier($specification) : parent::addSpecification($specification);
    }

    /**
     * @return string
     * @throws \obo\Exceptions\Exception
     */
    public function dumpQuery() {
        if ($this->defaultEntityClassName === null) throw new \obo\Exceptions\Exception("Unable to dump query because default Entity is not set");
        $defaultEntityClassName = $this->defaultEntityClassName;
        $managerClass = $defaultEntityClassName::entityInformation()->managerName;
        return $managerClass::dataStorage()->constructQuery($this);
    }

}
