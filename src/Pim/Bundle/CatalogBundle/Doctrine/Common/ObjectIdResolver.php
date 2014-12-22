<?php

namespace Pim\Bundle\CatalogBundle\Doctrine\Common;

use Doctrine\Common\Persistence\ManagerRegistry;

class ObjectIdResolver implements ObjectIdResolverInterface
{
    /** @var ManagerRegistry */
    protected $managerRegistry;

    /** @var array */
    protected $fieldMapping = [];

    /**
     * @param ManagerRegistry $managerRegistry
     */
    public function __construct(ManagerRegistry $managerRegistry)
    {
        $this->managerRegistry = $managerRegistry;
    }

    /**
     * {@inheritdoc}
     */
    public function getIdsFromCodes($entityName, $codes)
    {
        if (!isset($this->fieldMapping[$entityName])) {
            throw new \InvalidArgumentException(sprintf('The class %s cannot be found', $entityName));
        }

        $repository = $this->managerRegistry
            ->getManagerForClass($this->fieldMapping[$entityName])
            ->getRepository($this->fieldMapping[$entityName]);

        //TODO : do better
        $ids = [];
        foreach ($codes as $code) {
            $entity = $repository->findOneBy(['code' => $code]);

            if (!$entity) {
                throw new EntityNotFoundException(
                    sprintf('Object "%s" with code "%s" does not exist', $entityName, $code)
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
