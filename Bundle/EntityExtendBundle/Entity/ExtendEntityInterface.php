<?php

namespace Oro\Bundle\EntityExtendBundle\Entity;

/**
 * @codingStandardsIgnoreFile(PHPCS.MethodDoubleUnderscore)
 */
interface ExtendEntityInterface
{
    public function __toArray();

    public function __fromArray($values);

    public function __extend__setParent($parent);
}
