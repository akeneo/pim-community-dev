<?php

/*
 * This file is part of the Akeneo PIM Enterprise Edition.
 *
 * (c) 2015 Akeneo SAS (http://www.akeneo.com)
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace PimEnterprise\Component\Workflow\Applier;

use Akeneo\Component\StorageUtils\Repository\IdentifiableObjectRepositoryInterface;
use Akeneo\Component\StorageUtils\Updater\PropertySetterInterface;
use Pim\Component\Catalog\Model\ProductInterface;
use PimEnterprise\Component\Workflow\Event\ProductDraftEvents;
use PimEnterprise\Component\Workflow\Model\ProductDraftInterface;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use Symfony\Component\EventDispatcher\GenericEvent;

/**
 * Apply a draft
 *
 * @author Marie Bochu <marie.bochu@akeneo.com>
 */
class ProductDraftApplier implements ProductDraftApplierInterface
{
    /** @var PropertySetterInterface */
    protected $propertySetter;

    /** @var EventDispatcherInterface */
    protected $dispatcher;

    /** @var IdentifiableObjectRepositoryInterface */
    protected $attributeRepository;

    /**
     * @param PropertySetterInterface               $propertySetter
     * @param EventDispatcherInterface              $dispatcher
     * @param IdentifiableObjectRepositoryInterface $attributeRepository
     */
    public function __construct(
        PropertySetterInterface $propertySetter,
        EventDispatcherInterface $dispatcher,
        IdentifiableObjectRepositoryInterface $attributeRepository = null
    ) {
        $this->propertySetter = $propertySetter;
        $this->dispatcher = $dispatcher;
        $this->attributeRepository = $attributeRepository;
    }

    /**
     * {@inheritdoc}
     */
    public function applyAllChanges(ProductInterface $product, ProductDraftInterface $productDraft)
    {
        $this->dispatcher->dispatch(ProductDraftEvents::PRE_APPLY, new GenericEvent($productDraft));

        $changes = $productDraft->getChanges();
        if (!isset($changes['values'])) {
            return;
        }

        $this->applyValues($product, $changes['values']);

        $this->dispatcher->dispatch(ProductDraftEvents::POST_APPLY, new GenericEvent($productDraft));
    }

    /**
     * {@inheritdoc}
     */
    public function applyToReviewChanges(ProductInterface $product, ProductDraftInterface $productDraft)
    {
        $this->dispatcher->dispatch(ProductDraftEvents::PRE_APPLY, new GenericEvent($productDraft));

        $changes = $productDraft->getChangesToReview();
        if (!isset($changes['values'])) {
            return;
        }

        $this->applyValues($product, $changes['values']);

        $this->dispatcher->dispatch(ProductDraftEvents::POST_APPLY, new GenericEvent($productDraft));
    }

    /**
     * @param ProductInterface $product
     * @param array            $changesValues
     */
    protected function applyValues(ProductInterface $product, array $changesValues)
    {
        foreach ($changesValues as $code => $values) {
            if ($this->attributeExists($code)) {
                foreach ($values as $value) {
                    $this->propertySetter->setData(
                        $product,
                        $code,
                        $value['data'],
                        ['locale' => $value['locale'], 'scope' => $value['scope']]
                    );
                }
            }
        }
    }

    /**
     * Check if attribute still exists in db
     *
     * @param string $code
     *
     * @return bool
     */
    protected function attributeExists($code)
    {
        if (null === $this->attributeRepository) {
            return true;
        }

        return null !== $this->attributeRepository->findOneByIdentifier($code);
    }
}
