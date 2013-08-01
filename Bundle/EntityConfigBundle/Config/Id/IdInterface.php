<?php

namespace Oro\Bundle\EntityConfigBundle\Config\Id;

interface IdInterface extends \Serializable
{
    /**
     * @return string
     */
    public function getId();

    /**
     * @return string
     */
    public function getScope();

    /**
     * @return string
     */
    public function __toString();
}
