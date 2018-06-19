<?php

declare(strict_types=1);

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
use Pim\Component\Catalog\Model\EntityWithValuesInterface;
use PimEnterprise\Component\Workflow\Event\EntityWithValuesDraftEvents;
use PimEnterprise\Component\Workflow\Model\EntityWithValuesDraftInterface;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use Symfony\Component\EventDispatcher\GenericEvent;

/**
 * Apply a draft
 *
 * @author Marie Bochu <marie.bochu@akeneo.com>
 */
class DraftApplier implements DraftApplierInterface
{
    /** @var PropertySetterInterface */
    protected $propertySetter;

    /** @var EventDispatcherInterface */
    protected $dispatcher;

    /** @var IdentifiableObjectRepositoryInterface */
    protected $attributeRepository;

    public function __construct(
        PropertySetterInterface $propertySetter,
        EventDispatcherInterface $dispatcher,
        IdentifiableObjectRepositoryInterface $attributeRepository
    ) {
        $this->propertySetter = $propertySetter;
        $this->dispatcher = $dispatcher;
        $this->attributeRepository = $attributeRepository;
    }

    /**
     * {@inheritdoc}
     */
    public function applyAllChanges(
        EntityWithValuesInterface $entityWithValues,
        EntityWithValuesDraftInterface $entityWithValuesDraft
    ): void {
        $this->dispatcher->dispatch(EntityWithValuesDraftEvents::PRE_APPLY, new GenericEvent($entityWithValuesDraft));

        $changes = $entityWithValuesDraft->getChanges();
        if (!isset($changes['values'])) {
            return;
        }

        $this->applyValues($entityWithValues, $changes['values']);

        $this->dispatcher->dispatch(EntityWithValuesDraftEvents::POST_APPLY, new GenericEvent($entityWithValuesDraft));
    }

    /**
     * {@inheritdoc}
     */
    public function applyToReviewChanges(
        EntityWithValuesInterface $entityWithValues,
        EntityWithValuesDraftInterface $entityWithValuesDraft
    ): void {
        $this->dispatcher->dispatch(EntityWithValuesDraftEvents::PRE_APPLY, new GenericEvent($entityWithValuesDraft));

        $changes = $entityWithValuesDraft->getChangesToReview();
        if (!isset($changes['values'])) {
            return;
        }

        $this->applyValues($entityWithValues, $changes['values']);

        $this->dispatcher->dispatch(EntityWithValuesDraftEvents::POST_APPLY, new GenericEvent($entityWithValuesDraft));
    }

    protected function applyValues(EntityWithValuesInterface $entityWithValues, array $changesValues): void
    {
        foreach ($changesValues as $code => $values) {
            if ($this->attributeExists($code)) {
                foreach ($values as $value) {
                    $this->propertySetter->setData(
                        $entityWithValues,
                        $code,
                        $value['data'],
                        ['locale' => $value['locale'], 'scope' => $value['scope']]
                    );
                }
            }
        }
    }

    protected function attributeExists(string $code): bool
    {
        return null !== $this->attributeRepository->findOneByIdentifier($code);
    }
}
