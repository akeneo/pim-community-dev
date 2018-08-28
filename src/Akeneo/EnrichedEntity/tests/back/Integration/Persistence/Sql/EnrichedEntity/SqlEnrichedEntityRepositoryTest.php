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

namespace Akeneo\EnrichedEntity\tests\back\Integration\Persistence\Sql\EnrichedEntity;

use Akeneo\EnrichedEntity\Domain\Model\Attribute\AttributeCode;
use Akeneo\EnrichedEntity\Domain\Model\Attribute\AttributeIdentifier;
use Akeneo\EnrichedEntity\Domain\Model\Attribute\AttributeMaxLength;
use Akeneo\EnrichedEntity\Domain\Model\Attribute\AttributeOrder;
use Akeneo\EnrichedEntity\Domain\Model\Attribute\AttributeRequired;
use Akeneo\EnrichedEntity\Domain\Model\Attribute\AttributeValuePerChannel;
use Akeneo\EnrichedEntity\Domain\Model\Attribute\AttributeValuePerLocale;
use Akeneo\EnrichedEntity\Domain\Model\Attribute\TextAttribute;
use Akeneo\EnrichedEntity\Domain\Model\EnrichedEntity\EnrichedEntity;
use Akeneo\EnrichedEntity\Domain\Model\EnrichedEntity\EnrichedEntityIdentifier;
use Akeneo\EnrichedEntity\Domain\Model\Image;
use Akeneo\EnrichedEntity\Domain\Model\LabelCollection;
use Akeneo\EnrichedEntity\Domain\Repository\AttributeRepositoryInterface;
use Akeneo\EnrichedEntity\Domain\Repository\EnrichedEntityNotFoundException;
use Akeneo\EnrichedEntity\Domain\Repository\EnrichedEntityRepositoryInterface;
use Akeneo\EnrichedEntity\tests\back\Integration\SqlIntegrationTestCase;
use Doctrine\DBAL\DBALException;

class SqlEnrichedEntityRepositoryTest extends SqlIntegrationTestCase
{
    /** @var EnrichedEntityRepositoryInterface */
    private $repository;

    /** @var AttributeRepositoryInterface */
    private $attributeRepository;

    public function setUp()
    {
        parent::setUp();

        $this->repository = $this->get('akeneo_enrichedentity.infrastructure.persistence.enriched_entity');
        $this->attributeRepository = $this->get('akeneo_enrichedentity.infrastructure.persistence.attribute');
        $this->resetDB();
    }

    /**
     * @test
     */
    public function it_creates_an_enriched_entity_and_returns_it()
    {
        $identifier = EnrichedEntityIdentifier::fromString('identifier');
        $enrichedEntity = EnrichedEntity::create($identifier, ['en_US' => 'Designer', 'fr_FR' => 'Concepteur']);

        $this->repository->create($enrichedEntity);

        $enrichedEntityFound = $this->repository->getByIdentifier($identifier);
        $this->assertEnrichedEntity($enrichedEntity, $enrichedEntityFound);
    }

    /**
     * @test
     */
    public function it_throws_when_creating_an_enriched_entity_with_the_same_identifier()
    {
        $identifier = EnrichedEntityIdentifier::fromString('identifier');
        $enrichedEntity = EnrichedEntity::create($identifier, ['en_US' => 'Designer', 'fr_FR' => 'Concepteur']);
        $this->repository->create($enrichedEntity);

        $this->expectException(DBALException::class);
        $this->repository->create($enrichedEntity);
    }

    /**
     * @test
     */
    public function it_updates_an_enriched_entity_and_returns_it()
    {
        $identifier = EnrichedEntityIdentifier::fromString('identifier');
        $enrichedEntity = EnrichedEntity::create($identifier, ['en_US' => 'Designer', 'fr_FR' => 'Concepteur']);
        $this->repository->create($enrichedEntity);
        $enrichedEntity->updateLabels(LabelCollection::fromArray(['en_US' => 'Stylist', 'fr_FR' => 'Styliste']));
        $enrichedEntity->updateImage(Image::fromFileInfo('/path/image.jpg', 'image.jpg'));

        $this->repository->update($enrichedEntity);

        $enrichedEntityFound = $this->repository->getByIdentifier($identifier);
        $this->assertEnrichedEntity($enrichedEntity, $enrichedEntityFound);
    }

    /**
     * @test
     */
    public function it_throws_if_the_identifier_is_not_found()
    {
        $this->expectException(EnrichedEntityNotFoundException::class);
        $this->repository->getByIdentifier(EnrichedEntityIdentifier::fromString('unknown_identifier'));
    }

    /**
     * @test
     */
    public function it_deletes_an_enriched_entity_given_an_identifier()
    {
        $identifier = EnrichedEntityIdentifier::fromString('identifier');
        $enrichedEntity = EnrichedEntity::create($identifier, ['en_US' => 'Designer', 'fr_FR' => 'Concepteur']);
        $this->repository->create($enrichedEntity);

        $this->repository->deleteByIdentifier($identifier);

        $this->expectException(EnrichedEntityNotFoundException::class);
        $this->repository->getByIdentifier($identifier);
    }

    /**
     * @test
     */
    public function it_deletes_an_enriched_entity_given_an_identifier_even_if_it_has_attributes()
    {
        $enrichedEntityIdentifier = EnrichedEntityIdentifier::fromString('designer');
        $enrichedEntity = EnrichedEntity::create($enrichedEntityIdentifier, ['en_US' => 'Designer', 'fr_FR' => 'Concepteur']);
        $this->repository->create($enrichedEntity);

        $identifier = AttributeIdentifier::create('designer', 'name');
        $enrichedEntityIdentifier = EnrichedEntityIdentifier::fromString('designer');
        $expectedAttribute = TextAttribute::create(
            $identifier,
            $enrichedEntityIdentifier,
            AttributeCode::fromString('name'),
            LabelCollection::fromArray(['en_US' => 'Name', 'fr_FR' => 'Nom']),
            AttributeOrder::fromInteger(0),
            AttributeRequired::fromBoolean(true),
            AttributeValuePerChannel::fromBoolean(false),
            AttributeValuePerLocale::fromBoolean(false),
            AttributeMaxLength::fromInteger(255)
        );
        $this->attributeRepository->create($expectedAttribute);

        $this->repository->deleteByIdentifier($enrichedEntityIdentifier);

        $this->expectException(EnrichedEntityNotFoundException::class);
        $this->repository->getByIdentifier($enrichedEntityIdentifier);
    }

    /**
     * @test
     */
    public function it_throws_an_exception_if_it_tries_to_delete_an_unknown_enriched_entity()
    {
        $identifier = EnrichedEntityIdentifier::fromString('unknown');

        $this->expectException(EnrichedEntityNotFoundException::class);
        $this->repository->deleteByIdentifier($identifier);
    }

    /**
     * @param $enrichedEntityExpected
     * @param $enrichedEntityFound
     *
     */
    private function assertEnrichedEntity(
        EnrichedEntity $enrichedEntityExpected,
        EnrichedEntity $enrichedEntityFound
    ): void {
        $this->assertTrue($enrichedEntityExpected->equals($enrichedEntityFound));
        $labelCodesExpected = $enrichedEntityExpected->getLabelCodes();
        $labelCodesFound = $enrichedEntityFound->getLabelCodes();
        sort($labelCodesExpected);
        sort($labelCodesFound);
        $this->assertSame($labelCodesExpected, $labelCodesFound);
        foreach ($enrichedEntityExpected->getLabelCodes() as $localeCode) {
            $this->assertEquals($enrichedEntityExpected->getLabel($localeCode),
                $enrichedEntityFound->getLabel($localeCode));
        }
    }

    private function resetDB()
    {
        $this->get('akeneo_ee_integration_tests.helper.database_helper')->resetDatabase();
    }
}
