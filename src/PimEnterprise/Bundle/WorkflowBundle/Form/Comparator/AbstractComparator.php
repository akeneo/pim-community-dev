<?php

namespace PimEnterprise\Bundle\WorkflowBundle\Form\Comparator;

use Pim\Bundle\CatalogBundle\Model\AbstractProductValue;

/**
 * Base comparator
 *
 * @author    Gildas Quemener <gildas@akeneo.com>
 * @copyright 2014 Akeneo SAS (http://www.akeneo.com)
 */
abstract class AbstractComparator implements ComparatorInterface
{
    /**
     * {@inheritdoc}
     */
    public function getChanges(AbstractProductValue $value, $submittedData)
    {
        if ($dataChanges = $this->getDataChanges($value, $submittedData)) {
            // Those information are then used to display changes in the proposition view
            $dataChanges['__context__'] = [
                'attribute_id' => $value->getAttribute()->getId(),
                'value_id' => $value->getId(),
                'scope' => $value->getScope(),
                'locale' => $value->getLocale(),
            ];

            return $dataChanges;
        }
    }

    /**
     *
     */
    abstract public function getDataChanges(AbstractProductValue $value, $submittedData);
}
