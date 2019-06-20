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

namespace Akeneo\AssetManager\Application\Attribute\Subscribers;

use Akeneo\AssetManager\Application\Attribute\CreateAttribute\CreateAttributeHandler;
use Akeneo\AssetManager\Application\Attribute\CreateAttribute\CreateImageAttributeCommand;
use Akeneo\AssetManager\Application\Attribute\CreateAttribute\CreateTextAttributeCommand;
use Akeneo\AssetManager\Domain\Event\AssetFamilyCreatedEvent;
use Akeneo\AssetManager\Domain\Model\AssetFamily\AssetFamily;
use Akeneo\AssetManager\Domain\Model\AssetFamily\AssetFamilyIdentifier;
use Akeneo\AssetManager\Domain\Model\AssetFamily\AttributeAsImageReference;
use Akeneo\AssetManager\Domain\Model\AssetFamily\AttributeAsLabelReference;
use Akeneo\AssetManager\Domain\Repository\AssetFamilyRepositoryInterface;
use Akeneo\AssetManager\Domain\Repository\AttributeRepositoryInterface;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

/**
 * @author    Adrien Pétremann <adrien.petremann@akeneo.com>
 * @copyright 2019 Akeneo SAS (https://www.akeneo.com)
 */
class SetDefaultAttributesOnAssetFamilyCreationSubscriber implements EventSubscriberInterface
{
    /** @var CreateAttributeHandler */
    private $createAttributeHandler;

    /** @var AssetFamilyRepositoryInterface */
    private $assetFamilyRepository;

    /** @var AttributeRepositoryInterface */
    private $attributeRepository;

    public function __construct(
        AssetFamilyRepositoryInterface $assetFamilyRepository,
        AttributeRepositoryInterface $attributeRepository,
        CreateAttributeHandler $createAttributeHandler
    ) {
        $this->createAttributeHandler = $createAttributeHandler;
        $this->assetFamilyRepository = $assetFamilyRepository;
        $this->attributeRepository = $attributeRepository;
    }

    /**
     * {@inheritdoc}
     */
    public static function getSubscribedEvents()
    {
        return [
            AssetFamilyCreatedEvent::class => 'whenAssetFamilyCreated',
        ];
    }

    public function whenAssetFamilyCreated(AssetFamilyCreatedEvent $assetFamilyCreatedEvent): void
    {
        $assetFamilyIdentifier = $assetFamilyCreatedEvent->getAssetFamilyIdentifier();
        $this->createAttributeAsLabel($assetFamilyIdentifier);
        $this->createAttributeAsImage($assetFamilyIdentifier);
        $this->updateAssetFamilyWithAttributeAsLabelAndImage($assetFamilyIdentifier);
    }

    private function createAttributeAsLabel(AssetFamilyIdentifier $assetFamilyIdentifier): void
    {
        $createLabelAttributeCommand = new CreateTextAttributeCommand(
            $assetFamilyIdentifier->normalize(),
            AssetFamily::DEFAULT_ATTRIBUTE_AS_LABEL_CODE,
            [],
            false,
            false,
            true,
            null,
            false,
            false,
            'none',
            null
        );

        ($this->createAttributeHandler)($createLabelAttributeCommand);
    }

    private function createAttributeAsImage(AssetFamilyIdentifier $assetFamilyIdentifier): void
    {
        $createImageAttributeCommand = new CreateImageAttributeCommand(
            $assetFamilyIdentifier->normalize(),
            AssetFamily::DEFAULT_ATTRIBUTE_AS_IMAGE_CODE,
            [],
            false,
            false,
            false,
            null,
            []
        );

        ($this->createAttributeHandler)($createImageAttributeCommand);
    }

    private function updateAssetFamilyWithAttributeAsLabelAndImage(AssetFamilyIdentifier $assetFamilyIdentifier): void
    {
        $assetFamily = $this->assetFamilyRepository->getByIdentifier($assetFamilyIdentifier);

        $attributes = $this->attributeRepository->findByAssetFamily($assetFamilyIdentifier);
        foreach ($attributes as $attribute) {
            if (AssetFamily::DEFAULT_ATTRIBUTE_AS_LABEL_CODE === (string) $attribute->getCode()) {
                $assetFamily->updateAttributeAsLabelReference(
                    AttributeAsLabelReference::fromAttributeIdentifier($attribute->getIdentifier())
                );
            }
            if (AssetFamily::DEFAULT_ATTRIBUTE_AS_IMAGE_CODE === (string) $attribute->getCode()) {
                $assetFamily->updateAttributeAsImageReference(
                    AttributeAsImageReference::fromAttributeIdentifier($attribute->getIdentifier())
                );
            }
        }

        $this->assetFamilyRepository->update($assetFamily);
    }
}
