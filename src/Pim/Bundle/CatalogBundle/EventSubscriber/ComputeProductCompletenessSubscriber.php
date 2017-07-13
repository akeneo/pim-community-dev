<?php

namespace Pim\Bundle\CatalogBundle\EventSubscriber;

use Akeneo\Component\StorageUtils\StorageEvents;
use Pim\Component\Catalog\Manager\CompletenessManager;
use Pim\Component\Catalog\Model\ProductInterface;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\EventDispatcher\GenericEvent;

/**
 * Compute the product completeness on pre save event
 *
 * @author    Arnaud Langlade <arnaud.langlade@akeneo.com>
 * @copyright 2017 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class ComputeProductCompletenessSubscriber implements EventSubscriberInterface
{
    /** @var CompletenessManager */
    private $completenessManager;

    /**
     * @param CompletenessManager $completenessManager
     */
    public function __construct(CompletenessManager $completenessManager)
    {
        $this->completenessManager = $completenessManager;
    }

    /**
     * {@inheritdoc}
     */
    public static function getSubscribedEvents(): array
    {
        return [StorageEvents::PRE_SAVE => 'computeProductCompleteness'];
    }

    /**
     * @param GenericEvent $event
     */
    public function computeProductCompleteness(GenericEvent $event)
    {
        $product = $event->getSubject();
        if (!$product instanceof ProductInterface) {
            return;
        }

        $this->completenessManager->schedule($product);
        $this->completenessManager->generateMissingForProduct($product);
    }
}
