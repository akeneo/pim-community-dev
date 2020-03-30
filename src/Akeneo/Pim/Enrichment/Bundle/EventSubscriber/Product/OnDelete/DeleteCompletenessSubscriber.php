<?php

declare(strict_types=1);

namespace Akeneo\Pim\Enrichment\Bundle\EventSubscriber\Product\OnDelete;

use Akeneo\Pim\WorkOrganization\Workflow\Component\Model\PublishedProduct;

/**
 * Removes completness information related to deleted product.
 *
 * @author    Grégoire HUBERT <gregoire.hubert@akeneo.com>
 * @copyright 2020 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
final class DeleteCompletenessSubscriber implements EventSubscriberInterface
{
    use CheckProductAndEventTrait;

    /** @var CompletenessRemover */
    private $completenessRemover;

    public function __construct(CompletenessRemover $completenessRemover)
    {
        $this->completenessRemover = $completenessRemover;
    }

    /**
     * {@inheritdoc}
     */
    public static function getSubscribedEvents() : array
    {
        return [
            StorageEvents::POST_REMOVE   => ['deleteProduct'],
            StorageEvents::POST_REMOVE_ALL => ['deleteAllProducts'],
        ];
    }

    public function deleteProduct(RemoveEvent $event) : void
    {
        $product = $event->getSubject();
        if (!$this->checkProduct($product) || !$this->checkEvent($product)) {
            return;
        }

        $this->completenessRemover->deleteOneProduct($product->getId());
    }

    public function deleteAllProducts(RemoveEvent $event)
    {
        $products = $event->getSubject();
        if (!is_array($products) || !is_array($event->getSubjectId())) {
            return;
        }
        $products = array_filter($products, function ($product) {
            return $this->checkProduct($product);
        });
        if (!empty($products)) {
            $this->completenessRemover->deleteProducts($products);
        }
    }
}
