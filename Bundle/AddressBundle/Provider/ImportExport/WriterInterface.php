<?php

namespace Oro\Bundle\AddressBundle\Provider\ImportExport;

interface WriterInterface
{
    /**
     * Write batch data
     *
     * @param $data
     * @return true on success
     */
    public function writeBatch($data);
}
