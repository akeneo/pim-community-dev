<?php

namespace Oro\Bundle\AddressBundle\Provider\ImportExport;

use Doctrine\Common\Persistence\ObjectManager;

class DbWriter implements WriterInterface
{
    /**
     * @var ObjectManager
     */
    protected $om;

    public function __construct(ObjectManager $om)
    {
        $this->om = $om;
    }

    /**
     * @inheritdoc
     */
    public function writeBatch($data)
    {
        if (empty($data) || !is_array($data)) {
            return false;
        }

        foreach ($data as $entry) {
            if (is_object($entry)) {
                $this->om->persist($entry);
            } else {
                throw new \Exception("Entry passed to writer is not an object");
            }
        }

        $this->om->flush();

        return true;
    }
}
