<?php

declare(strict_types=1);

namespace Akeneo\Pim\Enrichment\Bundle\EventSubscriber\Product\OnDelete;

use Akeneo\Pim\Enrichment\Bundle\Storage\Sql\Product\CompletenessRemover;
use Akeneo\Pim\Enrichment\Component\Product\Model\ProductInterface;
use Akeneo\Tool\Component\StorageUtils\Event\RemoveEvent;
use Akeneo\Tool\Component\StorageUtils\StorageEvents;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

/**
 * Removes completness information related to deleted product.
 *
 * @author    Grégoire HUBERT <gregoire.hubert@akeneo.com>
 * @copyright 2020 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
final class DeleteCompletenessSubscriber implements EventSubscriberInterface
{
    /** @var CompletenessRemover */
    private $completenessRemover;

    public function __construct(CompletenessRemover $completenessRemover)
    {
        $this->completenessRemover = $completenessRemover;
    }

    /**
     * {@inheritdoc}
     */
    public static function getSubscribedEvents(): array
    {
        return [
            StorageEvents::POST_REMOVE      => 'deleteForOneProduct',
            StorageEvents::POST_REMOVE_ALL  => 'deleteForAllProducts',
        ];
    }

    public function deleteForOneProduct(RemoveEvent $event): void
    {
        $product = $event->getSubject();
        if (!$this->checkProduct($product) || !$this->checkEventUnitary($event)) {
            return;
        }

        $this->completenessRemover
            ->deleteForOneProduct($event->getSubjectId());
    }

    public function deleteForAllProducts(RemoveEvent $event)
    {
        $products = array_values($event->getSubject());
        $productIds = $event->getSubjectId();

        if (!is_array($products) || !is_array($productIds)) {
            return;
        }
        $productGoodIds =[];

        foreach ($products as $key => $product) {
            if ($this->checkProduct($product)) {
                $productGoodIds[] = $productIds[$key];
            }
        }

        if (!empty($productGoodIds)) {
            $this->completenessRemover
                ->deleteForProducts($productGoodIds);
        }
    }

    private function checkProduct($product): bool
    {
        return $product instanceof ProductInterface
            // TODO TIP-987 Remove this when decoupling PublishedProduct from Enrichment
            && get_class($product) != 'Akeneo\Pim\WorkOrganization\Workflow\Component\Model\PublishedProduct';
    }

    private function checkEventUnitary(RemoveEvent $event): bool
    {
        return $event->hasArgument('unitary')
            && true === $event->getArgument('unitary');
    }
}
