<?php

declare(strict_types=1);

namespace AkeneoTest\Pim\Structure\Integration\Family;

use Akeneo\Pim\Structure\Component\AttributeTypes;
use Akeneo\Pim\Structure\Component\Query\PublicApi\Family\Family;
use Akeneo\Pim\Structure\Component\Query\PublicApi\Family\GetFamilies;
use Akeneo\Test\Integration\Configuration;
use Akeneo\Test\Integration\TestCase;
use Webmozart\Assert\Assert;

final class SqlGetFamilyIntegration extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();
        $this->givenAttributes(['description', 'price', 'color', 'name']);
        $this->givenFamilies([
            [
                'code' => 'shoes',
                'labels' => [
                    'en_US' => 'Shoes',
                    'fr_FR' => 'Chaussures',
                ],
                'attributes' => [
                    'sku',
                    'description',
                    'price',
                ],
            ],
            [
                'code' => 'accessories',
                'labels' => [
                    'de_DE' => 'Zubehör',
                    'en_US' => 'Accessories',
                    'fr_FR' => 'Accessoires',
                ],
                'attributes' => [
                    'sku',
                    'color',
                    'name',
                ],
            ],
            [
                'code' => 'hats',
                'attributes' => [
                    'sku',
                    'description',
                    'name',
                ],
            ],
        ]);
        $this->givenActiveLocales(['en_US', 'fr_FR', 'de_DE']);
    }

    public function test_it_gets_families_by_codes(): void
    {
        $query = $this->get(GetFamilies::class);

        $expected = [
            'accessories' => new Family(
                'accessories',
                [
                    'de_DE' => 'Zubehör',
                    'en_US' => 'Accessories',
                    'fr_FR' => 'Accessoires',
                ],
                ['sku', 'color', 'name'],
            ),
            'shoes' => new Family(
                'shoes',
                [
                    'en_US' => 'Shoes',
                    'fr_FR' => 'Chaussures',
                ],
                ['sku', 'description', 'price'],
            ),
            'hats' => new Family(
                'hats',
                [],
                ['sku', 'description', 'name'],
            ),
        ];
        $actual = $query->byCodes(['shoes', 'accessories', 'hats']);

        $this->assertEqualsCanonicalizing($expected, $actual);
    }

    public function test_it_gets_an_empty_list_if_all_the_families_do_not_exist(): void
    {
        $query = $this->get(GetFamilies::class);

        $actual = $query->byCodes(['unknown_family1', 'unknown_family2']);

        $this->assertSame([], $actual);
    }

    public function test_it_gets_a_single_family(): void
    {
        $query = $this->get(GetFamilies::class);

        $expected = new Family(
            'accessories',
            [
                'de_DE' => 'Zubehör',
                'en_US' => 'Accessories',
                'fr_FR' => 'Accessoires',
            ],
            ['sku', 'color', 'name'],
        );
        $actual = $query->byCode('accessories');

        $this->assertEqualsCanonicalizing($expected, $actual);
    }

    public function test_it_returns_nothing_if_the_family_does_not_exist(): void
    {
        $query = $this->get(GetFamilies::class);

        $actual = $query->byCode('unknown_family');

        $this->assertNull($actual);
    }

    protected function getConfiguration(): Configuration
    {
        return $this->catalog->useMinimalCatalog();
    }

    private function givenFamilies(array $families): void
    {
        $families = array_map(function (array $familyData) {
            $family = $this->get('pim_catalog.factory.family')->create();
            $this->get('pim_catalog.updater.family')->update($family, $familyData);
            $constraintViolations = $this->get('validator')->validate($family);

            Assert::count($constraintViolations, 0);

            return $family;
        }, $families);

        $this->get('pim_catalog.saver.family')->saveAll($families);
    }

    private function givenAttributes(array $attributeCodes): void
    {
        $attributes = array_map(function ($attributeCode) {
            $attribute = $this->get('pim_catalog.factory.attribute')->create();
            $this->get('pim_catalog.updater.attribute')->update(
                $attribute,
                [
                    'code' => $attributeCode,
                    'type' => 'pim_catalog_text',
                    'group' => 'other'
                ]
            );

            $errors = $this->get('validator')->validate($attribute);
            Assert::count($errors, 0);

            return $attribute;
        }, $attributeCodes);

        $this->get('pim_catalog.saver.attribute')->saveAll($attributes);
    }

    private function givenActiveLocales(array $localeCodes): void
    {
        $ecommerce = $this->get('pim_catalog.repository.channel')->findOneByIdentifier('ecommerce');
        foreach ($localeCodes as $localeCode) {

            $locale = $this->get('pim_catalog.repository.locale')->findOneByIdentifier($localeCode);
            $ecommerce->addLocale($locale);
        }
        $this->get('pim_catalog.saver.channel')->save($ecommerce);
    }
}
