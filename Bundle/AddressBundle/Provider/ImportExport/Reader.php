<?php

namespace Oro\Bundle\AddressBundle\Provider\ImportExport;


abstract class Reader
{
    /**
     * @var int batch size for reading
     */
    protected $batchSize = 100;

    /**
     * @var int offset
     */
    protected $offset = 0;

    /**
     * Reset offset
     */
    public function reset()
    {
        $this->offset = 0;
    }

    /**
     * Return batch size
     *
     * @return int
     */
    public function getBatchSize()
    {
        return $this->batchSize;
    }
}
