<?php

namespace Akeneo\Pim\Enrichment\Component\Product\Query\Sorter;

use Akeneo\Pim\Structure\Component\Model\AttributeInterface;

/**
 * Aims to register and retrieve sorters useable on product query builder
 *
 * @author    Nicolas Dupont <nicolas@akeneo.com>
 * @copyright 2014 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class SorterRegistry implements SorterRegistryInterface
{
    /** @var AttributeSorterInterface[] priorized attribute sorters */
    protected $attributeSorters = [];

    /** @var FieldSorterInterface[] priorized field sorters */
    protected $fieldSorters = [];

    /**
     * {@inheritdoc}
     */
    public function register(SorterInterface $sorter)
    {
        if ($sorter instanceof FieldSorterInterface) {
            $this->fieldSorters[] = $sorter;
        }
        if ($sorter instanceof AttributeSorterInterface) {
            $this->attributeSorters[] = $sorter;
        }
    }

    /**
     * {@inheritdoc}
     */
    public function getFieldSorter($field)
    {
        foreach ($this->fieldSorters as $sorter) {
            if ($sorter->supportsField($field)) {
                return $sorter;
            }
        }

        return null;
    }

    /**
     * {@inheritdoc}
     */
    public function getAttributeSorter(AttributeInterface $attribute)
    {
        foreach ($this->attributeSorters as $sorter) {
            if ($sorter->supportsAttribute($attribute)) {
                return $sorter;
            }
        }

        return null;
    }
}
