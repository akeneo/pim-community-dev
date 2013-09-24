<?php

namespace Oro\Bundle\ImportExportBundle\Writer;

use Doctrine\ORM\EntityManager;
use Oro\Bundle\BatchBundle\Item\ItemWriterInterface;

class EntityWriter implements ItemWriterInterface
{
    /**
     * @var EntityManager
     */
    protected $entityManager;

    /**
     * @var EntityDetachFixer
     */
    protected $detachFixer;

    public function __construct(EntityManager $entityManager, EntityDetachFixer $detachFixer)
    {
        $this->entityManager = $entityManager;
        $this->detachFixer = $detachFixer;
    }

    /**
     * {@inheritdoc}
     */
    public function write(array $items)
    {
        foreach ($items as $item) {
            $this->entityManager->persist($item);
            $this->detachFixer->fixEntityAssociationFields($item, 1);
        }
        $this->entityManager->flush();
        $this->entityManager->clear();
    }
}
