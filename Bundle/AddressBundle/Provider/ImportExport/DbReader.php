<?php

namespace Oro\Bundle\AddressBundle\Provider\ImportExport;

use Doctrine\Common\Persistence\ObjectManager;

class DbReader extends Reader implements ReaderInterface
{
    /**
     * @var ObjectManager
     */
    protected $om;

    /**
     * @var string
     */
    protected $class;

    /**
     * @param string $class
     * @param ObjectManager $om
     * @param null|int $batchSize
     */
    public function __construct($class, ObjectManager $om, $batchSize = null)
    {
        $metadata = $om->getClassMetadata($class);
        $this->class = $metadata->getName();
        $this->om = $om;

        if (!is_null($batchSize)) {
            $this->batchSize = $batchSize;
        }
    }

    /**
     * @inheritdoc
     */
    public function readBatch()
    {
        $offset = $this->offset * $this->batchSize;
        $this->offset++;

        return $this->om->getRepository($this->class)->findBy(array(), array(), $this->batchSize, $offset);
    }
}
