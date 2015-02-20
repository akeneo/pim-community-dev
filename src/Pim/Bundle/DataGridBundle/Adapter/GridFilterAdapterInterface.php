<?php

namespace Pim\Bundle\DataGridBundle\Adapter;


interface GridFilterAdapterInterface
{
    public function transform(array $filter);
}
