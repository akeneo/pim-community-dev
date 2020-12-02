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
use Akeneo\Pim\Automation\DataQualityInsights\Domain\ValueObject\FamilyId;
use Akeneo\Pim\Automation\DataQualityInsights\Infrastructure\Persistence\Query\Structure\GetActivatedFamilyAttributesCodesQuery;
use Akeneo\Test\Pim\Automation\DataQualityInsights\Integration\DataQualityInsightsTestCase;

final class GetActivatedFamilyAttributesCodesQueryIntegration extends DataQualityInsightsTestCase
{
    public function test_it_retrieves_the_activated_attributes_codes_of_a_given_family()
    {
        $this->givenAnActivatedAttributeGroup('marketing');
        $this->givenADeactivatedAttributeGroup('erp');

        $this->createAttribute('name', ['group' => 'marketing']);
        $this->createAttribute('title', ['group' => 'marketing']);
        $this->createAttribute('internal_code', ['group' => 'erp']);

        $family = $this->createFamily('a_family', ['attributes' => ['sku', 'name', 'title', 'internal_code']]);
        $this->createFamily('another_family', ['attributes' => ['sku', 'name', 'internal_code']]);

        $expectedAttributes = [
            new AttributeCode('sku'),
            new AttributeCode('name'),
            new AttributeCode('title'),
        ];

        $familyAttributes = $this->get(GetActivatedFamilyAttributesCodesQuery::class)->byFamilyId(new FamilyId($family->getId()));

        $this->assertEqualsCanonicalizing($expectedAttributes, $familyAttributes);
    }

    private function givenAnActivatedAttributeGroup(string $code): void
    {
        $this->createAttributeGroup($code);
        $this->createAttributeGroupActivation($code, true, new \DateTimeImmutable());
    }

    private function givenADeactivatedAttributeGroup(string $code): void
    {
        $this->createAttributeGroup($code);
        $this->createAttributeGroupActivation($code, false, new \DateTimeImmutable());
    }
}
