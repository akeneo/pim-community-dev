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

use Akeneo\Pim\Automation\DataQualityInsights\Domain\Model\Structure\AttributeOptionSpellcheck;
use Akeneo\Pim\Automation\DataQualityInsights\Domain\Model\Structure\AttributeSpellcheck;
use Akeneo\Pim\Automation\DataQualityInsights\Domain\Model\Structure\SpellcheckResultByLocaleCollection;
use Akeneo\Pim\Automation\DataQualityInsights\Domain\ValueObject\AttributeCode;
use Akeneo\Pim\Automation\DataQualityInsights\Domain\ValueObject\AttributeOptionCode;
use Akeneo\Pim\Automation\DataQualityInsights\Domain\ValueObject\LocaleCode;
use Akeneo\Pim\Automation\DataQualityInsights\Domain\ValueObject\Structure\SpellCheckResult;
use Akeneo\Pim\Automation\DataQualityInsights\Infrastructure\Persistence\Query\Structure\GetAllAttributeOptionsSpellcheckQuery;
use Akeneo\Pim\Automation\DataQualityInsights\Infrastructure\Persistence\Repository\AttributeOptionSpellcheckRepository;
use Akeneo\Pim\Automation\DataQualityInsights\Infrastructure\Persistence\Repository\AttributeSpellcheckRepository;
use Akeneo\Test\Integration\TestCase;

class GetAllAttributeOptionsSpellcheckQueryIntegration extends TestCase
{
    public function test_it_returns_the_codes_of_the_attribute_options_with_spelling_issues()
    {
        $attributeCode = new AttributeCode('my_attribute');
        $attributeOptions = [
            new AttributeOptionCode($attributeCode, 'option1'),
            new AttributeOptionCode($attributeCode, 'option2'),
            new AttributeOptionCode($attributeCode, 'option3'),
        ];

        $attributeOptionsSpellcheck = [
            $this->createAttributeOptionSpellcheck($attributeOptions[0], [
                'en_US' => false,
                'fr_FR' => false,
                'de_DE' => true,
            ]),
            $this->createAttributeOptionSpellcheck($attributeOptions[1], [
                'en_US' => false,
                'fr_FR' => false,
                'de_DE' => false,
            ]),
            $this->createAttributeOptionSpellcheck($attributeOptions[2], [
                'en_US' => true,
                'fr_FR' => true,
                'de_DE' => true,
            ])
        ];

        $this->givenAnAttributeThatHasAlreadyBeenEvaluated($attributeOptionsSpellcheck);

        $actualAttributeOptionsSpellcheck = $this->get(GetAllAttributeOptionsSpellcheckQuery::class)->byAttributeCode($attributeCode);

        $this->assertCount(3, $actualAttributeOptionsSpellcheck);

        $this->assertInstanceOf(AttributeOptionSpellcheck::class, $actualAttributeOptionsSpellcheck[0]);
        $this->assertTrue($actualAttributeOptionsSpellcheck[0]->getResult()->isToImprove());
        $this->assertEquals(1, $actualAttributeOptionsSpellcheck[0]->getResult()->getLabelsToImproveNumber());

        $this->assertInstanceOf(AttributeOptionSpellcheck::class, $actualAttributeOptionsSpellcheck[1]);
        $this->assertFalse($actualAttributeOptionsSpellcheck[1]->getResult()->isToImprove());
        $this->assertEquals(0, $actualAttributeOptionsSpellcheck[1]->getResult()->getLabelsToImproveNumber());

        $this->assertInstanceOf(AttributeOptionSpellcheck::class, $actualAttributeOptionsSpellcheck[2]);
        $this->assertTrue($actualAttributeOptionsSpellcheck[2]->getResult()->isToImprove());
        $this->assertEquals(3, $actualAttributeOptionsSpellcheck[2]->getResult()->getLabelsToImproveNumber());
    }

    public function test_it_returns_the_codes_with_paginate_and_search_after_capability()
    {
        $attributeCode = new AttributeCode('my_attribute');
        $attributeOptions = [
            new AttributeOptionCode($attributeCode, 'option1'),
            new AttributeOptionCode($attributeCode, 'option2'),
            new AttributeOptionCode($attributeCode, 'option3'),
        ];

        $attributeOptionsSpellcheck = [
            $this->createAttributeOptionSpellcheck($attributeOptions[0], [
                'en_US' => false,
                'fr_FR' => false,
                'de_DE' => true,
            ]),
            $this->createAttributeOptionSpellcheck($attributeOptions[1], [
                'en_US' => false,
                'fr_FR' => false,
                'de_DE' => false,
            ]),
            $this->createAttributeOptionSpellcheck($attributeOptions[2], [
                'en_US' => true,
                'fr_FR' => true,
                'de_DE' => true,
            ])
        ];

        $this->givenAnAttributeThatHasAlreadyBeenEvaluated($attributeOptionsSpellcheck);

        $actualAttributeOptionsSpellcheck = $this->get(GetAllAttributeOptionsSpellcheckQuery::class)->byAttributeCode(
            $attributeCode,
            2
        );

        $this->assertCount(2, $actualAttributeOptionsSpellcheck);

        $this->assertInstanceOf(AttributeOptionSpellcheck::class, $actualAttributeOptionsSpellcheck[0]);
        $this->assertTrue($actualAttributeOptionsSpellcheck[0]->getResult()->isToImprove());
        $this->assertEquals(1, $actualAttributeOptionsSpellcheck[0]->getResult()->getLabelsToImproveNumber());

        $this->assertInstanceOf(AttributeOptionSpellcheck::class, $actualAttributeOptionsSpellcheck[1]);
        $this->assertFalse($actualAttributeOptionsSpellcheck[1]->getResult()->isToImprove());
        $this->assertEquals(0, $actualAttributeOptionsSpellcheck[1]->getResult()->getLabelsToImproveNumber());

        $actualAttributeOptionsSpellcheck = $this->get(GetAllAttributeOptionsSpellcheckQuery::class)->byAttributeCode(
            $attributeCode,
            2,
            'option2'
        );

        $this->assertCount(1, $actualAttributeOptionsSpellcheck);

        $this->assertInstanceOf(AttributeOptionSpellcheck::class, $actualAttributeOptionsSpellcheck[0]);
        $this->assertTrue($actualAttributeOptionsSpellcheck[0]->getResult()->isToImprove());
        $this->assertEquals(3, $actualAttributeOptionsSpellcheck[0]->getResult()->getLabelsToImproveNumber());
    }

    protected function getConfiguration()
    {
        return $this->catalog->useMinimalCatalog();
    }

    private function createAttributeOptionSpellcheck(AttributeOptionCode $attributeOptionCode, array $resultConfig): AttributeOptionSpellcheck
    {
        $attributeOptionSpellcheckResult = new SpellcheckResultByLocaleCollection();

        foreach ($resultConfig as $locale => $hasMistake) {
            $attributeOptionSpellcheckResult->add(new LocaleCode((string) $locale), new SpellCheckResult((bool) $hasMistake));
        }

        return new AttributeOptionSpellcheck(
            $attributeOptionCode,
            new \DateTimeImmutable('2020-06-10 10:00:00'),
            $attributeOptionSpellcheckResult
        );
    }

    private function givenAnAttributeThatHasAlreadyBeenEvaluated(array $attributeOptionSpellcheckList): void
    {
        foreach ($attributeOptionSpellcheckList as $attributeOptionSpellcheck) {
            $this->get(AttributeOptionSpellcheckRepository::class)->save($attributeOptionSpellcheck);
        }
    }
}
