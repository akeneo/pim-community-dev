<?php

declare (strict_types=1);

/*
 * This file is part of the Akeneo PIM Enterprise Edition.
 *
 * (c) 2019 Akeneo SAS (http://www.akeneo.com)
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Akeneo\Pim\Automation\FranklinInsights\tests\back\Integration\Persistence\Query\Doctrine\QualityHighlights;

use Akeneo\Pim\Structure\Component\AttributeTypes;
use Akeneo\Pim\Structure\Component\Model\AttributeGroup;
use Akeneo\Test\Integration\Configuration;
use Akeneo\Test\Integration\TestCase;

final class SelectAttributeCodesFromIdsQueryIntegration extends TestCase
{
    public function test_it_returns_the_attribute_code()
    {
        $attribute1 = $this->createAttribute('color');
        $attribute2 = $this->createAttribute('weight');

        $results = $this
            ->getFromTestContainer('akeneo.pim.automation.franklin_insights.infrastructure.persistence.query.select_attribute_codes_from_ids')
            ->execute([$attribute1->getId(), $attribute2->getId()]);
        $this->assertSame(['color', 'weight'], $results);
    }

    protected function getConfiguration(): Configuration
    {
        return $this->catalog->useMinimalCatalog();
    }

    private function createAttribute(string $code)
    {
        $attribute = $this->getFromTestContainer('akeneo_ee_integration_tests.builder.attribute')->build(
            [
                'code' => $code,
                'type' => AttributeTypes::TEXT,
                'group' => AttributeGroup::DEFAULT_GROUP_CODE,
            ]
        );
        $this->getFromTestContainer('validator')->validate($attribute);
        $this->getFromTestContainer('pim_catalog.saver.attribute')->save($attribute);

        return $attribute;
    }
}
