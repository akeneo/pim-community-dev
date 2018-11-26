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
use PimEnterprise\Component\ProductAsset\Completeness\CompletenessRemoverInterface;
use PimEnterprise\Component\ProductAsset\Model\AssetInterface;
use PimEnterprise\Component\ProductAsset\Model\ReferenceInterface;
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

    /** @var JobLauncherInterface */
    private $jobLauncher;

    /** @var TokenStorageInterface */
    private $tokenStorage;

    /** @var CompletenessRemoverInterface */
    private $completenessRemover;

    public function __construct(
        IdentifiableObjectRepositoryInterface $jobInstanceRepository,
        JobLauncherInterface $jobLauncher,
        TokenStorageInterface $tokenStorage,
        CompletenessRemoverInterface $completenessRemover
    ) {
        $this->jobInstanceRepository = $jobInstanceRepository;
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
            StorageEvents::POST_SAVE     => 'computeCompletenessOfProductsLinkedToAsset',
            StorageEvents::POST_SAVE_ALL => 'computeCompletenessOfProductsLinkedToAssets',
        ];
    }

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

    private function computeCompletenessForAssets(array $assets)
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

        $assetCodes = array_map(function (AssetInterface $asset) {
            return $asset->getCode();
        }, $assets);

        $user = $this->tokenStorage->getToken()->getUser();
        $configuration = ['asset_codes' => $assetCodes];

        $this->jobLauncher->launch($computeCompletenessJobInstance, $user, $configuration);
    }
}
