<?php

namespace Oro\Bundle\DataGridBundle\Datasource;

interface ResultRecordInterface
{
    /**
     * Get value of record property by name
     *
     * @param  string $name
     *
     * @throws \LogicException When cannot get value
     * @return mixed
     */
    public function getValue($name);

    /**
     * Get root entity of current result record
     *
     * @return object|null
     */
    public function getRootEntity();
}
