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
 * Comparator which calculate change set for options
 *
 * @author Gildas Quemener <gildas@akeneo.com>
 *
 * @see    PimEnterprise\Bundle\WorkflowBundle\Form\ComparatorInterface
 */
class OptionComparator implements ComparatorInterface
{
    /**
     * {@inheritdoc}
     */
    public function supportsComparison(ProductValueInterface $value)
    {
        return 'pim_catalog_simpleselect' === $value->getAttribute()->getAttributeType();
    }

    /**
     * {@inheritdoc}
     */
    public function getChanges(ProductValueInterface $value, $submittedData)
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
