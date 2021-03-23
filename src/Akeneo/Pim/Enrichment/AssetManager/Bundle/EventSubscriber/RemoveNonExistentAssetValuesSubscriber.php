<?php

declare(strict_types=1);

/*
 * This file is part of the Akeneo PIM Enterprise Edition.
 *
 * (c) 2021 Akeneo SAS (http://www.akeneo.com)
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Akeneo\Pim\Enrichment\AssetManager\Bundle\EventSubscriber;

use Akeneo\AssetManager\Domain\Event\AssetDeletedEvent;
use Akeneo\AssetManager\Domain\Event\AssetsDeletedEvent;
use Akeneo\AssetManager\Domain\Model\Asset\AssetCode;
use Akeneo\Pim\Enrichment\AssetManager\Component\AttributeType\AssetCollectionType;
use Akeneo\Tool\Bundle\BatchBundle\Launcher\JobLauncherInterface;
use Akeneo\Tool\Component\Batch\Model\JobInstance;
use Akeneo\Tool\Component\Batch\Query\CreateJobInstanceInterface;
use Akeneo\Tool\Component\StorageUtils\Repository\IdentifiableObjectRepositoryInterface;
use Doctrine\DBAL\Connection;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;
use Webmozart\Assert\Assert;

class RemoveNonExistentAssetValuesSubscriber implements EventSubscriberInterface
{
    private const REMOVE_NON_EXISTENT_VALUES_JOB = 'remove_non_existing_product_values';

    /** @var TokenStorageInterface */
    private $tokenStorage;

    /** @var IdentifiableObjectRepositoryInterface */
    private $jobInstanceRepository;

    /** @var JobLauncherInterface */
    private $jobLauncher;

    /** @var CreateJobInstanceInterface */
    private $createJobInstance;

    /** @var Connection */
    private $connection;

    /** @var array */
    private $assetCollectionAttributes;

    public function __construct(
        TokenStorageInterface $tokenStorage,
        IdentifiableObjectRepositoryInterface $jobInstanceRepository,
        JobLauncherInterface $jobLauncher,
        CreateJobInstanceInterface $createJobInstance,
        Connection $connection
    ) {
        $this->tokenStorage = $tokenStorage;
        $this->jobInstanceRepository = $jobInstanceRepository;
        $this->jobLauncher = $jobLauncher;
        $this->createJobInstance = $createJobInstance;
        $this->connection = $connection;
    }

    public static function getSubscribedEvents(): array
    {
        return [
            AssetDeletedEvent::class => 'onAssetDeleted',
            AssetsDeletedEvent::class => 'onBulkAssetsDeleted',
        ];
    }

    public function onAssetDeleted(AssetDeletedEvent $event): void
    {
        $this->launchJob($event->getAssetFamilyIdentifier()->normalize(), [$event->getAssetCode()]);
    }

    public function onBulkAssetsDeleted(AssetsDeletedEvent $event): void
    {
        $this->launchJob($event->getAssetFamilyIdentifier()->normalize(), $event->getAssetCodes());
    }

    private function launchJob(string $assetFamilyIdentifier, array $assetCodes): void
    {
        Assert::allIsInstanceOf($assetCodes, AssetCode::class);

        $assetCollectionAttributes = $this->getAssetCollectionAttributesForAssetFamily($assetFamilyIdentifier);
        if ([] === $assetCollectionAttributes) {
            return;
        }

        $user = $this->tokenStorage->getToken()->getUser();
        $jobInstance = $this->getOrCreateJobInstance();

        foreach ($assetCollectionAttributes as $attributeCode) {
            $this->jobLauncher->launch(
                $jobInstance,
                $user,
                [
                    'attribute_code' => $attributeCode,
                    'attribute_options' => \array_map(
                        function (AssetCode $assetCode): string {
                            return $assetCode->normalize();
                        },
                        $assetCodes
                    ),
                ]
            );
        }
    }

    private function getOrCreateJobInstance(): JobInstance
    {
        return $this->jobInstanceRepository->findOneByIdentifier(self::REMOVE_NON_EXISTENT_VALUES_JOB);
    }

    private function getAssetCollectionAttributesForAssetFamily(string $assetFamily): array
    {
        if (null === $this->assetCollectionAttributes) {
            $this->assetCollectionAttributes = [];

            $rows = $this->connection->executeQuery(
                'SELECT code, properties FROM pim_catalog_attribute WHERE attribute_type = :type',
                [
                    'type' => AssetCollectionType::ASSET_COLLECTION,
                ]
            )->fetchAll();

            foreach ($rows as $row) {
                $properties = \unserialize($row['properties']);
                $referenceDataName = $properties['reference_data_name'] ?? null;
                if (null !== $referenceDataName) {
                    $this->assetCollectionAttributes[$referenceDataName][] = $row['code'];
                }
            }
        }

        return $this->assetCollectionAttributes[$assetFamily] ?? [];
    }
}
