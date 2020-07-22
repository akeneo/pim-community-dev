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
use Akeneo\Pim\Automation\DataQualityInsights\Infrastructure\Persistence\Query\Structure\GetAttributeLabelsQuery;
use Akeneo\Pim\Structure\Component\AttributeTypes;
use Akeneo\Test\Integration\TestCase;

class GetAttributeLabelsQueryIntegration extends TestCase
{
    protected function getConfiguration()
    {
        return $this->catalog->useMinimalCatalog();
    }

    public function test_it_returns_the_labels_of_a_given_attribute()
    {
        $attribute = $this->get('akeneo_integration_tests.base.attribute.builder')->build([
            'code' => 'name',
            'type' => AttributeTypes::TEXT,
            'group' => 'other',
        ], true);

        $expectedLabels = [
            'en_US' => 'Name',
            'fr_FR' => 'Nom'
        ];

        $this->get('pim_catalog.updater.attribute')->update($attribute, [
            'labels' => $expectedLabels
        ]);
        $this->get('pim_catalog.saver.attribute')->save($attribute);

        $labels = $this->get(GetAttributeLabelsQuery::class)->byCode(new AttributeCode('name'));

        $this->assertEqualsCanonicalizing($expectedLabels, $labels);
    }

    public function test_it_returns_an_empty_array_if_the_attribute_has_no_label()
    {
        $attribute = $this->get('akeneo_integration_tests.base.attribute.builder')->build([
            'code' => 'name',
            'type' => AttributeTypes::TEXT,
            'group' => 'other',
        ], true);

        $this->get('pim_catalog.saver.attribute')->save($attribute);

        $labels = $this->get(GetAttributeLabelsQuery::class)->byCode(new AttributeCode('name'));
        $this->assertEmpty($labels);
    }
}
