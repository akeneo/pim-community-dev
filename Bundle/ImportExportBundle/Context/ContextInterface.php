<?php

namespace Oro\Bundle\ImportExportBundle\Context;

interface ContextInterface
{
    /**
     * @param string $message
     * @param int|null $severity constant of ErrorException
     */
    public function addError($message, $severity = null);

    /**
     * @return array
     */
    public function getErrors();

    /**
     * @return void
     */
    public function incrementReadCount();

    /**
     * @return int
     */
    public function getReadCount();

    /**
     * @return void
     */
    public function incrementUpdateCount();

    /**
     * @return int
     */
    public function getUpdateCount();

    /**
     * @return void
     */
    public function incrementReplaceCount();

    /**
     * @return int
     */
    public function getReplaceCount();

    /**
     * @return void
     */
    public function incrementDeleteCount();

    /**
     * @return int
     */
    public function getDeleteCount();

    /**
     * @param string $name
     * @param mixed $value
     */
    public function setValue($name, $value);

    /**
     * @param string $name
     * @return mixed
     */
    public function getValue($name);

    /**
     * @return array
     */
    public function getConfiguration();
}
