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
use Akeneo\ReferenceEntity\Domain\Model\ReferenceEntity\AttributeAsLabelReference;
use Akeneo\ReferenceEntity\Domain\Model\ReferenceEntity\ReferenceEntity;
use Akeneo\ReferenceEntity\Domain\Model\ReferenceEntity\ReferenceEntityIdentifier;
use Akeneo\ReferenceEntity\Domain\Query\ReferenceEntity\FindReferenceEntityAttributeAsLabelInterface;
use Akeneo\ReferenceEntity\Integration\SqlIntegrationTestCase;

class SqlFindReferenceEntityAttributeAsLabelTest extends SqlIntegrationTestCase
{
    /** @var FindReferenceEntityAttributeAsLabelInterface */
    private $findAttributeAsLabel;

    public function setUp()
    {
        parent::setUp();

        $this->findAttributeAsLabel = $this->get('akeneo_referenceentity.infrastructure.persistence.query.find_reference_entity_attribute_as_label');
        $this->resetDB();
        $this->loadFixtures();
    }

    /**
     * @test
     */
    public function it_finds_the_attribute_as_label_of_a_reference_entity()
    {
        $referenceEntityRepository = $this->get('akeneo_referenceentity.infrastructure.persistence.repository.reference_entity');
        $referenceEntity = $referenceEntityRepository->getByIdentifier(ReferenceEntityIdentifier::fromString('designer'));

        $expectedAttributeAsLabel = $referenceEntity->getAttributeAsLabelReference();
        $referenceEntityIdentifier = ReferenceEntityIdentifier::fromString('designer');
        $attributeAsLabel = ($this->findAttributeAsLabel)($referenceEntityIdentifier);

        $this->assertEquals($expectedAttributeAsLabel, $attributeAsLabel);
    }

    /**
     * @test
     */
    public function it_returns_an_empty_attribute_as_label_if_the_reference_entity_was_not_found()
    {
        $referenceEntityIdentifier = ReferenceEntityIdentifier::fromString('unknown');
        $attributeAsLabel = ($this->findAttributeAsLabel)($referenceEntityIdentifier);

        $this->assertTrue($attributeAsLabel->isEmpty());
    }

    private function loadFixtures(): void
    {
        $referenceEntityRepository = $this->get('akeneo_referenceentity.infrastructure.persistence.repository.reference_entity');
        $referenceEntity = ReferenceEntity::create(
            ReferenceEntityIdentifier::fromString('designer'),
            [],
            Image::createEmpty()
        );
        $referenceEntityRepository->create($referenceEntity);
    }

    private function resetDB(): void
    {
        $this->get('akeneoreference_entity.tests.helper.database_helper')->resetDatabase();
    }
}
