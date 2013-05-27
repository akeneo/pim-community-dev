<?php

namespace Oro\Bundle\AddressBundle\Provider\ImportExport;

interface ReaderInterface
{
    /**
     * Return data from source in batch
     *
     * @return mixed
     */
    public function readBatch();
}
