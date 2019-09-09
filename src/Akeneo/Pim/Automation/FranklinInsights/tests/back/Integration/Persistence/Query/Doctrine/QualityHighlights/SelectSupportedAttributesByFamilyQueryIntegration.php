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

namespace Akeneo\Pim\Automation\FranklinInsights\tests\back\Integration\Persistence\Query\Doctrine\QualityHighlights;

use Akeneo\Pim\Automation\FranklinInsights\Domain\Common\ValueObject\AttributeCode;
use Akeneo\Pim\Automation\FranklinInsights\Domain\Common\ValueObject\AttributeType;
use Akeneo\Pim\Automation\FranklinInsights\Domain\Common\ValueObject\FamilyCode;
use Akeneo\Pim\Automation\FranklinInsights\Domain\QualityHighlights\Model\Read\Attribute;
use Akeneo\Pim\Automation\FranklinInsights\Domain\QualityHighlights\Query\SelectSupportedAttributesByFamilyQueryInterface;
use Akeneo\Pim\Structure\Component\AttributeTypes;
use Akeneo\Pim\Structure\Component\Model\AttributeGroup;
use Akeneo\Test\Integration\TestCase;

class SelectSupportedAttributesByFamilyQueryIntegration extends TestCase
{
    public function test_it_selects_supported_attributes_by_family()
    {
        $this->createAttribute('name', AttributeTypes::TEXT);
        $this->createAttribute('description', AttributeTypes::TEXT);
        $this->createAttribute('weight', AttributeTypes::NUMBER);
        $this->createAttribute('photo', AttributeTypes::IMAGE);

        $this->createFamily('mugs', ['sku', 'name', 'weight', 'photo']);
        $this->createFamily('projectors', ['sku', 'name', 'description']);

        $attributes = $this->getQuery()->execute(new FamilyCode('mugs'));

        $this->assertEquals([
            'name' => new Attribute(new AttributeCode('name'), new AttributeType(AttributeTypes::TEXT)),
            'weight' => new Attribute(new AttributeCode('weight'), new AttributeType(AttributeTypes::NUMBER)),
        ], $attributes);
    }

    protected function getConfiguration()
    {
        return $this->catalog->useMinimalCatalog();
    }

    private function createAttribute(string $code, string $type): void
    {
        $attribute = $this->getFromTestContainer('akeneo_ee_integration_tests.builder.attribute')->build([
            'code' => $code,
            'type' => $type,
            'group' => AttributeGroup::DEFAULT_GROUP_CODE,
        ]);

        $this->getFromTestContainer('validator')->validate($attribute);
        $this->getFromTestContainer('pim_catalog.saver.attribute')->save($attribute);
    }

    private function createFamily(string $familyCode, array $attributeCodes): void
    {
        $family = $this
            ->getFromTestContainer('akeneo_ee_integration_tests.builder.family')
            ->build([
                'code' => $familyCode,
                'attributes' => $attributeCodes,
                'labels' => []
            ]);

        $this->getFromTestContainer('validator')->validate($family);
        $this->getFromTestContainer('pim_catalog.saver.family')->save($family);
    }

    private function getQuery(): SelectSupportedAttributesByFamilyQueryInterface
    {
        return $this->getFromTestContainer('akeneo.pim.automation.franklin_insights.infrastructure.persistence.query.quality_highlights.select_supported_attributes_by_family');
    }
}
