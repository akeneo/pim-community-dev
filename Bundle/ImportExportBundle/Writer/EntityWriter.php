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

    public function __construct(EntityManager $entityManager)
    {
        $this->entityManager = $entityManager;
    }

    /**
     * {@inheritdoc}
     */
    public function write(array $items)
    {
        foreach ($items as $item) {
            $this->entityManager->persist($item);
        }
        $this->entityManager->flush();
    }
}
