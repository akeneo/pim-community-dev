<?php

namespace Akeneo\Pim\Enrichment\Bundle\Doctrine\Common\Filter;

use Akeneo\Pim\Enrichment\Component\Product\Exception\ObjectNotFoundException;
use Akeneo\Pim\Structure\Component\Model\AttributeInterface;
use Doctrine\Common\Persistence\ObjectManager;

/**
 * Object code resolver
 *
 * @author    Julien Janvier <j.janvier@gmail.com>
 * @copyright 2016 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class ObjectCodeResolver
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
     * Get codes for the given ids
     *
     * @param string             $entityName
     * @param array              $ids
     * @param AttributeInterface $attribute
     *
     * @throws ObjectNotFoundException
     * @return array
     */
    public function getCodesFromIds($entityName, array $ids, AttributeInterface $attribute = null)
    {
        if (!isset($this->fieldMapping[$entityName])) {
            throw new \InvalidArgumentException(sprintf('The class %s cannot be found', $entityName));
        }

        $repository = $this->objectManager->getRepository($this->fieldMapping[$entityName]);

        $codes = [];
        foreach ($ids as $id) {
            $entity = $repository->findOneBy(['id' => $id]);

            if (null === $entity) {
                throw new ObjectNotFoundException(
                    sprintf('Object "%s" with id "%s" does not exist', $entityName, $id)
                );
            }

            $code = $entity->getCode();
            if (null !== $attribute) {
                $code = $attribute->getCode() . '.' . $code;
            }

            $codes[] = $code;
        }

        return $codes;
    }

    /**
     * Add a mapping to the field mapping
     *
     * @param string $entityName
     * @param string $className
     */
    public function addFieldMapping($entityName, $className)
    {
        $this->fieldMapping[$entityName] = $className;
    }
}
