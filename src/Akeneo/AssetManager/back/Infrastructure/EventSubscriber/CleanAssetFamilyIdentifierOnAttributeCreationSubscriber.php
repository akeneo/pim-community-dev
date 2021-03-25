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

namespace Akeneo\AssetManager\Infrastructure\EventSubscriber;

use Akeneo\AssetManager\Domain\Model\AssetFamily\AssetFamilyIdentifier;
use Akeneo\AssetManager\Domain\Repository\AssetFamilyRepositoryInterface;
use Akeneo\Pim\Enrichment\AssetManager\Component\AttributeType\AssetCollectionType;
use Akeneo\Pim\Structure\Component\Model\AttributeInterface;
use Akeneo\Tool\Component\StorageUtils\StorageEvents;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\EventDispatcher\GenericEvent;

/**
 * On an asset collection attribute creation
 * Replace the identifier of the asset family with the exact value stored in database to avoid any letter case issue.
 * (See PIM-9735)
 */
final class CleanAssetFamilyIdentifierOnAttributeCreationSubscriber implements EventSubscriberInterface
{
    /** @var AssetFamilyRepositoryInterface */
    private $assetFamilyRepository;

    public function __construct(AssetFamilyRepositoryInterface $assetFamilyRepository)
    {
        $this->assetFamilyRepository = $assetFamilyRepository;
    }

    public static function getSubscribedEvents()
    {
        return [
            StorageEvents::PRE_SAVE => 'cleanAssetFamilyIdentifier',
        ];
    }

    public function cleanAssetFamilyIdentifier(GenericEvent $event)
    {
        $attribute = $event->getSubject();
        if (!$attribute instanceof AttributeInterface) {
            return;
        }

        if (null !== $attribute->getId() || AssetCollectionType::ASSET_COLLECTION !== $attribute->getType()) {
            return;
        }

        try {
            $assetFamilyIdentifier = AssetFamilyIdentifier::fromString(strval($attribute->getProperty('reference_data_name')));
        } catch (\InvalidArgumentException $e) {
            return;
        }

        $assetFamily = $this->assetFamilyRepository->getByIdentifier($assetFamilyIdentifier);

        if (null !== $assetFamily) {
            $attribute->setProperty('reference_data_name', strval($assetFamily->getIdentifier()));
        }
    }
}
