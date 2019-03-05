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

namespace Akeneo\ReferenceEntity\Integration\PublicApi\Analytics;

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
use Akeneo\ReferenceEntity\Infrastructure\PublicApi\Analytics\SqlAverageMaxNumberOfAttributesPerReferenceEntity;
use Akeneo\ReferenceEntity\Integration\SqlIntegrationTestCase;
use Ramsey\Uuid\Uuid;

/**
 * @author    Samir Boulil <samir.boulil@akeneo.com>
 * @copyright 2018 Akeneo SAS (http://www.akeneo.com)
 */
class SqlAverageMaxNumberOfAttributesPerReferenceEntityTest extends SqlIntegrationTestCase
{
    /** @var SqlAverageMaxNumberOfAttributesPerReferenceEntity */
    private $averageMaxNumberOfAttributesPerReferenceEntity;

    public function setUp()
    {
        parent::setUp();

        $this->averageMaxNumberOfAttributesPerReferenceEntity = $this->get('akeneo_referenceentity.infrastructure.persistence.query.analytics.average_max_number_of_attributes_per_reference_entity');
        $this->resetDB();
    }

    private function resetDB(): void
    {
        $this->get('akeneoreference_entity.tests.helper.database_helper')->resetDatabase();
    }

    /**
     * @test
     */
    public function it_returns_the_average_and_max_number_of_attributes_per_reference_entity()
    {
        $this->loadAttributesForReferenceEntity(2);
        $this->loadAttributesForReferenceEntity(4);
        $this->loadAttributesForReferenceEntity(0);

        $volume = $this->averageMaxNumberOfAttributesPerReferenceEntity->fetch();

        $this->assertEquals('4', $volume->getMaxVolume());
        $this->assertEquals('3', $volume->getAverageVolume());
    }

    private function loadAttributesForReferenceEntity(int $numberOfAttributesPerReferenceEntitiestoLoad): void
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
        for ($i = 0; $i < $numberOfAttributesPerReferenceEntitiestoLoad - 2; $i++) {
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
