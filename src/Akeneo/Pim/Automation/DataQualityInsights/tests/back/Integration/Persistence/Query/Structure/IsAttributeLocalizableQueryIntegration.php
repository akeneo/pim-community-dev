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
use Akeneo\Pim\Automation\DataQualityInsights\Infrastructure\Persistence\Query\Structure\IsAttributeLocalizableQuery;
use Akeneo\Pim\Structure\Component\AttributeTypes;
use Akeneo\Test\Integration\TestCase;

final class IsAttributeLocalizableQueryIntegration extends TestCase
{
    protected function getConfiguration()
    {
        return $this->catalog->useMinimalCatalog();
    }

    public function test_it_determines_if_an_attribute_is_localizable()
    {
        $this->givenALocalizableAttribute('color');
        $this->givenANotLocalizableAttribute('weight');

        $query = $this->get(IsAttributeLocalizableQuery::class);

        $this->assertTrue($query->byCode(new AttributeCode('color')));
        $this->assertFalse($query->byCode(new AttributeCode('weight')));
        $this->assertFalse($query->byCode(new AttributeCode('foo')));
    }

    private function givenALocalizableAttribute(string $attributeCode): void
    {
        $this->createAttribute($attributeCode, true);
    }

    private function givenANotLocalizableAttribute(string $attributeCode): void
    {
        $this->createAttribute($attributeCode, false);
    }

    private function createAttribute(string $attributeCode, bool $isLocalizable): void
    {
        $attribute = $this->get('pim_catalog.factory.attribute')->create();
        $this->get('pim_catalog.updater.attribute')->update(
            $attribute,
            [
                'code' => $attributeCode,
                'type' => AttributeTypes::TEXT,
                'localizable' => $isLocalizable
            ]
        );
        $this->get('pim_catalog.saver.attribute')->save($attribute);
    }
}
