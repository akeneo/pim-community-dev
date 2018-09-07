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

use Akeneo\EnrichedEntity\Domain\Model\EnrichedEntity\EnrichedEntity;
use Akeneo\EnrichedEntity\Domain\Model\EnrichedEntity\EnrichedEntityIdentifier;
use Akeneo\EnrichedEntity\Domain\Query\EnrichedEntity\EnrichedEntityIsLinkedToAtLeastOneProductAttributeInterface;
use Akeneo\EnrichedEntity\tests\back\Integration\SqlIntegrationTestCase;
use Akeneo\Pim\EnrichedEntity\Component\AttributeType\EnrichedEntityCollectionType;
use Akeneo\Pim\Structure\Component\Model\AttributeGroup;

/**
 * @author    Adrien Pétremann <adrien.petremann@akeneo.com>
 * @copyright 2018 Akeneo SAS (https://www.akeneo.com)
 */
class SqlEnrichedEntityIsLinkedToAtLeastOneProductAttributeTest extends SqlIntegrationTestCase
{
    /** @var EnrichedEntityIsLinkedToAtLeastOneProductAttributeInterface */
    private $query;

    public function setUp()
    {
        parent::setUp();

        $this->query = $this->get('akeneo_enrichedentity.infrastructure.persistence.query.enriched_entity_is_linked_to_at_least_one_product_attribute');
        $this->resetDB();
        $this->loadEnrichedEntity();
        $this->loadAttributeGroupAndAttribute();
    }

    /**
     * @test
     */
    public function it_tells_if_an_enriched_entity_is_linked_to_at_least_one_product_attribute()
    {
        $identifier = EnrichedEntityIdentifier::fromString('designer');
        $isLinked = ($this->query)($identifier);
        $this->assertTrue($isLinked);

        $identifier = EnrichedEntityIdentifier::fromString('brand');
        $isLinked = ($this->query)($identifier);
        $this->assertFalse($isLinked);
    }

    private function resetDB(): void
    {
        $this->get('akeneo_ee_integration_tests.helper.database_helper')->resetDatabase();
    }

    private function loadEnrichedEntity(): void
    {
        $enrichedEntityRepository = $this->get('akeneo_enrichedentity.infrastructure.persistence.repository.enriched_entity');
        $enrichedEntity = EnrichedEntity::create(
            EnrichedEntityIdentifier::fromString('designer'),
            [
                'fr_FR' => 'Concepteur',
                'en_US' => 'Designer',
            ],
            null
        );
        $enrichedEntityRepository->create($enrichedEntity);
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

        $attribute = $this->get('pim_catalog.factory.attribute')
            ->createAttribute(EnrichedEntityCollectionType::ENRICHED_ENTITY_COLLECTION);
        $this->get('pim_catalog.updater.attribute')
            ->update($attribute, [
                'code' => 'main_designer',
                'reference_data_name' => 'designer',
                'group' => 'other'
            ]);

        $errors = $this->get('validator')->validate($attribute);
        if ($errors->count() > 0) {
            throw new \Exception(
                sprintf(
                    'Cannot create the attribute "%s": %s',
                    $attribute->getCode(),
                    (string) $errors[0]
                )
            );
        }

        $this->get('pim_catalog.saver.attribute')->save($attribute);
    }
}
