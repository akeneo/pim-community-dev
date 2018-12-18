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

use Akeneo\ReferenceEntity\Domain\Model\Attribute\AttributeCode;
use Akeneo\ReferenceEntity\Domain\Model\Attribute\AttributeIdentifier;
use Akeneo\ReferenceEntity\Domain\Model\Attribute\AttributeIsRequired;
use Akeneo\ReferenceEntity\Domain\Model\Attribute\AttributeOption\AttributeOption;
use Akeneo\ReferenceEntity\Domain\Model\Attribute\AttributeOption\OptionCode;
use Akeneo\ReferenceEntity\Domain\Model\Attribute\AttributeOrder;
use Akeneo\ReferenceEntity\Domain\Model\Attribute\AttributeValuePerChannel;
use Akeneo\ReferenceEntity\Domain\Model\Attribute\AttributeValuePerLocale;
use Akeneo\ReferenceEntity\Domain\Model\Attribute\OptionCollectionAttribute;
use Akeneo\ReferenceEntity\Domain\Model\Image;
use Akeneo\ReferenceEntity\Domain\Model\LabelCollection;
use Akeneo\ReferenceEntity\Domain\Model\ReferenceEntity\ReferenceEntity;
use Akeneo\ReferenceEntity\Domain\Model\ReferenceEntity\ReferenceEntityIdentifier;
use Akeneo\ReferenceEntity\Domain\Query\Attribute\Connector\ConnectorAttribute;
use Akeneo\ReferenceEntity\Domain\Query\Attribute\Connector\FindConnectorAttributeOptionInterface;
use Akeneo\ReferenceEntity\Domain\Repository\AttributeRepositoryInterface;
use Akeneo\ReferenceEntity\Domain\Repository\ReferenceEntityRepositoryInterface;
use Akeneo\ReferenceEntity\Integration\SqlIntegrationTestCase;

class SqlFindConnectorAttributeOptionTest extends SqlIntegrationTestCase
{
    /** @var ReferenceEntityRepositoryInterface */
    private $referenceEntityRepository;

    /** @var AttributeRepositoryInterface */
    private $attributeRepository;

    /** @var FindConnectorAttributeOptionInterface*/
    private $findConnectorAttributeOption;

    protected function setUp(): void
    {
        parent::setUp();

        $this->referenceEntityRepository = $this->get('akeneo_referenceentity.infrastructure.persistence.repository.reference_entity');
        $this->attributeRepository = $this->get('akeneo_referenceentity.infrastructure.persistence.repository.attribute');
        $this->findConnectorAttributeOption = $this->get('akeneo_referenceentity.infrastructure.persistence.query.find_connector_attribute_option');
        $this->resetDB();
    }

    /**
     * @test
     */
    public function it_finds_an_option_for_an_attribute()
    {
        $referenceEntityIdentifier = 'reference_entity';
        $this->createReferenceEntity($referenceEntityIdentifier);
        $this->createConnectorAttribute($referenceEntityIdentifier);

        $foundAttributeOption = ($this->findConnectorAttributeOption)(
            ReferenceEntityIdentifier::fromString($referenceEntityIdentifier),
            AttributeCode::fromString('attribute_1_code'),
            OptionCode::fromString('french')
        );

        $this->assertSame(
            AttributeOption::create(
                OptionCode::fromString('french'),
                LabelCollection::fromArray(['fr_FR' => 'Francais'])
            )->normalize(),
            $foundAttributeOption->normalize()
        );
    }

    /**
     * @test
     */
    public function it_returns_null_if_no_option_found()
    {
        $foundAttribute = ($this->findConnectorAttributeOption)(
            ReferenceEntityIdentifier::fromString('reference_entity'),
            AttributeCode::fromString('none'),
            OptionCode::fromString('whatever')
        );

        $this->assertSame(null, $foundAttribute);
    }

    private function resetDB(): void
    {
        $this->get('akeneoreference_entity.tests.helper.database_helper')->resetDatabase();
    }

    private function createConnectorAttribute(string $referenceEntityIdentifier)
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

        return new ConnectorAttribute(
            $optionCollectionAttribute->getCode(),
            LabelCollection::fromArray(['en_US' => 'Photo', 'fr_FR' => 'Photo']),
            'image',
            AttributeValuePerLocale::fromBoolean($optionCollectionAttribute->hasValuePerLocale()),
            AttributeValuePerChannel::fromBoolean($optionCollectionAttribute->hasValuePerChannel()),
            AttributeIsRequired::fromBoolean(true),
            [
                'options' => array_map(
                    function (AttributeOption $attributeOption) {
                        return $attributeOption->normalize();
                    },
                    $optionCollectionAttribute->getAttributeOptions()
                ),
            ]
        );
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
