<?php

namespace Pim\Bundle\CatalogBundle\Doctrine\Common\Filter;

use Doctrine\Common\Persistence\ManagerRegistry;
use Pim\Bundle\CatalogBundle\Exception\ObjectNotFoundException;
use Pim\Bundle\CatalogBundle\Model\AttributeInterface;

/**
 * Object id resolver
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
    public function getIdsFromCodes($entityName, array $codes, AttributeInterface $attribute = null)
    {
        if (!isset($this->fieldMapping[$entityName])) {
            throw new \InvalidArgumentException(sprintf('The class %s cannot be found', $entityName));
        }

        $repository = $this->managerRegistry
            ->getManagerForClass($this->fieldMapping[$entityName])
            ->getRepository($this->fieldMapping[$entityName]);

        $ids = [];
        foreach ($codes as $code) {
            $criterias = ['code' => $code];
            if (null !== $attribute) {
                $criterias['attribute'] = $attribute->getId();
            }

            //TODO: do not hydrate them, use a scalar result
            $entity = $repository->findOneBy($criterias);

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
