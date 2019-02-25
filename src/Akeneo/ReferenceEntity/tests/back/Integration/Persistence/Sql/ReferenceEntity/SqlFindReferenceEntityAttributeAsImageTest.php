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
use Akeneo\ReferenceEntity\Domain\Model\ReferenceEntity\AttributeAsImageReference;
use Akeneo\ReferenceEntity\Domain\Model\ReferenceEntity\ReferenceEntity;
use Akeneo\ReferenceEntity\Domain\Model\ReferenceEntity\ReferenceEntityIdentifier;
use Akeneo\ReferenceEntity\Domain\Query\ReferenceEntity\FindReferenceEntityAttributeAsLabelInterface;
use Akeneo\ReferenceEntity\Integration\SqlIntegrationTestCase;

class SqlFindReferenceEntityAttributeAsImageTest extends SqlIntegrationTestCase
{
    /** @var FindReferenceEntityAttributeAsLabelInterface */
    private $findAttributeAsImage;

    public function setUp(): void
    {
        parent::setUp();

        $this->findAttributeAsImage = $this->get('akeneo_referenceentity.infrastructure.persistence.query.find_reference_entity_attribute_as_image');
        $this->resetDB();
        $this->loadFixtures();
    }

    /**
     * @test
     */
    public function it_finds_the_attribute_as_image_of_a_reference_entity()
    {
        $referenceEntityRepository = $this->get('akeneo_referenceentity.infrastructure.persistence.repository.reference_entity');
        $referenceEntity = $referenceEntityRepository->getByIdentifier(ReferenceEntityIdentifier::fromString('designer'));

        $expectedAttributeAsImage = $referenceEntity->getAttributeAsImageReference();
        $referenceEntityIdentifier = ReferenceEntityIdentifier::fromString('designer');
        $attributeAsImage = ($this->findAttributeAsImage)($referenceEntityIdentifier);

        $this->assertEquals($expectedAttributeAsImage, $attributeAsImage);
    }

    /**
     * @test
     */
    public function it_returns_an_empty_attribute_as_image_if_the_reference_entity_was_not_found()
    {
        $referenceEntityIdentifier = ReferenceEntityIdentifier::fromString('unknown');
        $attributeAsImage = ($this->findAttributeAsImage)($referenceEntityIdentifier);

        $this->assertTrue($attributeAsImage->isEmpty());
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
