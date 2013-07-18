<?php

namespace Oro\Bundle\EntityExtendBundle\Entity;

/**
 * @codingStandardsIgnoreFile
 * @SuppressWarnings()
 */
interface ExtendProxyInterface
{
    public function __proxy__setExtend($extend);

    public function __proxy__getExtend();

    public function __proxy__createFromEntity($entity);

    public function __proxy__toArray();
}
