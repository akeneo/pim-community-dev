<?php

namespace PimEnterprise\Bundle\WorkflowBundle\Form\Comparator;

use Pim\Bundle\CatalogBundle\Model\AbstractProductValue;

/**
 * Comparator which calculate change set for collections of options
 *
 * @see PimEnterprise\Bundle\WorkflowBundle\Form\ComparatorInterface
 *
 * @author    Gildas Quemener <gildas@akeneo.com>
 * @copyright 2014 Akeneo SAS (http://www.akeneo.com)
 */
class OptionsComparator implements ComparatorInterface
{
    /**
     * {@inheritdoc}
     */
    public function supportsComparison(AbstractProductValue $value)
    {
        return 'pim_catalog_multiselect' === $value->getAttribute()->getAttributeType();
    }

    /**
     * {@inheritdoc}
     */
    public function getChanges(AbstractProductValue $value, $submittedData)
    {
        if (!isset($submittedData['options'])) {
            return;
        }

        $options = $value->getOptions();
        $getIds = function ($option) {
            return $option->getId();
        };

        $options = $options->map($getIds)->toArray();
        sort($options);

        $submittedOptions = explode(',', $submittedData['options']);
        sort($submittedOptions);

        if ($options != $submittedOptions) {
            return [
                'id' => $submittedData['id'],
                'options' => join(',', $submittedOptions),
            ];
        }
    }
}
