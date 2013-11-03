<?php

namespace Oro\Bundle\GridBundle\Datagrid;

interface QueryFactoryInterface
{
    /**
     * @return ProxyQueryInterface
     */
    public function createQuery();
}
