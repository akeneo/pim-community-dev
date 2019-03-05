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

namespace PimEnterprise\Bundle\ProductAssetBundle\EventSubscriber;

use Akeneo\Bundle\BatchBundle\Launcher\JobLauncherInterface;
use Akeneo\Component\StorageUtils\Repository\IdentifiableObjectRepositoryInterface;
use Akeneo\Component\StorageUtils\StorageEvents;
use Doctrine\Common\Util\Debug;
use Pim\Component\Catalog\Query\Filter\Operators;
use Pim\Component\Catalog\Query\ProductQueryBuilderFactoryInterface;
use Pim\Component\Catalog\Repository\AttributeRepositoryInterface;
use PimEnterprise\Bundle\ProductAssetBundle\AttributeType\AttributeTypes;
use PimEnterprise\Bundle\ProductAssetBundle\Event\AssetEvent;
use PimEnterprise\Bundle\ProductAssetBundle\Event\VariationHasBeenCreated;
use PimEnterprise\Bundle\ProductAssetBundle\Event\VariationHasBeenDeleted;
use PimEnterprise\Component\ProductAsset\Completeness\CompletenessRemoverInterface;
use PimEnterprise\Component\ProductAsset\Model\AssetInterface;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
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
            AssetEvent::POST_UPLOAD_FILES => 'onFilesUploaded',
            VariationHasBeenDeleted::VARIATION_HAS_BEEN_DELETED => 'onVariationDeleted',
            VariationHasBeenCreated::VARIATION_HAS_BEEN_CREATED => 'onVariationCreated',
        ];
    }

    public function onFilesUploaded(AssetEvent $event): void
    {
        $assets = is_array($event->getSubject()) ? $event->getSubject() : [$event->getSubject()];

        $this->computeCompletenessForAssets($assets);
    }

    public function onVariationDeleted(VariationHasBeenDeleted $event): void
    {
        $this->computeCompletenessForAssets([$event->getAsset()]);
    }

    public function onVariationCreated(VariationHasBeenCreated $event): void
    {
        $this->computeCompletenessForAssets([$event->getAsset()]);
    }

    /**
     * @param array $assets
     */
    private function computeCompletenessForAssets(array $assets): void
    {
        $computeCompletenessJobInstance = $this->jobInstanceRepository->findOneByIdentifier(self::JOB_CODE);

        // Here for backward compatibility if the migration creating the job has not been run.
        // TO REMOVE ON MASTER
        if (null === $computeCompletenessJobInstance) {
            foreach ($assets as $asset) {
                $this->completenessRemover->removeForAsset($asset);
            }

            return;
        }

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
