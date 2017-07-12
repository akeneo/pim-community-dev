<?php

namespace Pim\Bundle\CatalogBundle\Doctrine\Common\Filter;

use Doctrine\Common\Persistence\ObjectManager;
use Pim\Component\Catalog\Exception\ObjectNotFoundException;
use Pim\Component\Catalog\Model\AttributeInterface;

/**
 * Object id resolver
 *
 * @author    Julien Sanchez <julien@akeneo.com>
 * @copyright 2014 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 *
 * @deprecated will be removed in 1.8. The filters will only handle identifiers. No more IDs.
 * @deprecated Which means we won't have to convert IDs <=> codes.
 */
class ObjectIdResolver implements ObjectIdResolverInterface
{
    /** @var ObjectManager */
    protected $objectManager;

    /** @var array */
    protected $fieldMapping = [];

    /**
     * @param ObjectManager $objectManager
     */
    public function __construct(ObjectManager $objectManager)
    {
        $this->objectManager = $objectManager;
    }

    /**
     * {@inheritdoc}
     */
    public function getIdsFromCodes($entityName, array $codes, AttributeInterface $attribute = null)
    {
        if (!isset($this->fieldMapping[$entityName])) {
            throw new \InvalidArgumentException(sprintf('The class %s cannot be found', $entityName));
        }

        $repository = $this->objectManager->getRepository($this->fieldMapping[$entityName]);

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
