<?php

namespace Oro\Bundle\EntityExtendBundle\Entity;

interface ExtendEntityInterface
{
    public function __extend__setParent($parent);

    public function __toArray();

    public function __fromArray($values);
}
