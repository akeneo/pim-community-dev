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
use Akeneo\ReferenceEntity\Domain\Model\Attribute\AttributeOrder;
use Akeneo\ReferenceEntity\Domain\Model\Attribute\AttributeValuePerChannel;
use Akeneo\ReferenceEntity\Domain\Model\Attribute\AttributeValuePerLocale;
use Akeneo\ReferenceEntity\Domain\Model\Attribute\ImageAttribute;
use Akeneo\ReferenceEntity\Domain\Model\Image;
use Akeneo\ReferenceEntity\Domain\Model\LabelCollection;
use Akeneo\ReferenceEntity\Domain\Model\ReferenceEntity\ReferenceEntity;
use Akeneo\ReferenceEntity\Domain\Model\ReferenceEntity\ReferenceEntityIdentifier;
use Akeneo\ReferenceEntity\Domain\Query\Attribute\Connector\ConnectorAttribute;
use Akeneo\ReferenceEntity\Domain\Query\Attribute\Connector\FindConnectorAttributeByIdentifierAndCodeInterface;
use Akeneo\ReferenceEntity\Domain\Repository\AttributeRepositoryInterface;
use Akeneo\ReferenceEntity\Domain\Repository\ReferenceEntityRepositoryInterface;
use Akeneo\ReferenceEntity\Integration\SqlIntegrationTestCase;
use Akeneo\Tool\Component\FileStorage\Model\FileInfo;

class SqlFindConnectorAttributeByIdentifierAndCodeTest extends SqlIntegrationTestCase
{
    /** @var ReferenceEntityRepositoryInterface */
    private $referenceEntityRepository;

    /** @var AttributeRepositoryInterface */
    private $attributeRepository;

    /** @var FindConnectorAttributeByIdentifierAndCodeInterface*/
    private $findConnectorReferenceEntityAttribute;

    protected function setUp(): void
    {
        parent::setUp();

        $this->referenceEntityRepository = $this->get('akeneo_referenceentity.infrastructure.persistence.repository.reference_entity');
        $this->attributeRepository = $this->get('akeneo_referenceentity.infrastructure.persistence.repository.attribute');
        $this->findConnectorReferenceEntityAttribute = $this->get('akeneo_referenceentity.infrastructure.persistence.query.find_connector_attribute_by_identifier_and_code');
        $this->resetDB();
    }

    /**
     * @test
     */
    public function it_finds_a_connector_attribute_for_a_reference_entity()
    {
        $referenceEntityIdentifier = 'reference_entity';
        $this->createReferenceEntity($referenceEntityIdentifier);
        $connectorAttribute = $this->createConnectorAttribute($referenceEntityIdentifier);

        $foundAttribute = ($this->findConnectorReferenceEntityAttribute)(
            ReferenceEntityIdentifier::fromString($referenceEntityIdentifier),
            AttributeCode::fromString('main_image')
        );

        $this->assertSame($connectorAttribute->normalize(), $foundAttribute->normalize());
    }

    /**
     * @test
     */
    public function it_returns_null_if_no_attribute_found()
    {
        $foundAttribute = ($this->findConnectorReferenceEntityAttribute)(
            ReferenceEntityIdentifier::fromString('reference_entity'),
            AttributeCode::fromString('none')
        );

        $this->assertSame(null, $foundAttribute);
    }

    private function resetDB(): void
    {
        $this->get('akeneoreference_entity.tests.helper.database_helper')->resetDatabase();
    }

    private function createConnectorAttribute(string $referenceEntityIdentifier)
    {
        $imageAttribute = ImageAttribute::create(
            AttributeIdentifier::create($referenceEntityIdentifier, 'main_image', 'test'),
            ReferenceEntityIdentifier::fromString($referenceEntityIdentifier),
            AttributeCode::fromString('main_image'),
            LabelCollection::fromArray(['en_US' => 'Photo', 'fr_FR' => 'Photo']),
            AttributeOrder::fromInteger(2),
            AttributeIsRequired::fromBoolean(true),
            AttributeValuePerChannel::fromBoolean(false),
            AttributeValuePerLocale::fromBoolean(false),
            AttributeMaxFileSize::fromString('10'),
            AttributeAllowedExtensions::fromList(['jpg'])
        );

        $this->attributeRepository->create($imageAttribute);

        return new ConnectorAttribute(
                $imageAttribute->getCode(),
                LabelCollection::fromArray(['en_US' => 'Photo', 'fr_FR' => 'Photo']),
                'image',
                AttributeValuePerLocale::fromBoolean($imageAttribute->hasValuePerLocale()),
                AttributeValuePerChannel::fromBoolean($imageAttribute->hasValuePerChannel()),
                AttributeIsRequired::fromBoolean(true),
                [
                    'max_file_size' => '10',
                    'allowed_extensions' => ['jpg']
                ]
            );
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
