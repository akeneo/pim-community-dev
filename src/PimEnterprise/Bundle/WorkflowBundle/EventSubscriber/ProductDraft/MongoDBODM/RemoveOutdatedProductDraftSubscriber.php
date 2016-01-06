<?php

/*
 * This file is part of the Akeneo PIM Enterprise Edition.
 *
 * (c) 2015 Akeneo SAS (http://www.akeneo.com)
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace PimEnterprise\Bundle\WorkflowBundle\EventSubscriber\ProductDraft\MongoDBODM;

use Akeneo\Component\StorageUtils\Event\RemoveEvent;
use Akeneo\Component\StorageUtils\Remover\BulkRemoverInterface;
use Akeneo\Component\StorageUtils\StorageEvents;
use Pim\Bundle\CatalogBundle\Event\ProductEvents;
use Pim\Bundle\CatalogBundle\Model\ProductInterface;
use PimEnterprise\Bundle\WorkflowBundle\Repository\ProductDraftRepositoryInterface;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\EventDispatcher\GenericEvent;

/**
 * Remove drafts on product remove event
 *
 * @author Marie Bochu <marie.bochu@akeneo.com>
 */
class RemoveOutdatedProductDraftSubscriber implements EventSubscriberInterface
{
    /** @var ProductDraftRepositoryInterface */
    protected $productDraftRepo;

    /** @var BulkRemoverInterface */
    protected $remover;

    /**
     * @param ProductDraftRepositoryInterface $productDraftRepo
     * @param BulkRemoverInterface            $remover
     */
    public function __construct(ProductDraftRepositoryInterface $productDraftRepo, BulkRemoverInterface $remover)
    {
        $this->productDraftRepo = $productDraftRepo;
        $this->remover          = $remover;
    }

    /**
     * {@inheritdoc}
     */
    public static function getSubscribedEvents()
    {
        return [
            StorageEvents::POST_REMOVE      => 'removeDraftsByProduct',
            ProductEvents::POST_MASS_REMOVE => 'removeDraftsByProducts'
        ];
    }

    /**
     * Remove product drafts by a product
     *
     * @param RemoveEvent $event
     */
    public function removeDraftsByProduct(RemoveEvent $event)
    {
        $subject = $event->getSubject();

        if (!$subject instanceof ProductInterface) {
            return;
        }

        $this->removeDrafts($subject->getId());
    }

    /**
     * Remove product drafts by products
     *
     * @param GenericEvent $event
     */
    public function removeDraftsByProducts(GenericEvent $event)
    {
        $subjects = $event->getSubject();

        if (empty($subjects)) {
            return;
        }

        foreach ($subjects as $subject) {
            $this->removeDrafts($subject);
        }
    }

    /**
     * Remove drafts associated to a product
     *
     * @param string $id
     */
    protected function removeDrafts($id)
    {
        $drafts = $this->productDraftRepo->findBy(['product.$id' => new \MongoId($id)]);
        if (!empty($drafts)) {
            $this->remover->removeAll($drafts);
        }
    }
}
