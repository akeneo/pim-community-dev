<?php

declare(strict_types=1);

namespace Akeneo\ReferenceEntity\Integration\PublicApi\Enrich;

use Akeneo\ReferenceEntity\Domain\Model\Attribute\AttributeCode;
use Akeneo\ReferenceEntity\Domain\Model\Attribute\AttributeIdentifier;
use Akeneo\ReferenceEntity\Domain\Model\Attribute\AttributeIsRequired;
use Akeneo\ReferenceEntity\Domain\Model\Attribute\AttributeOption\AttributeOption;
use Akeneo\ReferenceEntity\Domain\Model\Attribute\AttributeOption\OptionCode;
use Akeneo\ReferenceEntity\Domain\Model\Attribute\AttributeOrder;
use Akeneo\ReferenceEntity\Domain\Model\Attribute\AttributeValuePerChannel;
use Akeneo\ReferenceEntity\Domain\Model\Attribute\AttributeValuePerLocale;
use Akeneo\ReferenceEntity\Domain\Model\Attribute\OptionAttribute;
use Akeneo\ReferenceEntity\Domain\Model\Attribute\OptionCollectionAttribute;
use Akeneo\ReferenceEntity\Domain\Model\Image;
use Akeneo\ReferenceEntity\Domain\Model\LabelCollection;
use Akeneo\ReferenceEntity\Domain\Model\ReferenceEntity\ReferenceEntity;
use Akeneo\ReferenceEntity\Domain\Model\ReferenceEntity\ReferenceEntityIdentifier;
use Akeneo\ReferenceEntity\Infrastructure\PublicApi\Enrich\FindOptionAttributeLabelsInterface;
use Akeneo\ReferenceEntity\Integration\SqlIntegrationTestCase;
use PHPUnit\Framework\Assert;

class FindOptionAttributeLabelsTest extends SqlIntegrationTestCase
{
    private FindOptionAttributeLabelsInterface $findOptionAttributeLabels;
    private string $labelAttributeIdentifier = '';

    public function setUp(): void
    {
        parent::setUp();

        $this->findOptionAttributeLabels = $this->get(
            'akeneo_referenceentity.infrastructure.persistence.query.enrich.find_option_attribute_labels_public_api'
        );
        $this->get('akeneoreference_entity.tests.helper.database_helper')->resetDatabase();
        $this->loadReferenceEntityWithOptionAttributes();
    }

    /**
     * @test
     */
    public function it_finds_provided_option_attribute_labels(): void
    {
        $records = $this->findOptionAttributeLabels->find(
            'option_attribute_designer_fingerprint',
        );

        Assert::assertEquals(
            [
                'blue' => [
                    'en_US' => 'blue in english',
                    'fr_FR' => 'blue en français',
                ],
                'red' => [
                    'en_US' => 'red in english',
                    'fr_FR' => 'red en français',
                ],
            ],
            $records,
        );
    }

    /**
     * @test
     */
    public function it_finds_provided_option_collection_attribute_labels(): void
    {
        $records = $this->findOptionAttributeLabels->find(
            'option_col_attribute_designer_fingerprint',
        );

        Assert::assertEquals(
            [
                'spring' => [
                    'en_US' => 'spring in english',
                    'fr_FR' => 'spring en français',
                ],
                'summer' => [
                    'en_US' => 'summer in english',
                    'fr_FR' => 'summer en français',
                ],
            ],
            $records,
        );
    }

    /**
     * @test
     */
    public function it_returns_empty_array_when_attribute_is_not_found(): void
    {
        $records = $this->findOptionAttributeLabels->find('unknown_attribute');

        Assert::assertEmpty($records);
    }

    /**
     * @test
     */
    public function it_returns_empty_array_when_attribute_is_not_an_option_attribute(): void
    {
        $records = $this->findOptionAttributeLabels->find($this->labelAttributeIdentifier);

        Assert::assertEmpty($records);
    }

    private function loadReferenceEntityWithOptionAttributes(): void
    {
        $referenceEntityRepository = $this->get(
            'akeneo_referenceentity.infrastructure.persistence.repository.reference_entity',
        );
        $referenceEntityIdentifier = ReferenceEntityIdentifier::fromString('designer');
        $referenceEntity = ReferenceEntity::create(
            $referenceEntityIdentifier,
            [
                'fr_FR' => 'Concepteur',
                'en_US' => 'Designer',
            ],
            Image::createEmpty(),
        );
        $referenceEntityRepository->create($referenceEntity);
        $referenceEntity = $referenceEntityRepository->getByIdentifier($referenceEntityIdentifier);
        $this->labelAttributeIdentifier = (string) $referenceEntity->getAttributeAsLabelReference()->getIdentifier();
        $this->createOptionAttribute((string) $referenceEntityIdentifier, 'option_attribute', ['blue', 'red'], 40, false, false);
        $this->createOptionCollectionAttribute((string) $referenceEntityIdentifier, 'option_col_attribute', ['spring', 'summer'], 50, false, false);
    }

    private function createOptionAttribute(
        string $referenceEntityIdentifier,
        string $attributeCode,
        array $options,
        int $order,
        bool $valuePerChannel,
        bool $valuePerLocale,
    ): void {
        $attributeRepository = $this->get('akeneo_referenceentity.infrastructure.persistence.repository.attribute');

        $attribute = OptionAttribute::create(
            AttributeIdentifier::create((string) $referenceEntityIdentifier, $attributeCode, 'fingerprint'),
            ReferenceEntityIdentifier::fromString($referenceEntityIdentifier),
            AttributeCode::fromString($attributeCode),
            LabelCollection::fromArray([]),
            AttributeOrder::fromInteger($order),
            AttributeIsRequired::fromBoolean(true),
            AttributeValuePerChannel::fromBoolean($valuePerChannel),
            AttributeValuePerLocale::fromBoolean($valuePerLocale),
        );
        $attribute->setOptions(array_map(
            static fn (string $optionCode) => AttributeOption::create(
                OptionCode::fromString($optionCode),
                LabelCollection::fromArray([
                    'en_US' => sprintf('%s in english', $optionCode),
                    'fr_FR' => sprintf('%s en français', $optionCode),
                ]),
            ),
            $options,
        ));

        $attributeRepository->create($attribute);
    }

    private function createOptionCollectionAttribute(
        string $referenceEntityIdentifier,
        string $attributeCode,
        array $options,
        int $order,
        bool $valuePerChannel,
        bool $valuePerLocale,
    ): void {
        $attributeRepository = $this->get('akeneo_referenceentity.infrastructure.persistence.repository.attribute');

        $attribute = OptionCollectionAttribute::create(
            AttributeIdentifier::create((string) $referenceEntityIdentifier, $attributeCode, 'fingerprint'),
            ReferenceEntityIdentifier::fromString($referenceEntityIdentifier),
            AttributeCode::fromString($attributeCode),
            LabelCollection::fromArray([]),
            AttributeOrder::fromInteger($order),
            AttributeIsRequired::fromBoolean(true),
            AttributeValuePerChannel::fromBoolean($valuePerChannel),
            AttributeValuePerLocale::fromBoolean($valuePerLocale),
        );
        $attribute->setOptions(array_map(
            static fn (string $optionCode) => AttributeOption::create(
                OptionCode::fromString($optionCode),
                LabelCollection::fromArray([
                    'en_US' => sprintf('%s in english', $optionCode),
                    'fr_FR' => sprintf('%s en français', $optionCode),
                ]),
            ),
            $options,
        ));

        $attributeRepository->create($attribute);
    }
}
