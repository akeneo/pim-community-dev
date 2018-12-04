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

namespace Akeneo\ReferenceEntity\Integration\Persistence\Sql\ReferenceEntity;

use Akeneo\ReferenceEntity\Domain\Model\Attribute\AttributeAllowedExtensions;
use Akeneo\ReferenceEntity\Domain\Model\Attribute\AttributeCode;
use Akeneo\ReferenceEntity\Domain\Model\Attribute\AttributeIdentifier;
use Akeneo\ReferenceEntity\Domain\Model\Attribute\AttributeIsRequired;
use Akeneo\ReferenceEntity\Domain\Model\Attribute\AttributeMaxFileSize;
use Akeneo\ReferenceEntity\Domain\Model\Attribute\AttributeMaxLength;
use Akeneo\ReferenceEntity\Domain\Model\Attribute\AttributeOrder;
use Akeneo\ReferenceEntity\Domain\Model\Attribute\AttributeRegularExpression;
use Akeneo\ReferenceEntity\Domain\Model\Attribute\AttributeValidationRule;
use Akeneo\ReferenceEntity\Domain\Model\Attribute\AttributeValuePerChannel;
use Akeneo\ReferenceEntity\Domain\Model\Attribute\AttributeValuePerLocale;
use Akeneo\ReferenceEntity\Domain\Model\Attribute\ImageAttribute;
use Akeneo\ReferenceEntity\Domain\Model\Attribute\TextAttribute;
use Akeneo\ReferenceEntity\Domain\Model\Image;
use Akeneo\ReferenceEntity\Domain\Model\LabelCollection;
use Akeneo\ReferenceEntity\Domain\Model\ReferenceEntity\ReferenceEntity;
use Akeneo\ReferenceEntity\Domain\Model\ReferenceEntity\ReferenceEntityIdentifier;
use Akeneo\ReferenceEntity\Domain\Query\Attribute\Connector\ConnectorAttribute;
use Akeneo\ReferenceEntity\Domain\Query\Attribute\Connector\FindConnectorAttributesByReferenceEntityIdentifierInterface;
use Akeneo\ReferenceEntity\Domain\Repository\AttributeRepositoryInterface;
use Akeneo\ReferenceEntity\Domain\Repository\ReferenceEntityRepositoryInterface;
use Akeneo\ReferenceEntity\Integration\SqlIntegrationTestCase;
use Akeneo\Tool\Component\FileStorage\Model\FileInfo;

class SqlFindConnectorAttributesByReferenceEntityIdentifierTest extends SqlIntegrationTestCase
{
    /** @var ReferenceEntityRepositoryInterface */
    private $referenceEntityRepository;

    /** @var AttributeRepositoryInterface */
    private $attributeRepository;

    /** @var FindConnectorAttributesByReferenceEntityIdentifierInterface*/
    private $findConnectorReferenceEntityAttributes;

    protected function setUp(): void
    {
        parent::setUp();

        $this->referenceEntityRepository = $this->get('akeneo_referenceentity.infrastructure.persistence.repository.reference_entity');
        $this->attributeRepository = $this->get('akeneo_referenceentity.infrastructure.persistence.repository.attribute');
        $this->findConnectorReferenceEntityAttributes = $this->get('akeneo_referenceentity.infrastructure.persistence.query.find_connector_reference_entity_attributes_by_reference_entity_identifier');
        $this->resetDB();
    }

    /**
     * @test
     */
    public function it_finds_connector_attributes_for_a_reference_entity()
    {
        $referenceEntityIdentifier = 'reference_entity';
        $this->createReferenceEntity($referenceEntityIdentifier);
        $connectorAttributes = $this->createConnectorAttributes($referenceEntityIdentifier);

        $foundAttributes = ($this->findConnectorReferenceEntityAttributes)(ReferenceEntityIdentifier::fromString($referenceEntityIdentifier));

        $normalizedAttributes = [];
        foreach ($connectorAttributes as $attribute) {
            $normalizedAttributes[] = $attribute->normalize();
        }

        $normalizedFoundAttributes = [];
        foreach ($foundAttributes as $foundAttribute) {
            $normalizedFoundAttributes[] = $foundAttribute->normalize();
        }

        $this->assertSame($normalizedAttributes, $normalizedFoundAttributes);
    }

    /**
     * @test
     */
    public function it_returns_an_empty_array_if_no_attributes_found()
    {
        $foundAttributes = ($this->findConnectorReferenceEntityAttributes)(ReferenceEntityIdentifier::fromString('whatever'));

        $this->assertSame([], $foundAttributes);
    }

    private function resetDB(): void
    {
        $this->get('akeneoreference_entity.tests.helper.database_helper')->resetDatabase();
    }

    private function createConnectorAttributes(string $referenceEntityIdentifier)
    {
        $textAttribute = TextAttribute::createText(
            AttributeIdentifier::create($referenceEntityIdentifier, 'text', 'test'),
            ReferenceEntityIdentifier::fromString($referenceEntityIdentifier),
            AttributeCode::fromString('regex'),
            LabelCollection::fromArray(['en_US' => 'Description', 'fr_FR' => 'Description']),
            AttributeOrder::fromInteger(1),
            AttributeIsRequired::fromBoolean(true),
            AttributeValuePerChannel::fromBoolean(false),
            AttributeValuePerLocale::fromBoolean(true),
            AttributeMaxLength::fromInteger(155),
            AttributeValidationRule::fromString(AttributeValidationRule::REGULAR_EXPRESSION),
            AttributeRegularExpression::fromString('/\w+/')
        );

        $imageAttribute = ImageAttribute::create(
            AttributeIdentifier::create($referenceEntityIdentifier, 'image', 'test'),
            ReferenceEntityIdentifier::fromString($referenceEntityIdentifier),
            AttributeCode::fromString('image'),
            LabelCollection::fromArray(['en_US' => 'Photo', 'fr_FR' => 'Photo']),
            AttributeOrder::fromInteger(2),
            AttributeIsRequired::fromBoolean(true),
            AttributeValuePerChannel::fromBoolean(false),
            AttributeValuePerLocale::fromBoolean(false),
            AttributeMaxFileSize::fromString('10'),
            AttributeAllowedExtensions::fromList(['jpg'])
        );

        $this->attributeRepository->create($textAttribute);
        $this->attributeRepository->create($imageAttribute);

        return [
            new ConnectorAttribute(
                $textAttribute->getIdentifier(),
                LabelCollection::fromArray(['en_US' => 'Description', 'fr_FR' => 'Description']),
                'text',
                AttributeValuePerLocale::fromBoolean($textAttribute->hasValuePerLocale()),
                AttributeValuePerChannel::fromBoolean($textAttribute->hasValuePerChannel()),
                AttributeIsRequired::fromBoolean(true),
                [
                    'max_length' => $textAttribute->getMaxLength()->intValue(),
                    'is_textarea' => false,
                    'is_rich_text_editor' => false,
                    'validation_rule' => AttributeValidationRule::REGULAR_EXPRESSION,
                    'regular_expression' => $textAttribute->getRegularExpression()->normalize()
                ]
            ),
            new ConnectorAttribute(
                $imageAttribute->getIdentifier(),
                LabelCollection::fromArray(['en_US' => 'Photo', 'fr_FR' => 'Photo']),
                'image',
                AttributeValuePerLocale::fromBoolean($imageAttribute->hasValuePerLocale()),
                AttributeValuePerChannel::fromBoolean($imageAttribute->hasValuePerChannel()),
                AttributeIsRequired::fromBoolean(true),
                [
                    'max_file_size' => '10',
                    'allowed_extensions' => ['jpg']
                ]
            )
        ];
    }

    private function createReferenceEntity(string $rawIdentifier): ReferenceEntity
    {
        $referenceEntityIdentifier = ReferenceEntityIdentifier::fromString($rawIdentifier);

        $imageInfo = new FileInfo();
        $imageInfo
            ->setOriginalFilename(sprintf('image_%s', $rawIdentifier))
            ->setKey(sprintf('test/image_%s.jpg', $rawIdentifier));

        $referenceEntity = ReferenceEntity::create(
            $referenceEntityIdentifier,
            ['en_US' => $rawIdentifier],
            Image::fromFileInfo($imageInfo)
        );

        $this->referenceEntityRepository->create($referenceEntity);

        return $referenceEntity;
    }
}
