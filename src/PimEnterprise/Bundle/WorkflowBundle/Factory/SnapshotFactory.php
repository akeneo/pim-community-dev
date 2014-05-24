<?php

namespace PimEnterprise\Bundle\WorkflowBundle\Factory;

use Pim\Bundle\CatalogBundle\Model\ProductInterface;
use PimEnterprise\Bundle\WorkflowBundle\Model\Snapshot;
use PimEnterprise\Bundle\WorkflowBundle\Model\SnapshotValue;

/**
 * Snapshot factory
 *
 * @author    Gildas Quemener <gildas@akeneo.com>
 * @copyright 2014 Akeneo SAS (http://www.akeneo.com)
 */
class SnapshotFactory
{
    /**
     * Create and configure a snapshot instance
     *
     * @param ProductInterface $product
     *
     * @return Snapshot
     */
    public function createSnapshot(ProductInterface $product)
    {
        $snapshot = new Snapshot();
        foreach ($product->getValues() as $originalValue) {
            $snapshotValue = new SnapshotValue();
            $snapshotValue->setAttribute($originalValue->getAttribute());
            $snapshotValue->setLocale($originalValue->getLocale());
            $snapshotValue->setScope($originalValue->getScope());

            $originalData = $originalValue->getData();
            if (is_object($originalData)) {
                // TODO not deal with object for now
                continue;

                $copiedData = clone $originalData;
            } else {
                $copiedData = $originalData;
            }
            $snapshotValue->setData($copiedData);

            $snapshot->addValue($snapshotValue);
        }


/*        $proposal
            ->setProduct($product)
            ->setAuthor($username)
            ->setCreatedAt(new \DateTime())
            ->setChanges($changes);
*/

        return $snapshot;
    }
}
