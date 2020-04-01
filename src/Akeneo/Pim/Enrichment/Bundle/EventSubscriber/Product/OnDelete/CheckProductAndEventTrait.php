<?php

declare(strict_types=1);

namespace Akeneo\Pim\Enrichment\Bundle\EventSubscriber\Product\OnDelete;

use Akeneo\Pim\Enrichment\Component\Product\Model\ProductInterface;
use Akeneo\Pim\WorkOrganization\Workflow\Component\Model\PublishedProduct;
use Akeneo\Tool\Component\StorageUtils\Event\RemoveEvent;

/**
 * Make product subscribers able to verify their products are real products or product models
 *
 * @author    Grégoire HUBERT <gregoire.hubert@akeneo.com>
 * @copyright 2020 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
trait CheckProductAndEventTrait
{
    private function checkProduct($product): bool
    {
        return $product instanceof ProductInterface
            // TODO TIP-987 Remove this when decoupling PublishedProduct from Enrichment
            && !$product instanceof PublishedProduct;
    }

    private function checkEvent(RemoveEvent $event): bool
    {
        return $event->hasArgument('unitary')
            && true === $event->getArgument('unitary');
    }
}
