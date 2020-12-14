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

namespace Akeneo\Test\Pim\Automation\DataQualityInsights\Integration\Persistence\Query\KeyIndicator;

use Akeneo\Pim\Automation\DataQualityInsights\Domain\Model\KeyIndicator\AttributesWithPerfectSpelling;
use Akeneo\Pim\Automation\DataQualityInsights\Domain\Model\Read\KeyIndicator;
use Akeneo\Pim\Automation\DataQualityInsights\Domain\ValueObject\AttributeCode;
use Akeneo\Pim\Automation\DataQualityInsights\Domain\ValueObject\CategoryCode;
use Akeneo\Pim\Automation\DataQualityInsights\Domain\ValueObject\FamilyCode;
use Akeneo\Pim\Automation\DataQualityInsights\Domain\ValueObject\KeyIndicatorCode;
use Akeneo\Pim\Automation\DataQualityInsights\Domain\ValueObject\LocaleCode;
use Akeneo\Pim\Automation\DataQualityInsights\Domain\ValueObject\Structure\Quality;
use Akeneo\Pim\Automation\DataQualityInsights\Infrastructure\Persistence\Query\KeyIndicator\ComputeAttributesWithPerfectSpellingQuery;
use Akeneo\Pim\Automation\DataQualityInsights\Infrastructure\Persistence\Repository\AttributeLocaleQualityRepository;
use Akeneo\Test\Pim\Automation\DataQualityInsights\Integration\DataQualityInsightsTestCase;

final class ComputeAttributesWithPerfectSpellingQueryIntegration extends DataQualityInsightsTestCase
{
    public function test_it_computes_attributes_with_perfect_spelling_for_a_given_locale()
    {
        $this->clearAttributeLocaleQualities();

        $this->givenAnAttributeLocaleQuality('name', 'en_US', Quality::good());
        $this->givenAnAttributeLocaleQuality('name', 'fr_FR', Quality::toImprove());
        $this->givenAnAttributeLocaleQuality('title', 'en_US', Quality::toImprove());
        $this->givenAnAttributeLocaleQuality('title', 'fr_FR', Quality::good());
        $this->givenAnAttributeLocaleQuality('color', 'en_US', Quality::toImprove());
        $this->givenAnAttributeLocaleQuality('image', 'en_US', Quality::notApplicable());

        $expectedKeyIndicator = new KeyIndicator(new KeyIndicatorCode(AttributesWithPerfectSpelling::CODE), 1, 2);
        $keyIndicator = $this->get(ComputeAttributesWithPerfectSpellingQuery::class)->computeByLocale(new LocaleCode('en_US'));

        $this->assertEquals($expectedKeyIndicator, $keyIndicator);
    }

    public function test_it_computes_attributes_with_perfect_spelling_for_a_given_locale_and_family()
    {
        $this->createAttribute('name');
        $this->createAttribute('title');
        $this->createAttribute('color');
        $this->createAttribute('att_other_family');
        $this->createAttribute('att_without_family');

        $this->createFamily('family_A', ['attributes' => ['name', 'title', 'color']]);
        $this->createFamily('family_B', ['attributes' => ['name', 'att_other_family']]);

        $this->clearAttributeLocaleQualities();

        $this->givenAnAttributeLocaleQuality('name', 'en_US', Quality::good());
        $this->givenAnAttributeLocaleQuality('name', 'fr_FR', Quality::toImprove());
        $this->givenAnAttributeLocaleQuality('title', 'en_US', Quality::toImprove());
        $this->givenAnAttributeLocaleQuality('title', 'fr_FR', Quality::good());
        $this->givenAnAttributeLocaleQuality('color', 'en_US', Quality::toImprove());
        $this->givenAnAttributeLocaleQuality('att_other_family', 'en_US', Quality::toImprove());
        $this->givenAnAttributeLocaleQuality('att_without_family', 'en_US', Quality::toImprove());

        $expectedKeyIndicator = new KeyIndicator(
            new KeyIndicatorCode(AttributesWithPerfectSpelling::CODE),
            1,
            2,
            ['impactedFamilies' => ['family_A']],
        );
        $keyIndicator = $this->get(ComputeAttributesWithPerfectSpellingQuery::class)
            ->computeByLocaleAndFamily(new LocaleCode('en_US'), new FamilyCode('family_A'));

        $this->assertEquals($expectedKeyIndicator, $keyIndicator);
    }

    public function test_it_computes_attributes_with_perfect_spelling_for_a_given_locale_and_category()
    {
        $this->createAttribute('name');
        $this->createAttribute('title');
        $this->createAttribute('color');
        $this->createAttribute('att_from_children');
        $this->createAttribute('att_from_grand_children');
        $this->createAttribute('att_other_category');

        $this->createFamily('family_A', ['attributes' => ['name', 'title', 'color']]);
        $this->createFamily('family_B', ['attributes' => ['name', 'att_from_children']]);
        $this->createFamily('family_C', ['attributes' => ['title', 'att_from_grand_children']]);
        $this->createFamily('other_family', ['attributes' => ['att_other_category']]);

        $this->createCategory(['code' => 'cat1']);
        $this->createCategory(['code' => 'cat2']);
        $this->createCategory(['code' => 'cat1_child', 'parent' => 'cat1']);
        $this->createCategory(['code' => 'cat1_grand_son', 'parent' => 'cat1_child']);
        $this->createCategory(['code' => 'other_cat']);

        $this->createProduct('p1', ['family' => 'family_A', 'categories' => ['cat1', 'cat2']]);
        $this->createProduct('p2', ['family' => 'family_B', 'categories' => ['cat1_child']]);
        $this->createProduct('p3', ['family' => 'family_C', 'categories' => ['cat1_grand_son']]);
        $this->createProduct('p4', ['categories' => ['cat1']]);
        $this->createProduct('p5', ['family' => 'other_family', 'categories' => ['other_cat']]);
        $this->createProduct('p6', ['family' => 'other_family']);

        $this->get('akeneo_elasticsearch.client.product_and_product_model')->refreshIndex();
        $this->clearAttributeLocaleQualities();

        $this->givenAnAttributeLocaleQuality('name', 'en_US', Quality::good());
        $this->givenAnAttributeLocaleQuality('name', 'fr_FR', Quality::toImprove());
        $this->givenAnAttributeLocaleQuality('title', 'en_US', Quality::toImprove());
        $this->givenAnAttributeLocaleQuality('title', 'fr_FR', Quality::good());
        $this->givenAnAttributeLocaleQuality('color', 'fr_FR', Quality::notApplicable());
        $this->givenAnAttributeLocaleQuality('att_from_children', 'en_US', Quality::toImprove());
        $this->givenAnAttributeLocaleQuality('att_from_grand_children', 'en_US', Quality::toImprove());
        $this->givenAnAttributeLocaleQuality('att_other_category', 'en_US', Quality::toImprove());

        $expectedKeyIndicator = new KeyIndicator(
            new KeyIndicatorCode(AttributesWithPerfectSpelling::CODE),
            1,
            3,
            ['impactedFamilies' => ['family_A', 'family_B', 'family_C']],
        );
        $keyIndicator = $this->get(ComputeAttributesWithPerfectSpellingQuery::class)
            ->computeByLocaleAndCategory(new LocaleCode('en_US'), new CategoryCode('cat1'));

        $this->assertEquals($expectedKeyIndicator, $keyIndicator);
    }

    private function givenAnAttributeLocaleQuality(string $attribute, string $locale, Quality $quality): void
    {
        $this->get(AttributeLocaleQualityRepository::class)->save(
            new AttributeCode($attribute),
            new LocaleCode($locale),
            $quality
        );
    }

    private function clearAttributeLocaleQualities(): void
    {
        $this->get('database_connection')->executeQuery('TRUNCATE TABLE pimee_dqi_attribute_locale_quality;');
    }
}
