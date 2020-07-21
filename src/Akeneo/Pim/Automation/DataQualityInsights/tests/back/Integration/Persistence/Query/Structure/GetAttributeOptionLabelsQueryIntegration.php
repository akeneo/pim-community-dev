<?php

declare(strict_types=1);

/*
 * This file is part of the Akeneo PIM Enterprise Edition.
 *
 * (c) 2020 Akeneo SAS (http://www.akeneo.com)
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Akeneo\Test\Pim\Automation\DataQualityInsights\Integration\Persistence\Query\Structure;

use Akeneo\Pim\Automation\DataQualityInsights\Domain\ValueObject\AttributeCode;
use Akeneo\Pim\Automation\DataQualityInsights\Domain\ValueObject\AttributeOptionCode;
use Akeneo\Pim\Automation\DataQualityInsights\Infrastructure\Persistence\Query\Structure\GetAttributeOptionLabelsQuery;
use Akeneo\Pim\Structure\Component\AttributeTypes;
use Akeneo\Test\Integration\TestCase;

class GetAttributeOptionLabelsQueryIntegration extends TestCase
{
    protected function getConfiguration()
    {
        return $this->catalog->useMinimalCatalog();
    }

    public function test_it_retrieves_the_labels_of_an_attribute_option()
    {
        $this->createSimpleSelectAttribute('color');
        $this->createSimpleSelectAttribute('secondary_color');

        $expectedOptionLabels = ['en_US' => 'Red', 'fr_FR' => 'Rouge'];
        $this->createAttributeOption('color', 'blue', ['en_US' => 'Blue', 'fr_FR' => 'Bleu']);
        $this->createAttributeOption('color', 'red', $expectedOptionLabels);
        $this->createAttributeOption('secondary_color', 'red', ['en_US' => 'Almost red', 'fr_FR' => 'presque rouge']);

        $attributeOptionLabels = $this->get(GetAttributeOptionLabelsQuery::class)
            ->byCode(new AttributeOptionCode(new AttributeCode('color'), 'red'));

        $this->assertEqualsCanonicalizing($expectedOptionLabels, $attributeOptionLabels);
    }

    public function test_it_returns_an_empty_array_if_the_attribute_option_has_no_labels()
    {
        $this->createSimpleSelectAttribute('color');
        $this->createAttributeOption('color', 'red', []);

        $attributeOptionLabels = $this->get(GetAttributeOptionLabelsQuery::class)
            ->byCode(new AttributeOptionCode(new AttributeCode('color'), 'red'));

        $this->assertSame([], $attributeOptionLabels);
    }

    private function createSimpleSelectAttribute(string $attributeCode): void
    {
        $attribute = $this->get('akeneo_integration_tests.base.attribute.builder')->build([
            'code' => $attributeCode,
            'type' => AttributeTypes::OPTION_SIMPLE_SELECT,
            'group' => 'other',
        ], true);

        $this->get('pim_catalog.saver.attribute')->save($attribute);
    }

    private function createAttributeOption(string $attributeCode, string $optionCode, array $labels): void
    {
        $attributeOption = $this->get('pim_catalog.factory.attribute_option')->create();
        $this->get('pim_catalog.updater.attribute_option')->update($attributeOption, [
            'code' => $optionCode,
            'attribute' => $attributeCode,
            'labels' => $labels,
        ]);

        $this->get('pim_catalog.saver.attribute_option')->save($attributeOption);
    }
}
