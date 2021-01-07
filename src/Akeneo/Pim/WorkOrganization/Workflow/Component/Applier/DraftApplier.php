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

namespace Akeneo\Pim\WorkOrganization\Workflow\Component\Applier;

use Akeneo\Pim\Enrichment\Component\Product\Model\EntityWithValuesInterface;
use Akeneo\Pim\WorkOrganization\Workflow\Component\Event\EntityWithValuesDraftEvents;
use Akeneo\Pim\WorkOrganization\Workflow\Component\Model\EntityWithValuesDraftInterface;
use Akeneo\Tool\Component\StorageUtils\Repository\IdentifiableObjectRepositoryInterface;
use Akeneo\Tool\Component\StorageUtils\Updater\PropertySetterInterface;
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
        $this->dispatcher->dispatch(new GenericEvent($entityWithValuesDraft), EntityWithValuesDraftEvents::PRE_APPLY);

        $changes = $entityWithValuesDraft->getChanges();
        if (!isset($changes['values'])) {
            return;
        }

        $this->applyValues($entityWithValues, $changes['values']);

        $this->dispatcher->dispatch(new GenericEvent($entityWithValuesDraft), EntityWithValuesDraftEvents::POST_APPLY);
    }

    /**
     * {@inheritdoc}
     */
    public function applyToReviewChanges(
        EntityWithValuesInterface $entityWithValues,
        EntityWithValuesDraftInterface $entityWithValuesDraft
    ): void {
        $this->dispatcher->dispatch(new GenericEvent($entityWithValuesDraft), EntityWithValuesDraftEvents::PRE_APPLY);

        $changes = $entityWithValuesDraft->getChangesToReview();
        if (!isset($changes['values'])) {
            return;
        }

        $this->applyValues($entityWithValues, $changes['values']);

        $this->dispatcher->dispatch(new GenericEvent($entityWithValuesDraft), EntityWithValuesDraftEvents::POST_APPLY);
    }

    protected function applyValues(EntityWithValuesInterface $entityWithValues, array $changesValues): void
    {
        foreach ($changesValues as $code => $values) {
            if ($this->attributeExists((string) $code)) {
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
