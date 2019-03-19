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

use Akeneo\Pim\Enrichment\ReferenceEntity\Component\AttributeType\ReferenceEntityCollectionType;
use Akeneo\Pim\Enrichment\ReferenceEntity\Component\AttributeType\ReferenceEntityType;
use Akeneo\Pim\Structure\Component\Model\AttributeGroup;
use Akeneo\ReferenceEntity\Domain\Model\Image;
use Akeneo\ReferenceEntity\Domain\Model\ReferenceEntity\ReferenceEntity;
use Akeneo\ReferenceEntity\Domain\Model\ReferenceEntity\ReferenceEntityIdentifier;
use Akeneo\ReferenceEntity\Domain\Query\ReferenceEntity\ReferenceEntityIsLinkedToAtLeastOneProductAttributeInterface;
use Akeneo\ReferenceEntity\Integration\SqlIntegrationTestCase;

/**
 * @author    Adrien Pétremann <adrien.petremann@akeneo.com>
 * @copyright 2018 Akeneo SAS (https://www.akeneo.com)
 */
class SqlReferenceEntityIsLinkedToAtLeastOneProductAttributeTest extends SqlIntegrationTestCase
{
    /** @var ReferenceEntityIsLinkedToAtLeastOneProductAttributeInterface */
    private $query;

    public function setUp()
    {
        parent::setUp();

        $this->query = $this->get('akeneo_referenceentity.infrastructure.persistence.query.reference_entity_is_linked_to_at_least_one_product_attribute');
        $this->resetDB();
        $this->loadReferenceEntity();
        $this->loadAttributeGroupAndAttribute();
    }

    /**
     * @test
     */
    public function it_tells_if_a_reference_entity_is_linked_to_at_least_one_product_attribute()
    {
        $identifier = ReferenceEntityIdentifier::fromString('designer');
        $isLinked = ($this->query)($identifier);
        $this->assertTrue($isLinked);

        $identifier = ReferenceEntityIdentifier::fromString('city');
        $isLinked = ($this->query)($identifier);
        $this->assertTrue($isLinked);

        $identifier = ReferenceEntityIdentifier::fromString('brand');
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

        $referenceEntity = ReferenceEntity::create(
            ReferenceEntityIdentifier::fromString('designer'),
            [
                'fr_FR' => 'Concepteur',
                'en_US' => 'Designer',
            ],
            Image::createEmpty()
        );
        $referenceEntityRepository->create($referenceEntity);

        $referenceEntity = ReferenceEntity::create(
            ReferenceEntityIdentifier::fromString('city'),
            [
                'fr_FR' => 'Ville',
                'en_US' => 'City',
            ],
            Image::createEmpty()
        );
        $referenceEntityRepository->create($referenceEntity);
    }

    private function loadAttributeGroupAndAttribute(): void
    {
        $attributeGroup = new AttributeGroup();
        $this->get('pim_catalog.updater.attribute_group')
            ->update($attributeGroup, [
                'code' => 'other'
            ]);

        $errors = $this->get('validator')->validate($attributeGroup);
        if ($errors->count() > 0) {
            throw new \Exception(
                sprintf(
                    'Cannot create the attribute group "%s": %s',
                    $attributeGroup->getCode(),
                    (string) $errors[0]
                )
            );
        }

        $this->get('pim_catalog.saver.attribute_group')->save($attributeGroup);

        $attributeReferenceEntityCollection = $this->get('pim_catalog.factory.attribute')
            ->createAttribute(ReferenceEntityCollectionType::REFERENCE_ENTITY_COLLECTION);
        $this->get('pim_catalog.updater.attribute')
            ->update($attributeReferenceEntityCollection, [
                'code' => 'main_designer',
                'reference_data_name' => 'designer',
                'group' => 'other'
            ]);

        $errors = $this->get('validator')->validate($attributeReferenceEntityCollection);
        if ($errors->count() > 0) {
            throw new \Exception(
                sprintf(
                    'Cannot create the attribute "%s": %s',
                    $attributeReferenceEntityCollection->getCode(),
                    (string) $errors[0]
                )
            );
        }

        $this->get('pim_catalog.saver.attribute')->save($attributeReferenceEntityCollection);

        $attributeReferenceEntity = $this->get('pim_catalog.factory.attribute')
            ->createAttribute(ReferenceEntityType::REFERENCE_ENTITY);
        $this->get('pim_catalog.updater.attribute')
            ->update($attributeReferenceEntity, [
                'code' => 'main_city',
                'reference_data_name' => 'city',
                'group' => 'other'
            ]);

        $errors = $this->get('validator')->validate($attributeReferenceEntity);
        if ($errors->count() > 0) {
            throw new \Exception(
                sprintf(
                    'Cannot create the attribute "%s": %s',
                    $attributeReferenceEntity->getCode(),
                    (string) $errors[0]
                )
            );
        }

        $this->get('pim_catalog.saver.attribute')->save($attributeReferenceEntity);
    }
}
