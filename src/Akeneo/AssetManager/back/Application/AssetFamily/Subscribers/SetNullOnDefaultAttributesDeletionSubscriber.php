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

namespace Akeneo\AssetManager\Application\AssetFamily\Subscribers;

use Akeneo\AssetManager\Domain\Event\BeforeAttributeDeletedEvent;
use Akeneo\AssetManager\Domain\Model\AssetFamily\AttributeAsLabelReference;
use Akeneo\AssetManager\Domain\Model\AssetFamily\AttributeAsMainMediaReference;
use Akeneo\AssetManager\Domain\Repository\AssetFamilyRepositoryInterface;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

/**
 * @author    Christophe Chausseray <christophe.chausseray@akeneo.com>
 * @copyright 2019 Akeneo SAS (http://www.akeneo.com)
 */
class SetNullOnDefaultAttributesDeletionSubscriber implements EventSubscriberInterface
{
    private AssetFamilyRepositoryInterface $assetFamilyRepository;

    public function __construct(AssetFamilyRepositoryInterface $assetFamilyRepository)
    {
        $this->assetFamilyRepository = $assetFamilyRepository;
    }
    /**
     * {@inheritdoc}
     */
    public static function getSubscribedEvents()
    {
        return [
            BeforeAttributeDeletedEvent::class => 'beforeAttributeAsLabelOrMainMediaIsDeleted',
        ];
    }

    public function beforeAttributeAsLabelOrMainMediaIsDeleted(BeforeAttributeDeletedEvent $beforeAttributeDeletedEvent): void
    {
        $assetFamily = $this->assetFamilyRepository->getByIdentifier($beforeAttributeDeletedEvent->getAssetFamilyIdentifier());

        $attributeAsLabel = $assetFamily->getAttributeAsLabelReference();
        $attributeAsMainMedia = $assetFamily->getAttributeAsMainMediaReference();

        if (!$attributeAsLabel->isEmpty() && $beforeAttributeDeletedEvent->getAttributeIdentifier()->equals($attributeAsLabel->getIdentifier())) {
            $assetFamily->updateAttributeAsLabelReference(AttributeAsLabelReference::noReference());
        }

        if (!$attributeAsMainMedia->isEmpty() && $beforeAttributeDeletedEvent->getAttributeIdentifier()->equals($attributeAsMainMedia->getIdentifier())) {
            $assetFamily->updateAttributeAsMainMediaReference(AttributeAsMainMediaReference::noReference());
        }

        $this->assetFamilyRepository->update($assetFamily);
    }
}
