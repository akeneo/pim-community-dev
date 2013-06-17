<?php

namespace Oro\Bundle\EntityExtendBundle\Entity;

interface ExtendEntityInterface
{
    public function setParent($parent);

    public function get($key);

    public function set($key, $value);

    public function __toArray();

    public function __fromArray($values);
}
