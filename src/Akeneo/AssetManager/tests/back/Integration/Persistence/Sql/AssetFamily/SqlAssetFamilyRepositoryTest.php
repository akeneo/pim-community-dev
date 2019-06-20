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
use Akeneo\ReferenceEntity\Domain\Model\Image;
use Akeneo\ReferenceEntity\Domain\Model\LabelCollection;
use Akeneo\ReferenceEntity\Domain\Model\ReferenceEntity\ReferenceEntity;
use Akeneo\ReferenceEntity\Domain\Model\ReferenceEntity\ReferenceEntityIdentifier;
use Akeneo\ReferenceEntity\Domain\Repository\AttributeRepositoryInterface;
use Akeneo\ReferenceEntity\Domain\Repository\ReferenceEntityNotFoundException;
use Akeneo\ReferenceEntity\Domain\Repository\ReferenceEntityRepositoryInterface;
use Akeneo\ReferenceEntity\Integration\SqlIntegrationTestCase;
use Akeneo\Tool\Component\FileStorage\Model\FileInfo;
use Doctrine\DBAL\DBALException;

class SqlReferenceEntityRepositoryTest extends SqlIntegrationTestCase
{
    /** @var ReferenceEntityRepositoryInterface */
    private $repository;

    /** @var AttributeRepositoryInterface */
    private $attributeRepository;

    public function setUp(): void
    {
        parent::setUp();

        $this->repository = $this->get('akeneo_referenceentity.infrastructure.persistence.repository.reference_entity');
        $this->attributeRepository = $this->get('akeneo_referenceentity.infrastructure.persistence.repository.attribute');
        $this->resetDB();
    }

    /**
     * @test
     */
    public function it_creates_a_reference_entity_and_returns_it()
    {
        $identifier = ReferenceEntityIdentifier::fromString('identifier');
        $referenceEntity = ReferenceEntity::create($identifier, ['en_US' => 'Designer', 'fr_FR' => 'Concepteur'], Image::createEmpty());

        $this->repository->create($referenceEntity);

        $referenceEntityFound = $this->repository->getByIdentifier($identifier);
        $this->assertReferenceEntity($referenceEntity, $referenceEntityFound);
    }

    /**
     * @test
     */
    public function it_returns_all_reference_entities()
    {
        $designer = ReferenceEntity::create(
            ReferenceEntityIdentifier::fromString('designer'),
            ['en_US' => 'Designer'],
            Image::createEmpty()
        );
        $brand = ReferenceEntity::create(
            ReferenceEntityIdentifier::fromString('brand'),
            ['en_US' => 'Brand'],
            Image::createEmpty()
        );
        $this->repository->create($designer);
        $this->repository->create($brand);

        $referenceEntities = iterator_to_array($this->repository->all());
        $this->assertReferenceEntity($brand, $referenceEntities[0]);
        $this->assertReferenceEntity($designer, $referenceEntities[1]);
    }

    /**
     * @test
     */
    public function it_throws_when_creating_a_reference_entity_with_the_same_identifier()
    {
        $identifier = ReferenceEntityIdentifier::fromString('identifier');
        $referenceEntity = ReferenceEntity::create($identifier, ['en_US' => 'Designer', 'fr_FR' => 'Concepteur'], Image::createEmpty());
        $this->repository->create($referenceEntity);

        $this->expectException(DBALException::class);
        $this->repository->create($referenceEntity);
    }

    /**
     * @test
     */
    public function it_updates_a_reference_entity_and_returns_it()
    {
        $identifier = ReferenceEntityIdentifier::fromString('identifier');
        $referenceEntity = ReferenceEntity::create($identifier, ['en_US' => 'Designer', 'fr_FR' => 'Concepteur'], Image::createEmpty());
        $this->repository->create($referenceEntity);
        $referenceEntity->updateLabels(LabelCollection::fromArray(['en_US' => 'Stylist', 'fr_FR' => 'Styliste']));

        $file = new FileInfo();
        $file->setKey('/path/image.jpg');
        $file->setOriginalFilename('image.jpg');
        $referenceEntity->updateImage(Image::fromFileInfo($file));

        $this->repository->update($referenceEntity);

        $referenceEntityFound = $this->repository->getByIdentifier($identifier);
        $this->assertReferenceEntity($referenceEntity, $referenceEntityFound);
    }

    /**
     * @test
     */
    public function it_throws_if_the_identifier_is_not_found()
    {
        $this->expectException(ReferenceEntityNotFoundException::class);
        $this->repository->getByIdentifier(ReferenceEntityIdentifier::fromString('unknown_identifier'));
    }

    /**
     * @test
     */
    public function it_deletes_a_reference_entity_given_an_identifier()
    {
        $identifier = ReferenceEntityIdentifier::fromString('identifier');
        $referenceEntity = ReferenceEntity::create($identifier, ['en_US' => 'Designer', 'fr_FR' => 'Concepteur'], Image::createEmpty());
        $this->repository->create($referenceEntity);

        $this->repository->deleteByIdentifier($identifier);

        $this->expectException(ReferenceEntityNotFoundException::class);
        $this->repository->getByIdentifier($identifier);
    }

    /**
     * @test
     */
    public function it_deletes_a_reference_entity_given_an_identifier_even_if_it_has_attributes()
    {
        $referenceEntityIdentifier = ReferenceEntityIdentifier::fromString('designer');
        $referenceEntity = ReferenceEntity::create($referenceEntityIdentifier, ['en_US' => 'Designer', 'fr_FR' => 'Concepteur'], Image::createEmpty());
        $this->repository->create($referenceEntity);

        $identifier = AttributeIdentifier::create('designer', 'name', 'test');
        $referenceEntityIdentifier = ReferenceEntityIdentifier::fromString('designer');
        $attribute = TextAttribute::createText(
            $identifier,
            $referenceEntityIdentifier,
            AttributeCode::fromString('name'),
            LabelCollection::fromArray(['en_US' => 'Name', 'fr_FR' => 'Nom']),
            AttributeOrder::fromInteger(2),
            AttributeIsRequired::fromBoolean(true),
            AttributeValuePerChannel::fromBoolean(false),
            AttributeValuePerLocale::fromBoolean(false),
            AttributeMaxLength::fromInteger(255),
            AttributeValidationRule::none(),
            AttributeRegularExpression::createEmpty()
        );
        $this->attributeRepository->create($attribute);

        $this->repository->deleteByIdentifier($referenceEntityIdentifier);

        $this->expectException(ReferenceEntityNotFoundException::class);
        $this->repository->getByIdentifier($referenceEntityIdentifier);
    }

    /**
     * @test
     */
    public function it_counts_all_reference_entities()
    {
        $this->assertEquals(0, $this->repository->count());

        $designerIdentifier = ReferenceEntityIdentifier::fromString('designer');
        $designer = ReferenceEntity::create($designerIdentifier, ['en_US' => 'Designer', 'fr_FR' => 'Concepteur'], Image::createEmpty());
        $brandIdentifier = ReferenceEntityIdentifier::fromString('brand');
        $brand = ReferenceEntity::create($brandIdentifier, ['en_US' => 'Brand', 'fr_FR' => 'Marque'], Image::createEmpty());

        $this->repository->create($designer);
        $this->repository->create($brand);

        $this->assertEquals(2, $this->repository->count());
    }

    /**
     * @test
     */
    public function it_throws_an_exception_if_it_tries_to_delete_an_unknown_reference_entity()
    {
        $identifier = ReferenceEntityIdentifier::fromString('unknown');

        $this->expectException(ReferenceEntityNotFoundException::class);
        $this->repository->deleteByIdentifier($identifier);
    }

    /**
     * @param $referenceEntityExpected
     * @param $referenceEntityFound
     *
     */
    private function assertReferenceEntity(
        ReferenceEntity $referenceEntityExpected,
        ReferenceEntity $referenceEntityFound
    ): void {
        $this->assertTrue($referenceEntityExpected->equals($referenceEntityFound));
        $labelCodesExpected = $referenceEntityExpected->getLabelCodes();
        $labelCodesFound = $referenceEntityFound->getLabelCodes();
        sort($labelCodesExpected);
        sort($labelCodesFound);
        $this->assertSame($labelCodesExpected, $labelCodesFound);
        foreach ($referenceEntityExpected->getLabelCodes() as $localeCode) {
            $this->assertEquals($referenceEntityExpected->getLabel($localeCode),
                $referenceEntityFound->getLabel($localeCode));
        }
    }

    private function resetDB()
    {
        $this->get('akeneoreference_entity.tests.helper.database_helper')->resetDatabase();
    }
}
