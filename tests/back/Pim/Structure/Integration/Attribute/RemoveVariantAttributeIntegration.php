<?php

declare(strict_types=1);

namespace AkeneoTest\Pim\Structure\Integration\Attribute;

use Akeneo\Test\Integration\Configuration;
use Akeneo\Test\Integration\TestCase;

/**
 * @author    Elodie Raposo (elodie.raposo@akeneo.com)
 * @copyright 2017 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php Open Software License (OSL 3.0)
 */
class RemoveVariantAttributeIntegration extends TestCase
{
    public function testRemoveAnAttributeRemoveAlsoVariantAttributeLinked()
    {
        $attributeToRemove = $this->get('pim_catalog.repository.attribute')->findOneByIdentifier('composition');

        $familyVariant = $this->get('pim_catalog.repository.family_variant')
            ->findOneByIdentifier('clothing_material_size');
        $nbVariantAttributeBeforeRemove = count($familyVariant->getVariantAttributeSet(1)->getAttributes());

        $this->get('pim_catalog.remover.attribute')->remove($attributeToRemove);
        $this->get('doctrine.orm.default_entity_manager')->clear();

        $familyVariant = $this->get('pim_catalog.repository.family_variant')
            ->findOneByIdentifier('clothing_material_size');
        $nbVariantAttributeAfterRemove = count($familyVariant->getVariantAttributeSet(1)->getAttributes());

        $this->assertNull($this->get('pim_catalog.repository.attribute')->findOneByIdentifier('composition'));
        $this->assertNotEquals($nbVariantAttributeBeforeRemove, $nbVariantAttributeAfterRemove);
    }

    /**
     * {@inheritdoc}
     */
    protected function getConfiguration(): Configuration
    {
        return $this->catalog->useFunctionalCatalog('catalog_modeling');
    }
}
