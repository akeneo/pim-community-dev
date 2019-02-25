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
use Akeneo\ReferenceEntity\Domain\Model\Attribute\AttributeOrder;
use Akeneo\ReferenceEntity\Domain\Model\Attribute\AttributeValuePerChannel;
use Akeneo\ReferenceEntity\Domain\Model\Attribute\AttributeValuePerLocale;
use Akeneo\ReferenceEntity\Domain\Model\Attribute\RecordAttribute;
use Akeneo\ReferenceEntity\Domain\Model\Image;
use Akeneo\ReferenceEntity\Domain\Model\LabelCollection;
use Akeneo\ReferenceEntity\Domain\Model\ReferenceEntity\ReferenceEntity;
use Akeneo\ReferenceEntity\Domain\Model\ReferenceEntity\ReferenceEntityIdentifier;
use Akeneo\ReferenceEntity\Domain\Query\ReferenceEntity\ReferenceEntityIsLinkedToAtLeastOneReferenceEntityAttributeInterface;
use Akeneo\ReferenceEntity\Integration\SqlIntegrationTestCase;

/**
 * @author    Adrien Pétremann <adrien.petremann@akeneo.com>
 * @copyright 2018 Akeneo SAS (https://www.akeneo.com)
 */
class SqlReferenceEntityIsLinkedToAtLeastOneReferenceEntityAttributeTest extends SqlIntegrationTestCase
{
    /** @var ReferenceEntityIsLinkedToAtLeastOneReferenceEntityAttributeInterface */
    private $query;

    public function setUp(): void
    {
        parent::setUp();

        $this->query = $this->get('akeneo_referenceentity.infrastructure.persistence.query.reference_entity_is_linked_to_at_least_one_reference_entity_attribute');
        $this->resetDB();
        $this->loadReferenceEntity();
    }

    /**
     * @test
     */
    public function it_tells_if_a_reference_entity_is_linked_to_at_least_one_reference_entity_attribute()
    {
        $identifier = ReferenceEntityIdentifier::fromString('designer');
        $isLinked = ($this->query)($identifier);
        $this->assertTrue($isLinked);

        $identifier = ReferenceEntityIdentifier::fromString('brand');
        $isLinked = ($this->query)($identifier);
        $this->assertTrue($isLinked);

        $identifier = ReferenceEntityIdentifier::fromString('color');
        $isLinked = ($this->query)($identifier);
        $this->assertFalse($isLinked);
    }

    private function resetDB(): void
    {
        $this->get('akeneoreference_entity.tests.helper.database_helper')->resetDatabase();
    }

    private function loadReferenceEntity(): void
    {
        $referenceEntityRepository = $this->get('akeneo_referenceentity.infrastructure.persistence.repository.reference_entity');
        $attributeRepository = $this->get('akeneo_referenceentity.infrastructure.persistence.repository.attribute');

        $designer = ReferenceEntity::create(
            ReferenceEntityIdentifier::fromString('designer'),
            ['fr_FR' => 'Concepteur', 'en_US' => 'Designer'],
            Image::createEmpty()
        );
        $brand = ReferenceEntity::create(
            ReferenceEntityIdentifier::fromString('brand'),
            ['fr_FR' => 'Marque', 'en_US' => 'Brand'],
            Image::createEmpty()
        );
        $color = ReferenceEntity::create(
            ReferenceEntityIdentifier::fromString('color'),
            ['fr_FR' => 'Couleur', 'en_US' => 'Color'],
            Image::createEmpty()
        );
        $referenceEntityRepository->create($designer);
        $referenceEntityRepository->create($brand);
        $referenceEntityRepository->create($color);

        $mentor = RecordAttribute::create(
            AttributeIdentifier::fromString('mentor_designer_fingerprint'),
            ReferenceEntityIdentifier::fromString('designer'),
            AttributeCode::fromString('mentor'),
            LabelCollection::fromArray(['en_US' => 'Mentor']),
            AttributeOrder::fromInteger(5),
            AttributeIsRequired::fromBoolean(false),
            AttributeValuePerChannel::fromBoolean(false),
            AttributeValuePerLocale::fromBoolean(false),
            ReferenceEntityIdentifier::fromString('designer')
        );
        $brands = RecordAttribute::create(
            AttributeIdentifier::fromString('brands_designer_fingerprint'),
            ReferenceEntityIdentifier::fromString('designer'),
            AttributeCode::fromString('brands'),
            LabelCollection::fromArray(['en_US' => 'Brands']),
            AttributeOrder::fromInteger(6),
            AttributeIsRequired::fromBoolean(false),
            AttributeValuePerChannel::fromBoolean(false),
            AttributeValuePerLocale::fromBoolean(false),
            ReferenceEntityIdentifier::fromString('brand')
        );
        $attributeRepository->create($mentor);
        $attributeRepository->create($brands);
    }
}
