<?php

/*
 * This file is part of the Akeneo PIM Enterprise Edition.
 *
 * (c) 2014 Akeneo SAS (http://www.akeneo.com)
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace PimEnterprise\Bundle\ReferenceDataBundle\Workflow\Comparator;

use Pim\Bundle\CatalogBundle\Model\ProductValueInterface;
use Pim\Component\ReferenceData\MethodNameGuesser;
use PimEnterprise\Bundle\WorkflowBundle\Comparator\ComparatorInterface;

/**
 * Comparator which calculate change set for multi select reference data
 *
 * @author    Marie Bochu <marie.bochu@akeneo.com>
 * @copyright 2015 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 *
 * @see    PimEnterprise\Bundle\WorkflowBundle\Form\ComparatorInterface
 */
class ReferenceDataCollectionComparator implements ComparatorInterface
{
    /**
     * {@inheritdoc}
     */
    public function supportsComparison(ProductValueInterface $value)
    {
        return 'pim_reference_data_multiselect' === $value->getAttribute()->getAttributeType();
    }

    /**
     * {@inheritdoc}
     */
    public function getChanges(ProductValueInterface $value, $submittedData)
    {
        $referenceDataName = $value->getAttribute()->getReferenceDataName();

        if (null === $referenceDataName
            || !isset($submittedData[$referenceDataName])
            || empty($submittedData[$referenceDataName])
        ) {
            return;
        }

        $getter = MethodNameGuesser::guess('get', $referenceDataName);
        $referenceDataIds = $value->$getter();
        $getIds = function ($reference) {
            return $reference->getId();
        };

        $referenceDataIds = $referenceDataIds->map($getIds)->toArray();
        sort($referenceDataIds);

        $submittedReferenceDataIds = explode(',', $submittedData[$referenceDataName]);
        sort($submittedReferenceDataIds);

        if (
            count(array_diff($referenceDataIds, $submittedReferenceDataIds)) > 0 ||
            count(array_diff($submittedReferenceDataIds, $referenceDataIds)) > 0
        ) {
            return [
                $referenceDataName => implode(',', $submittedReferenceDataIds),
            ];
        }
    }
}
