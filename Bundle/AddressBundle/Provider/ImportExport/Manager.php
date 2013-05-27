<?php

namespace Oro\Bundle\AddressBundle\Provider\ImportExport;

class Manager
{
    /**
     * @var ReaderInterface
     */
    protected $reader;

    /**
     * @var WriterInterface
     */
    protected $writer;

    public function __construct(WriterInterface $writer, ReaderInterface $reader = null)
    {
        $this->reader = $reader;
        $this->writer = $writer;
    }

    /**
     * Reading/Writing data in a batch and write/read it to destination
     * data can be passed through argument or read from reader
     *
     * @param $data
     * @throws \Exception
     * @return boolean true on success
     */
    public function sync($data = null)
    {
        if ($this->reader instanceof ReaderInterface) {
            while ($batchData = $this->reader->readBatch()) {
                $this->writer->writeBatch($batchData);
            }
        } elseif (!is_null($data)) {
            $this->writer->writeBatch($data);
        } else {
            throw new \Exception("Source is not defined");
        }

        return true;
    }
}
