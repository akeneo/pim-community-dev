<?php

namespace Akeneo\Pim\Enrichment\Component\Product\Validator;

use Akeneo\Pim\Enrichment\Component\Product\Model\EntityWithValuesInterface;
use Akeneo\Pim\Enrichment\Component\Product\Model\ProductInterface;
use Akeneo\Pim\Enrichment\Component\Product\Model\ProductModelInterface;
use Akeneo\Pim\Enrichment\Component\Product\Model\ValueInterface;

/**
 * Contains the state of the unique value for a product, due to EAV model we cannot ensure it via constraints on
 * database, we use this state to deal with bulk update and validation
 *
 * @author    Nicolas Dupont <nicolas@akeneo.com>
 * @copyright 2015 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class UniqueValuesSet
{
    /** @var array allows to keep the state */
    protected $uniqueValues;

    /**
     * Constructor
     */
    public function __construct()
    {
        $this->uniqueValues = [];
    }

    /**
     * Reset the set
     */
    public function reset()
    {
        $this->uniqueValues = [];
    }

    /**
     * Return true if value has been added, else if value already exists inside the set
     */
    public function addValue(ValueInterface $value, EntityWithValuesInterface $entity): bool
    {
        $identifier = $this->getEntityId($entity);
        $data = strtolower($value->__toString());
        $attributeCode = $value->getAttributeCode();

        if (isset($this->uniqueValues[$attributeCode][$data])) {
            $storedIdentifier = $this->uniqueValues[$attributeCode][$data];
            if ($storedIdentifier !== $identifier) {
                return false;
            }
        }

        if (!isset($this->uniqueValues[$attributeCode])) {
            $this->uniqueValues[$attributeCode] = [];
        }

        if (!isset($this->uniqueValues[$attributeCode][$data])) {
            $this->uniqueValues[$attributeCode][$data] = $identifier;
        }

        return true;
    }

    public function getUniqueValues(): array
    {
        return $this->uniqueValues;
    }

    /**
     * spl_object_hash for new entity and id when entity exists
     */
    protected function getEntityId(EntityWithValuesInterface $entity): string
    {
        if ($entity instanceof ProductInterface || $entity instanceof ProductModelInterface) {
            if (null !== $entity->getCreated()) {
                return $entity instanceof ProductInterface && get_class($entity) !== 'Akeneo\Pim\WorkOrganization\Workflow\Component\Model\PublishedProduct'
                    ? $entity->getUuid()->toString()
                    : $entity->getId();
            }
        }

        return spl_object_hash($entity);
    }
}
