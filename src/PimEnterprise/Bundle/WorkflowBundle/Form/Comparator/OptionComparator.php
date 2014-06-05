<?php

namespace PimEnterprise\Bundle\WorkflowBundle\Form\Comparator;

use Pim\Bundle\CatalogBundle\Model\AbstractProductValue;

/**
 * Comparator which calculate change set for options
 *
 * @see PimEnterprise\Bundle\WorkflowBundle\Form\ComparatorInterface
 *
 * @author    Gildas Quemener <gildas@akeneo.com>
 * @copyright 2014 Akeneo SAS (http://www.akeneo.com)
 */
class OptionComparator extends AbstractComparator
{
    /**
     * {@inheritdoc}
     */
    public function supportsComparison(AbstractProductValue $value)
    {
        return 'pim_catalog_simpleselect' === $value->getAttribute()->getAttributeType();
    }

    /**
     * {@inheritdoc}
     */
    public function getDataChanges(AbstractProductValue $value, $submittedData)
    {
        if (!isset($submittedData['option'])) {
            return;
        }

        $option = $value->getOption();
        if (null === $option && empty($submittedData['option'])) {
            return;
        }

        if (!$option || $option->getId() != $submittedData['option']) {
            return [
                'option' => $submittedData['option'],
            ];
        }
    }
}
