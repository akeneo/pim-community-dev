<?php

namespace PimEnterprise\Bundle\WorkflowBundle\Form\Comparator;

use Pim\Bundle\CatalogBundle\Model\AbstractProductValue;

/**
 * Comparator which calculate change set for Dates
 *
 * @see PimEnterprise\Bundle\WorkflowBundle\Form\ComparatorInterface
 *
 * @author    Gildas Quemener <gildas@akeneo.com>
 * @copyright 2014 Akeneo SAS (http://www.akeneo.com)
 */
class DateComparator implements ComparatorInterface
{
    /**
     * {@inheritdoc}
     */
    public function supportsComparison(AbstractProductValue $value)
    {
        return 'pim_catalog_date' === $value->getAttribute()->getAttributeType();
    }

    /**
     * {@inheritdoc}
     */
    public function getChanges(AbstractProductValue $value, $submittedData)
    {
        $date = $value->getDate();
        if (($date instanceof \DateTime && $date->format('Y-m-d') !== $submittedData['date'])
            || (!$date instanceof \DateTime && '' !== $submittedData['date'])
        ) {
            return [
                'id' => $submittedData['id'],
                'date' => $submittedData['date'],
            ];
        }
    }
}
