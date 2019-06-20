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

namespace Akeneo\ReferenceEntity\Application\Attribute\Subscribers;

use Akeneo\ReferenceEntity\Application\Attribute\CreateAttribute\CreateAttributeHandler;
use Akeneo\ReferenceEntity\Application\Attribute\CreateAttribute\CreateImageAttributeCommand;
use Akeneo\ReferenceEntity\Application\Attribute\CreateAttribute\CreateTextAttributeCommand;
use Akeneo\ReferenceEntity\Domain\Event\ReferenceEntityCreatedEvent;
use Akeneo\ReferenceEntity\Domain\Model\ReferenceEntity\AttributeAsImageReference;
use Akeneo\ReferenceEntity\Domain\Model\ReferenceEntity\AttributeAsLabelReference;
use Akeneo\ReferenceEntity\Domain\Model\ReferenceEntity\ReferenceEntity;
use Akeneo\ReferenceEntity\Domain\Model\ReferenceEntity\ReferenceEntityIdentifier;
use Akeneo\ReferenceEntity\Domain\Repository\AttributeRepositoryInterface;
use Akeneo\ReferenceEntity\Domain\Repository\ReferenceEntityRepositoryInterface;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

/**
 * @author    Adrien Pétremann <adrien.petremann@akeneo.com>
 * @copyright 2019 Akeneo SAS (https://www.akeneo.com)
 */
class SetDefaultAttributesOnReferenceEntityCreationSubscriber implements EventSubscriberInterface
{
    /** @var CreateAttributeHandler */
    private $createAttributeHandler;

    /** @var ReferenceEntityRepositoryInterface */
    private $referenceEntityRepository;

    /** @var AttributeRepositoryInterface */
    private $attributeRepository;

    public function __construct(
        ReferenceEntityRepositoryInterface $referenceEntityRepository,
        AttributeRepositoryInterface $attributeRepository,
        CreateAttributeHandler $createAttributeHandler
    ) {
        $this->createAttributeHandler = $createAttributeHandler;
        $this->referenceEntityRepository = $referenceEntityRepository;
        $this->attributeRepository = $attributeRepository;
    }

    /**
     * {@inheritdoc}
     */
    public static function getSubscribedEvents()
    {
        return [
            ReferenceEntityCreatedEvent::class => 'whenReferenceEntityCreated',
        ];
    }

    public function whenReferenceEntityCreated(ReferenceEntityCreatedEvent $referenceEntityCreatedEvent): void
    {
        $referenceEntityIdentifier = $referenceEntityCreatedEvent->getReferenceEntityIdentifier();
        $this->createAttributeAsLabel($referenceEntityIdentifier);
        $this->createAttributeAsImage($referenceEntityIdentifier);
        $this->updateReferenceEntityWithAttributeAsLabelAndImage($referenceEntityIdentifier);
    }

    private function createAttributeAsLabel(ReferenceEntityIdentifier $referenceEntityIdentifier): void
    {
        $createLabelAttributeCommand = new CreateTextAttributeCommand(
            $referenceEntityIdentifier->normalize(),
            ReferenceEntity::DEFAULT_ATTRIBUTE_AS_LABEL_CODE,
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

    private function createAttributeAsImage(ReferenceEntityIdentifier $referenceEntityIdentifier): void
    {
        $createImageAttributeCommand = new CreateImageAttributeCommand(
            $referenceEntityIdentifier->normalize(),
            ReferenceEntity::DEFAULT_ATTRIBUTE_AS_IMAGE_CODE,
            [],
            false,
            false,
            false,
            null,
            []
        );

        ($this->createAttributeHandler)($createImageAttributeCommand);
    }

    private function updateReferenceEntityWithAttributeAsLabelAndImage(ReferenceEntityIdentifier $referenceEntityIdentifier): void
    {
        $referenceEntity = $this->referenceEntityRepository->getByIdentifier($referenceEntityIdentifier);

        $attributes = $this->attributeRepository->findByReferenceEntity($referenceEntityIdentifier);
        foreach ($attributes as $attribute) {
            if (ReferenceEntity::DEFAULT_ATTRIBUTE_AS_LABEL_CODE === (string) $attribute->getCode()) {
                $referenceEntity->updateAttributeAsLabelReference(
                    AttributeAsLabelReference::fromAttributeIdentifier($attribute->getIdentifier())
                );
            }
            if (ReferenceEntity::DEFAULT_ATTRIBUTE_AS_IMAGE_CODE === (string) $attribute->getCode()) {
                $referenceEntity->updateAttributeAsImageReference(
                    AttributeAsImageReference::fromAttributeIdentifier($attribute->getIdentifier())
                );
            }
        }

        $this->referenceEntityRepository->update($referenceEntity);
    }
}
