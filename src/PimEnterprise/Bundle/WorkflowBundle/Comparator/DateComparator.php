<?php

namespace PimEnterprise\Bundle\WorkflowBundle\Comparator;

use Pim\Bundle\CatalogBundle\Model\AbstractProductValue;

/**
 * Comparator which calculate change set for dates
 *
 * @author    Gildas Quemener <gildas@akeneo.com>
 * @copyright 2014 Akeneo SAS (http://www.akeneo.com)
 *
 * @see       PimEnterprise\Bundle\WorkflowBundle\Form\ComparatorInterface
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
        if (!isset($submittedData['date'])) {
            return;
        }

        $date = $value->getDate();
        if ($date instanceof \DateTime && $date->format('Y-m-d') === $submittedData['date']) {
            return;
        }

        if (!$date instanceof \DateTime && '' === $submittedData['date']) {
            return;
        }

        return [
            'date' => $submittedData['date'],
        ];
    }
}
