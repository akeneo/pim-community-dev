<?php

namespace Oro\Bundle\EntityExtendBundle\Entity;

/**
 * @SuppressWarnings
 */
interface ExtendEntityInterface
{
    public function __toArray();

    public function __fromArray($values);

    public function __extend__setParent($parent);
}
