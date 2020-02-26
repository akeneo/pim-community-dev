<?php

declare(strict_types=1);

/*
 * This file is part of the Akeneo PIM Enterprise Edition.
 *
 * (c) 2019 Akeneo SAS (http://www.akeneo.com)
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Pimee\Upgrade\Schema\Tests;

use Akeneo\Pim\Enrichment\Component\Product\Model\ProductModelInterface;
use Akeneo\Pim\Structure\Component\AttributeTypes;
use Akeneo\Test\Integration\Configuration;
use Akeneo\Test\Integration\TestCase;

final class Version_5_0_20200226102033_data_quality_insights_init_variant_products_evaluations_Integration extends TestCase
{
    public function test_it_schedules_variant_products_evaluations()
    {
        $this->createFamily();
        $this->createFamilyVariant();

        $shirtProductModel = $this->createProductModel(['code' => 'a_shirt', 'family_variant' => 'shirt_size']);
        $this->createProduct('a_small_shirt', 'family', $shirtProductModel);
        $this->createProduct('a_medium_shirt', 'family', $shirtProductModel);
        $this->createProduct('a_large_shirt', 'family', $shirtProductModel);

        $this->createProduct('a_simple_product', 'family');
        $this->createProduct('another_simple_product', 'family');

        $this->get('database_connection')->executeQuery(
            "TRUNCATE pimee_data_quality_insights_criteria_evaluation"
        );

        $resultUp = $this->get('pim_catalog.command_launcher')->executeForeground(
            sprintf('doctrine:migrations:execute %s --up -n', $this->getMigrationLabel())
        );
        self::assertEquals(0, $resultUp->getCommandStatus(), \json_encode($resultUp->getCommandOutput()));

        $stmt = $this->get('database_connection')->executeQuery(
            "SELECT count(*) FROM pimee_data_quality_insights_criteria_evaluation",
        );
        $this->assertSame(18, intval($stmt->fetchColumn()));
    }

    private function createFamily(): string
    {
        $this->createAttributeSimpleSelect();

        $family = $this
            ->get('akeneo_ee_integration_tests.builder.family')
            ->build([
                'code' => 'family',
                'attributes' => ['a_simple_select'],
            ]);
        $this->get('pim_catalog.saver.family')->save($family);

        return $family->getCode();
    }

    private function createAttributeSimpleSelect(): void
    {
        $attribute = $this->get('akeneo_integration_tests.base.attribute.builder')->build([
            'code' => 'a_simple_select',
            'type' => AttributeTypes::OPTION_SIMPLE_SELECT,
            'unique' => false,
            'group' => 'other',
            'localizable' => false
        ], true);

        $this->get('pim_catalog.saver.attribute')->save($attribute);
    }

    private function createFamilyVariant()
    {
        $family = $this->get('pim_catalog.factory.family_variant')->create();
        $this->get('pim_catalog.updater.family_variant')->update($family, [
            'code' => 'shirt_size',
            'family' => 'family',
            'variant_attribute_sets' => [
                ['axes' => ['a_simple_select'], 'level' => 1],
            ],
        ]);
        $this->get('pim_catalog.saver.family_variant')->save($family);
    }

    private function createProductModel(array $data = [])
    {
        $productModel = $this->get('pim_catalog.factory.product_model')->create();
        $this->get('pim_catalog.updater.product_model')->update($productModel, $data);
        $this->get('pim_catalog.saver.product_model')->save($productModel);

        return $productModel;
    }

    private function createProduct(string $identifier, string $familyCode, ?ProductModelInterface $productModel = null, array $values = [])
    {
        $product = $this->get('pim_catalog.builder.product')->createProduct($identifier, $familyCode);
        if (null !== $productModel) {
            $product->setParent($productModel);
        }
        $this->get('pim_catalog.updater.product')->update($product, ['values' => $values]);
        $this->get('pim_catalog.saver.product')->save($product);
    }

    private function getMigrationLabel(): string
    {
        $migration = (new \ReflectionClass($this))->getShortName();
        $migration = str_replace('_Integration', '', $migration);
        $migration = str_replace('Version', '', $migration);

        return $migration;
    }

    protected function getConfiguration(): Configuration
    {
        return $this->catalog->useMinimalCatalog();
    }
}
