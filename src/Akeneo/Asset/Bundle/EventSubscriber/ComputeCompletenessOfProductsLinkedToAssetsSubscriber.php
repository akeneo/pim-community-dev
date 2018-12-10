<?php

declare(strict_types=1);

/*
 * This file is part of the Akeneo PIM Enterprise Edition.
 *
 * (c) 2018 Akeneo SAS (http://www.akeneo.com)
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Akeneo\Asset\Bundle\EventSubscriber;

use Akeneo\Asset\Bundle\AttributeType\AttributeTypes;
use Akeneo\Asset\Component\Model\AssetInterface;
use Akeneo\Asset\Component\Model\ReferenceInterface;
use Akeneo\Pim\Enrichment\Asset\Component\Completeness\CompletenessRemoverInterface;
use Akeneo\Pim\Enrichment\Component\Product\Query\Filter\Operators;
use Akeneo\Pim\Enrichment\Component\Product\Query\ProductQueryBuilderFactoryInterface;
use Akeneo\Pim\Structure\Component\Repository\AttributeRepositoryInterface;
use Akeneo\Tool\Bundle\BatchBundle\Launcher\JobLauncherInterface;
use Akeneo\Tool\Component\StorageUtils\Repository\IdentifiableObjectRepositoryInterface;
use Akeneo\Tool\Component\StorageUtils\StorageEvents;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\EventDispatcher\GenericEvent;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;

/**
 * Launches the job recomputing the completeness of products when an asset or asset reference is updated.
 *
 * @author Yohan Blain <yohan.blain@akeneo.com>
 */
final class ComputeCompletenessOfProductsLinkedToAssetsSubscriber implements EventSubscriberInterface
{
    private const JOB_CODE = 'compute_completeness_of_products_linked_to_assets';

    /** @var IdentifiableObjectRepositoryInterface */
    private $jobInstanceRepository;

    /** @var AttributeRepositoryInterface */
    private $attributeRepository;

    /** @var ProductQueryBuilderFactoryInterface */
    private $productQueryBuilderFactory;

    /** @var JobLauncherInterface */
    private $jobLauncher;

    /** @var TokenStorageInterface */
    private $tokenStorage;

    /** @var CompletenessRemoverInterface */
    private $completenessRemover;

    /**
     * @param IdentifiableObjectRepositoryInterface $jobInstanceRepository
     * @param AttributeRepositoryInterface $attributeRepository
     * @param ProductQueryBuilderFactoryInterface $productQueryBuilderFactory
     * @param JobLauncherInterface $jobLauncher
     * @param TokenStorageInterface $tokenStorage
     * @param CompletenessRemoverInterface $completenessRemover
     */
    public function __construct(
        IdentifiableObjectRepositoryInterface $jobInstanceRepository,
        AttributeRepositoryInterface $attributeRepository,
        ProductQueryBuilderFactoryInterface $productQueryBuilderFactory,
        JobLauncherInterface $jobLauncher,
        TokenStorageInterface $tokenStorage,
        CompletenessRemoverInterface $completenessRemover
    ) {
        $this->jobInstanceRepository = $jobInstanceRepository;
        $this->attributeRepository = $attributeRepository;
        $this->productQueryBuilderFactory = $productQueryBuilderFactory;
        $this->jobLauncher = $jobLauncher;
        $this->tokenStorage = $tokenStorage;
        $this->completenessRemover = $completenessRemover;
    }

    /**
     * {@inheritdoc}
     */
    public static function getSubscribedEvents(): array
    {
        return [
            StorageEvents::POST_SAVE => 'computeCompletenessOfProductsLinkedToAsset',
            StorageEvents::POST_SAVE_ALL => 'computeCompletenessOfProductsLinkedToAssets',
        ];
    }

    /**
     * @param GenericEvent $event
     */
    public function computeCompletenessOfProductsLinkedToAsset(GenericEvent $event): void
    {
        if ($event->hasArgument('unitary') && !$event->getArgument('unitary')) {
            return;
        }

        if (!$event->getSubject() instanceof AssetInterface
            && !$event->getSubject() instanceof ReferenceInterface
        ) {
            return;
        }

        if ($event->getSubject() instanceof AssetInterface) {
            $asset = $event->getSubject();
        } else {
            $asset = $event->getSubject()->getAsset();
        }

        $this->computeCompletenessForAssets([$asset]);
    }

    /**
     * @param GenericEvent $event
     */
    public function computeCompletenessOfProductsLinkedToAssets(GenericEvent $event): void
    {
        $assets = [];

        foreach ($event->getSubject() as $item) {
            if (!$item instanceof AssetInterface
                && !$item instanceof ReferenceInterface
            ) {
                continue;
            }

            if ($item instanceof AssetInterface) {
                $assets[] = $item;
            } else {
                $assets[] = $item->getAsset();
            }
        }

        if (empty($assets)) {
            return;
        }

        $this->computeCompletenessForAssets($assets);
    }

    /**
     * @param array $assets
     */
    private function computeCompletenessForAssets(array $assets): void
    {
        $computeCompletenessJobInstance = $this->jobInstanceRepository->findOneByIdentifier(self::JOB_CODE);

        $assetCodes = array_map(
            function (AssetInterface $asset) {
                return $asset->getCode();
            },
            $assets
        );

        if (!$this->areThereProductsLinkedToAssets($assetCodes)) {
            return;
        }

        $user = $this->tokenStorage->getToken()->getUser();
        $configuration = ['asset_codes' => $assetCodes];

        $this->jobLauncher->launch($computeCompletenessJobInstance, $user, $configuration);
    }

    /**
     * Checks whether there are products linked to specific assets
     *
     * @param array $assetCodes
     *
     * @return bool
     */
    private function areThereProductsLinkedToAssets(array $assetCodes): bool
    {
        $assetAttributeCodes = $this->attributeRepository->getAttributeCodesByType(AttributeTypes::ASSETS_COLLECTION);
        foreach ($assetAttributeCodes as $attributeCode) {
            $pqb = $this->productQueryBuilderFactory->create();
            $pqb->addFilter($attributeCode, Operators::IN_LIST, $assetCodes);

            if ($pqb->execute()->count() > 0) {
                return true;
            }
        }

        return false;
    }
}
