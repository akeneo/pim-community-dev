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

namespace Akeneo\ReferenceEntity\Integration\Persistence\Sql\Record;

use Akeneo\Platform\Component\CatalogVolumeMonitoring\Volume\Query\AverageMaxQuery;
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
use Akeneo\ReferenceEntity\Domain\Model\Record\Record;
use Akeneo\ReferenceEntity\Domain\Model\Record\RecordCode;
use Akeneo\ReferenceEntity\Domain\Model\Record\RecordIdentifier;
use Akeneo\ReferenceEntity\Domain\Model\Record\Value\ValueCollection;
use Akeneo\ReferenceEntity\Domain\Model\ReferenceEntity\ReferenceEntity;
use Akeneo\ReferenceEntity\Domain\Model\ReferenceEntity\ReferenceEntityIdentifier;
use Akeneo\ReferenceEntity\Integration\SqlIntegrationTestCase;
use Ramsey\Uuid\Uuid;

/**
 * @author    Samir Boulil <samir.boulil@akeneo.com>
 * @copyright 2018 Akeneo SAS (http://www.akeneo.com)
 */
class SqlAverageMaxNumberOfAttributesPerReferenceEntityTest extends SqlIntegrationTestCase
{
    /** @var AverageMaxQuery */
    private $averageMaxNumberOfAttributessPerReferenceEntity;

    public function setUp()
    {
        parent::setUp();

        $this->averageMaxNumberOfAttributessPerReferenceEntity = $this->get('akeneo_referenceentity.infrastructure.persistence.query.analytics.average_max_number_of_attributes_per_reference_entity');
        $this->resetDB();
    }

    /**
     * @test
     */
    public function it_returns_the_average_and_max_number_of_attributes_per_reference_entity()
    {
        $this->loadAttributesForReferenceEntity(2);
        $this->loadAttributesForReferenceEntity(4);

        $volume = $this->averageMaxNumberOfAttributessPerReferenceEntity->fetch();

        $this->assertEquals('4', $volume->getMaxVolume());
        $this->assertEquals('3', $volume->getAverageVolume());
        $this->assertEquals('average_max_attributes_per_reference_entity', $volume->getVolumeName());
        $this->assertFalse($volume->hasWarning(), 'There shouldn\'t be a warning for this reference entity volume');
    }

    private function resetDB(): void
    {
        $this->get('akeneoreference_entity.tests.helper.database_helper')->resetDatabase();
    }

    private function loadAttributesForReferenceEntity(int $numberOfRecordsPerReferenceEntitiestoLoad): void
    {
        $referenceEntityRepository = $this->get('akeneo_referenceentity.infrastructure.persistence.repository.reference_entity');
        $referenceEntityIdentifier = ReferenceEntityIdentifier::fromString($this->getRandomIdentifier());
        $referenceEntityRepository->create(ReferenceEntity::create(
            $referenceEntityIdentifier,
            [],
            Image::createEmpty()
        ));

        // By default, there are already 2 attributes created for each reference entity
        $attributeRepository = $this->get('akeneo_referenceentity.infrastructure.persistence.repository.attribute');
        for ($i=0; $i < $numberOfRecordsPerReferenceEntitiestoLoad - 2; $i++) {
            $attributeRepository->create(
                TextAttribute::createText(
                    AttributeIdentifier::fromString(sprintf('%s_%d', $i, $referenceEntityIdentifier->normalize())),
                    $referenceEntityIdentifier,
                    AttributeCode::fromString(sprintf('%d', $i)),
                    LabelCollection::fromArray(['en_US' => 'Name']),
                    AttributeOrder::fromInteger($i + 2),
                    AttributeIsRequired::fromBoolean(true),
                    AttributeValuePerChannel::fromBoolean(true),
                    AttributeValuePerLocale::fromBoolean(true),
                    AttributeMaxLength::fromInteger(155),
                    AttributeValidationRule::none(),
                    AttributeRegularExpression::createEmpty()
                )
            );
        }
    }

    private function getRandomIdentifier(): string
    {
        return str_replace('-', '_', Uuid::uuid4()->toString());
    }
}
