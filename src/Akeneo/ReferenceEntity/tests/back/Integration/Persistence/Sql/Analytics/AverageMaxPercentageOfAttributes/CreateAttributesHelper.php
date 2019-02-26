<?php

declare(strict_types=1);

namespace Akeneo\ReferenceEntity\Integration\Persistence\Sql\Analytics\AverageMaxPercentageOfAttributes;

use Akeneo\ReferenceEntity\Domain\Model\Attribute\AttributeCode;
use Akeneo\ReferenceEntity\Domain\Model\Attribute\AttributeIdentifier;
use Akeneo\ReferenceEntity\Domain\Model\Attribute\AttributeIsRequired;
use Akeneo\ReferenceEntity\Domain\Model\Attribute\AttributeMaxLength;
use Akeneo\ReferenceEntity\Domain\Model\Attribute\AttributeOrder;
use Akeneo\ReferenceEntity\Domain\Model\Attribute\AttributeRegularExpression;
use Akeneo\ReferenceEntity\Domain\Model\Attribute\AttributeValidationRule;
use Akeneo\ReferenceEntity\Domain\Model\Attribute\AttributeValuePerChannel;
use Akeneo\ReferenceEntity\Domain\Model\Attribute\AttributeValuePerLocale;
use Akeneo\ReferenceEntity\Domain\Model\Attribute\TextAttribute;
use Akeneo\ReferenceEntity\Domain\Model\LabelCollection;
use Akeneo\ReferenceEntity\Domain\Model\ReferenceEntity\ReferenceEntityIdentifier;
use Akeneo\ReferenceEntity\Domain\Repository\AttributeRepositoryInterface;

/**
 * @author    Samir Boulil <samir.boulil@akeneo.com>
 * @copyright 2019 Akeneo SAS (http://www.akeneo.com)
 */
class CreateAttributesHelper
{
    /** @var AttributeRepositoryInterface */
    private $attributeRepository;

    /** @var int */
    private $attributeOrder = 2;

    public function __construct(AttributeRepositoryInterface $attributeRepository)
    {
        $this->attributeRepository = $attributeRepository;
    }

    public function loadLocalizableOnlyAttributesForReferenceEntity(ReferenceEntityIdentifier $referenceEntityIdentifier, int $numberOfAttributesToCreate): void
    {
        // By default the label is localizable only
        $numberOfAttributesToCreate = $numberOfAttributesToCreate - 1;
        $this->createAttributesWith($referenceEntityIdentifier, $numberOfAttributesToCreate, false, true);
    }

    public function loadLocalizableAndScopableAttributesForReferenceEntity(ReferenceEntityIdentifier $referenceEntityIdentifier, int $numberOfAttributesToCreate): void
    {
        $this->createAttributesWith($referenceEntityIdentifier, $numberOfAttributesToCreate, true, true);
    }

    public function loadScopableOnlyAttributesForReferenceEntity(ReferenceEntityIdentifier $referenceEntityIdentifier, int $numberOfAttributesToCreate): void
    {
        $this->createAttributesWith($referenceEntityIdentifier, $numberOfAttributesToCreate, true, false);
    }

    public function loadNotLocalizableNotScopableAttributesForReferenceEntity($referenceEntityIdentifier, int $numberOfAttributesToCreate)
    {
        // By default, the image is not localizable nor scopable
        $numberOfAttributesToCreate -= 1;
        $this->createAttributesWith($referenceEntityIdentifier, $numberOfAttributesToCreate, false, false);
    }

    private function createAttributesWith(
        ReferenceEntityIdentifier $referenceEntityIdentifier,
        int $numberOfAttributesToCreate,
        bool $hasOneValuePerChannel,
        bool $hasOneValuePerLocale
    ): void {
        // By default, there are already 2 attributes created for each reference entity
        for ($i = 0; $i < $numberOfAttributesToCreate; $i++) {
            $this->attributeOrder++;
            $identifier = sprintf('%s_%d', $referenceEntityIdentifier->normalize(), $this->attributeOrder);
            $this->attributeRepository->create(
                TextAttribute::createText(
                    AttributeIdentifier::fromString($identifier),
                    $referenceEntityIdentifier,
                    AttributeCode::fromString($identifier),
                    LabelCollection::fromArray(['en_US' => 'Name']),
                    AttributeOrder::fromInteger($this->attributeOrder),
                    AttributeIsRequired::fromBoolean(true),
                    AttributeValuePerChannel::fromBoolean($hasOneValuePerChannel),
                    AttributeValuePerLocale::fromBoolean($hasOneValuePerLocale),
                    AttributeMaxLength::fromInteger(155),
                    AttributeValidationRule::none(),
                    AttributeRegularExpression::createEmpty()
                )
            );
        }
    }

}
