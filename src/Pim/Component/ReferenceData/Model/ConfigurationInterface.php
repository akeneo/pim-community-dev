<?php

namespace Pim\Component\ReferenceData\Model;

interface ConfigurationInterface
{
    const TYPE_SIMPLE = 'simple';
    const TYPE_MULTI = 'multi';

    /**
     * @return string
     */
    public function getClass();

    /**
     * @param string $class
     */
    public function setClass($class);

    /**
     * @return string
     */
    public function getName();

    /**
     * @param string $name
     */
    public function setName($name);
    
    /**
     * @return string
     */
    public function getType();

    /**
     * @param string $type
     */
    public function setType($type);
}
