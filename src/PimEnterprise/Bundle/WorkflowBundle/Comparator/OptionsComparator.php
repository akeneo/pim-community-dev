<?php

/*
 * This file is part of the Akeneo PIM Enterprise Edition.
 *
 * (c) 2014 Akeneo SAS (http://www.akeneo.com)
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace PimEnterprise\Bundle\WorkflowBundle\Comparator;

use Pim\Bundle\CatalogBundle\Model\ProductValueInterface;

/**
 * Comparator which calculate change set for collections of options
 *
 * @author Gildas Quemener <gildas@akeneo.com>
 *
 * @see    PimEnterprise\Bundle\WorkflowBundle\Form\ComparatorInterface
 */
class OptionsComparator implements ComparatorInterface
{
    /**
     * {@inheritdoc}
     */
    public function supportsComparison(ProductValueInterface $value)
    {
        return 'pim_catalog_multiselect' === $value->getAttribute()->getAttributeType();
    }

    /**
     * {@inheritdoc}
     */
    public function getChanges(ProductValueInterface $value, $submittedData)
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
                'options' => join(',', $submittedOptions),
            ];
        }
    }
}
