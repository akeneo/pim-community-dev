<?php

declare(strict_types=1);

namespace Akeneo\Pim\Enrichment\Bundle\EventSubscriber;

use Akeneo\Pim\Enrichment\Bundle\Product\ComputeAndPersistProductCompletenesses;
use Akeneo\Pim\Enrichment\Component\Product\Model\ProductInterface;
use Akeneo\Tool\Component\StorageUtils\StorageEvents;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\EventDispatcher\GenericEvent;

/**
 * This subscriber should be executed before the subscriber to index the products In ES, as the completeness is also
 * indexed. This subscriber has a big priority for that reason.
 *
 * @author    Mathias METAYER <mathias.metayer@akeneo.com>
 * @copyright 2019 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
final class ComputeAndPersistProductCompletenessSubscriber implements EventSubscriberInterface
{
    /** @var ComputeAndPersistProductCompletenesses */
    private $computeAndPersistProductCompletenesses;

    public function __construct(ComputeAndPersistProductCompletenesses $computeAndPersistProductCompletenesses)
    {
        $this->computeAndPersistProductCompletenesses = $computeAndPersistProductCompletenesses;
    }

    /**
     * {@inheritdoc}
     */
    public static function getSubscribedEvents(): array
    {
        return [
            StorageEvents::POST_SAVE => ['computeProductCompleteness', 320],
        ];
    }

    public function computeProductCompleteness(GenericEvent $event): void
    {
        $product = $event->getSubject();

        if (
            !$product instanceof ProductInterface
            // TODO TIP-987 Remove this when decoupling PublishedProduct from Enrichment
            || get_class($product) === 'Akeneo\Pim\WorkOrganization\Workflow\Component\Model\PublishedProduct'
        ) {
            return;
        }

        $this->computeAndPersistProductCompletenesses->fromProductIdentifier($product->getIdentifier());
    }
}
