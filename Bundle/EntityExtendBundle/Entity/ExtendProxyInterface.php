<?php

namespace Oro\Bundle\EntityExtendBundle\Entity;

interface ExtendProxyInterface
{
    public function __proxy__setExtend($extend);

    public function __proxy__toArray();

    public function __proxy__fromArray($values);
}