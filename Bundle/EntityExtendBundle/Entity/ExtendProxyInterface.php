<?php

namespace Oro\Bundle\EntityExtendBundle\Entity;

interface ExtendProxyInterface
{
    public function __fromArray(array $values);

    public function __toArray();
}