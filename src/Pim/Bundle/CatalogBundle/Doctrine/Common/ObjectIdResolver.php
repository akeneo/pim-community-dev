<?php

namespace Pim\Bundle\CatalogBundle\Doctrine\Common;

use Doctrine\Common\Persistence\ManagerRegistry;
use Doctrine\ORM\EntityNotFoundException;

/**
 * Object id resolver
 *
 * TODO: move it to StorageUtilsBundle
 *
 * @author    Julien Sanchez <julien@akeneo.com>
 * @copyright 2014 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
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

        //TODO : do a proper query to fetch codes in one query (think about not found codes)
        $ids = [];
        foreach ($codes as $code) {
            $entity = $repository->findOneBy(['code' => $code]);

            if (!$entity) {
                throw new ObjectNotFoundException(
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
