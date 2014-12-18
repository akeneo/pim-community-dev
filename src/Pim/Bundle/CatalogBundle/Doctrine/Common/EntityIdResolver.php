<?php

namespace Pim\Bundle\CatalogBundle\Doctrine\Common;

use Doctrine\ORM\EntityManager;

class EntityIdResolver implements EntityIdResolverInterface
{
    /** @var EntityManager */
    protected $entityManager;

    /** @var array */
    protected $fieldMapping = [];

    /**
     * @param EntityManager $entityManager
     */
    public function __construct(EntityManager $entityManager)
    {
        $this->entityManager = $entityManager;
    }

    /**
     * {@inheritdoc}
     */
    public function getIdsFromCodes($entityName, $codes)
    {
        if (!isset($this->fieldMapping[$entityName])) {
            throw new \InvalidArgumentException(sprintf('The class %s cannot be found', $entityName));
        }

        $repository = $this->entityManager->getRepository($this->fieldMapping[$entityName]);

        //TODO : do better
        $ids = [];
        foreach ($codes as $code) {
            $entity = $repository->findOneBy(['code' => $code]);

            if (!$entity) {
                throw new EntityNotFoundException(
                    sprintf('Entity "%s" with code "%s" does not exist', $entityName, $code)
                );
            }

            $ids[] = $entity->getId();
        }

        return $ids;
    }

    /**
     * {@inheritdoc}
     */
    public function addFieldMapping($entityName, $className)
    {
        $this->fieldMapping[$entityName] = $className;
    }
}
