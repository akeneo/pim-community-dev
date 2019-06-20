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

namespace Akeneo\ReferenceEntity\Application\ReferenceEntity\Subscribers;

use Akeneo\ReferenceEntity\Domain\Event\BeforeAttributeDeletedEvent;
use Akeneo\ReferenceEntity\Domain\Model\ReferenceEntity\AttributeAsImageReference;
use Akeneo\ReferenceEntity\Domain\Model\ReferenceEntity\AttributeAsLabelReference;
use Akeneo\ReferenceEntity\Domain\Repository\ReferenceEntityRepositoryInterface;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

/**
 * @author    Christophe Chausseray <christophe.chausseray@akeneo.com>
 * @copyright 2019 Akeneo SAS (http://www.akeneo.com)
 */
class SetNullOnDefaultAttributesDeletionSubscriber implements EventSubscriberInterface
{
    /** @var ReferenceEntityRepositoryInterface */
    private $referenceEntityRepository;

    public function __construct(ReferenceEntityRepositoryInterface $referenceEntityRepository)
    {
        $this->referenceEntityRepository = $referenceEntityRepository;
    }
    /**
     * {@inheritdoc}
     */
    public static function getSubscribedEvents()
    {
        return [
            BeforeAttributeDeletedEvent::class => 'beforeAttributeAsLabelOrImageIsDeleted',
        ];
    }

    public function beforeAttributeAsLabelOrImageIsDeleted(BeforeAttributeDeletedEvent $beforeAttributeDeletedEvent): void
    {
        $referenceEntity = $this->referenceEntityRepository->getByIdentifier($beforeAttributeDeletedEvent->getReferenceEntityIdentifier());

        $attributeAsLabel = $referenceEntity->getAttributeAsLabelReference();
        $attributeAsImage = $referenceEntity->getAttributeAsImageReference();

        if (!$attributeAsLabel->isEmpty() && $beforeAttributeDeletedEvent->getAttributeIdentifier()->equals($attributeAsLabel->getIdentifier())) {
            $referenceEntity->updateAttributeAsLabelReference(AttributeAsLabelReference::noReference());
        }

        if (!$attributeAsImage->isEmpty() && $beforeAttributeDeletedEvent->getAttributeIdentifier()->equals($attributeAsImage->getIdentifier())) {
            $referenceEntity->updateAttributeAsImageReference(AttributeAsImageReference::noReference());
        }

        $this->referenceEntityRepository->update($referenceEntity);
    }
}
