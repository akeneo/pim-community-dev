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

namespace Akeneo\ReferenceEntity\Integration\Persistence\Sql\Attribute;

use Akeneo\ReferenceEntity\Domain\Model\Attribute\AttributeAllowedExtensions;
use Akeneo\ReferenceEntity\Domain\Model\Attribute\AttributeCode;
use Akeneo\ReferenceEntity\Domain\Model\Attribute\AttributeIdentifier;
use Akeneo\ReferenceEntity\Domain\Model\Attribute\AttributeIsRequired;
use Akeneo\ReferenceEntity\Domain\Model\Attribute\AttributeMaxFileSize;
use Akeneo\ReferenceEntity\Domain\Model\Attribute\AttributeOption\AttributeOption;
use Akeneo\ReferenceEntity\Domain\Model\Attribute\AttributeOption\OptionCode;
use Akeneo\ReferenceEntity\Domain\Model\Attribute\AttributeOrder;
use Akeneo\ReferenceEntity\Domain\Model\Attribute\AttributeValuePerChannel;
use Akeneo\ReferenceEntity\Domain\Model\Attribute\AttributeValuePerLocale;
use Akeneo\ReferenceEntity\Domain\Model\Attribute\ImageAttribute;
use Akeneo\ReferenceEntity\Domain\Model\Attribute\OptionCollectionAttribute;
use Akeneo\ReferenceEntity\Domain\Model\Image;
use Akeneo\ReferenceEntity\Domain\Model\LabelCollection;
use Akeneo\ReferenceEntity\Domain\Model\ReferenceEntity\ReferenceEntity;
use Akeneo\ReferenceEntity\Domain\Model\ReferenceEntity\ReferenceEntityIdentifier;
use Akeneo\ReferenceEntity\Domain\Query\Attribute\Connector\FindConnectorAttributeOptionsInterface;
use Akeneo\ReferenceEntity\Domain\Repository\AttributeRepositoryInterface;
use Akeneo\ReferenceEntity\Domain\Repository\ReferenceEntityRepositoryInterface;
use Akeneo\ReferenceEntity\Integration\SqlIntegrationTestCase;

class SqlFindConnectorAttributeOptionsTest extends SqlIntegrationTestCase
{
    /** @var ReferenceEntityRepositoryInterface */
    private $referenceEntityRepository;

    /** @var AttributeRepositoryInterface */
    private $attributeRepository;

    /** @var FindConnectorAttributeOptionsInterface*/
    private $findConnectorAttributeOption;

    protected function setUp(): void
    {
        parent::setUp();

        $this->referenceEntityRepository = $this->get('akeneo_referenceentity.infrastructure.persistence.repository.reference_entity');
        $this->attributeRepository = $this->get('akeneo_referenceentity.infrastructure.persistence.repository.attribute');
        $this->findConnectorAttributeOption = $this->get('akeneo_referenceentity.infrastructure.persistence.query.find_connector_attribute_options');
        $this->resetDB();
    }

    /**
     * @test
     */
    public function it_finds_options_for_an_attribute()
    {
        $referenceEntityIdentifier = 'reference_entity';
        $this->createReferenceEntity($referenceEntityIdentifier);
        $this->createAttribute($referenceEntityIdentifier);

        $foundAttributeOptions = ($this->findConnectorAttributeOption)(
            ReferenceEntityIdentifier::fromString($referenceEntityIdentifier),
            AttributeCode::fromString('attribute_1_code')
        );

        $normalizedFoundOptions = [];

        foreach ($foundAttributeOptions as $option) {
            $normalizedFoundOptions[] = $option->normalize();
        }

        $this->assertSame(
            [
                AttributeOption::create(
                    OptionCode::fromString('french'),
                    LabelCollection::fromArray(['fr_FR' => 'Francais'])
                )->normalize(),
                AttributeOption::create(
                    OptionCode::fromString('english'),
                    LabelCollection::fromArray(['fr_FR' => 'Angalis'])
                )->normalize()
            ],
            $normalizedFoundOptions
        );
    }

    /**
     * @test
     */
    public function it_returns_empty_array_if_attribute_has_no_options()
    {
        $referenceEntityIdentifier = 'reference_entity_test';
        $this->createReferenceEntity($referenceEntityIdentifier);
        $this->createAttributeWithNoOptions($referenceEntityIdentifier);

        $foundOptions = ($this->findConnectorAttributeOption)(
            ReferenceEntityIdentifier::fromString($referenceEntityIdentifier),
            AttributeCode::fromString('no_options')
        );

        $this->assertSame([], $foundOptions);
    }

    private function resetDB(): void
    {
        $this->get('akeneoreference_entity.tests.helper.database_helper')->resetDatabase();
    }

    private function createImageAttribute(string $referenceEntityIdentifier)
    {
        $imageAttribute = ImageAttribute::create(
            AttributeIdentifier::create($referenceEntityIdentifier, 'portrait', 'fingerprint'),
            ReferenceEntityIdentifier::fromString($referenceEntityIdentifier),
            AttributeCode::fromString('portrait'),
            LabelCollection::fromArray(['fr_FR' => 'Image autobiographique', 'en_US' => 'Portrait']),
            AttributeOrder::fromInteger(3),
            AttributeIsRequired::fromBoolean(false),
            AttributeValuePerChannel::fromBoolean(false),
            AttributeValuePerLocale::fromBoolean(false),
            AttributeMaxFileSize::fromString('200.10'),
            AttributeAllowedExtensions::fromList(['png'])
        );

        $this->attributeRepository->create($imageAttribute);
    }

    private function createAttribute(string $referenceEntityIdentifier)
    {
        $optionCollectionAttribute = OptionCollectionAttribute::create(
            AttributeIdentifier::create($referenceEntityIdentifier, 'attribute_1', 'test'),
            ReferenceEntityIdentifier::fromString($referenceEntityIdentifier),
            AttributeCode::fromString('attribute_1_code'),
            LabelCollection::fromArray(['en_US' => 'Attribute']),
            AttributeOrder::fromInteger(2),
            AttributeIsRequired::fromBoolean(true),
            AttributeValuePerChannel::fromBoolean(false),
            AttributeValuePerLocale::fromBoolean(false)
        );

        $optionCollectionAttribute->setOptions([
            AttributeOption::create(
                OptionCode::fromString('french'),
                LabelCollection::fromArray(['fr_FR' => 'Francais'])
            ),
            AttributeOption::create(
                OptionCode::fromString('english'),
                LabelCollection::fromArray(['fr_FR' => 'Angalis'])
            )
        ]);

        $this->attributeRepository->create($optionCollectionAttribute);
    }

    private function createAttributeWithNoOptions(string $referenceEntityIdentifier)
    {
        $optionCollectionAttribute = OptionCollectionAttribute::create(
            AttributeIdentifier::create($referenceEntityIdentifier, 'no_options', 'test'),
            ReferenceEntityIdentifier::fromString($referenceEntityIdentifier),
            AttributeCode::fromString('no_options'),
            LabelCollection::fromArray(['en_US' => 'Attribute']),
            AttributeOrder::fromInteger(2),
            AttributeIsRequired::fromBoolean(true),
            AttributeValuePerChannel::fromBoolean(false),
            AttributeValuePerLocale::fromBoolean(false)
        );

        $optionCollectionAttribute->setOptions([]);

        $this->attributeRepository->create($optionCollectionAttribute);
    }

    private function createReferenceEntity(string $rawIdentifier): ReferenceEntity
    {
        $referenceEntityIdentifier = ReferenceEntityIdentifier::fromString($rawIdentifier);

        $referenceEntity = ReferenceEntity::create(
            $referenceEntityIdentifier,
            ['en_US' => $rawIdentifier],
            Image::createEmpty()
        );

        $this->referenceEntityRepository->create($referenceEntity);

        return $referenceEntity;
    }
}
