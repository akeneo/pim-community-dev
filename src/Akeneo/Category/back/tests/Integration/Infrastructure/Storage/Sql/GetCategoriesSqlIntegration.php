<?php

declare(strict_types=1);

namespace Akeneo\Category\back\tests\Integration\Infrastructure\Storage\Sql;

use Akeneo\Category\Application\Query\GetCategoriesInterface;
use Akeneo\Category\back\tests\Integration\Helper\CategoryTestCase;
use Akeneo\Category\Domain\ValueObject\Attribute\Value\AbstractValue;
use Akeneo\Category\Domain\ValueObject\Attribute\Value\TextValue;
use Akeneo\Test\Integration\Configuration;
use Doctrine\DBAL\Connection;

/**
 * @copyright 2022 Akeneo SAS (https://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class GetCategoriesSqlIntegration extends CategoryTestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        $this->createCategory([
            'code' => 'socks',
            'labels' => [
                'fr_FR' => 'Chaussettes',
                'en_US' => 'Socks'
            ]
        ]);

        $categoryShoes = $this->createCategory([
            'code' => 'shoes',
            'labels' => [
                'fr_FR' => 'Chaussures',
                'en_US' => 'Shoes'
            ]
        ]);

        $this->createCategory([
            'code' => 'pants',
            'labels' => [
                'fr_FR' => 'Pantalons',
                'en_US' => 'Pants'
            ]
        ]);

        $this->updateCategoryWithValues($categoryShoes->getCode());
    }

    public function testDoNotGetCategoryByCodes(): void
    {
        $parameters['sqlWhere'] = 'category.code IN (:category_codes)';
        $parameters['sqlLimitOffset'] = 'LIMIT 10';
        $parameters['params'] = [
            'category_codes' => ['wrong code'],
            'with_enriched_attributes' => true
        ];
        $parameters['types'] = [
            'category_codes' => Connection::PARAM_STR_ARRAY,
            'with_enriched_attributes' => \PDO::PARAM_BOOL
        ];
        $category = $this->get(GetCategoriesInterface::class)->execute($parameters);
        $this->assertIsArray($category);
        $this->assertEmpty($category);
    }

    public function testGetCategoryByCodes(): void
    {
        $parameters['sqlWhere'] = 'category.code IN (:category_codes)';
        $parameters['sqlLimitOffset'] = 'LIMIT 10';
        $parameters['params'] = [
            'category_codes' => ['socks', 'shoes'],
            'with_enriched_attributes' => true
        ];
        $parameters['types'] = [
            'category_codes' => Connection::PARAM_STR_ARRAY,
            'with_enriched_attributes' => \PDO::PARAM_BOOL
        ];
        $retrievedCategories = $this->get(GetCategoriesInterface::class)->execute($parameters);
        $this->assertIsArray($retrievedCategories);
        // we retrieve 2 out of the 3 existing categories
        $this->assertCount(2, $retrievedCategories);

        $shoesCategory = null;
        $socksCategory = null;

        foreach ($retrievedCategories as $category) {
            if ((string) $category->getCode() === 'shoes') {
                $this->assertEmpty($shoesCategory);
                $shoesCategory = $category;
            }
            if ((string) $category->getCode() === 'socks') {
                $this->assertEmpty($socksCategory);
                $socksCategory = $category;
            }
        }

        $this->assertNotEmpty($shoesCategory);
        $this->assertNotEmpty($socksCategory);

        // we check labels of retrieved categories
        $this->assertEqualsCanonicalizing(
            [
                'fr_FR' => 'Chaussures',
                'en_US' => 'Shoes'
            ],
            $shoesCategory->getLabels()->normalize()
        );
        $this->assertEqualsCanonicalizing(
            [
                'fr_FR' => 'Chaussettes',
                'en_US' => 'Socks'
            ],
            $socksCategory->getLabels()->normalize()
        );

        // we check that existing categories attributes were fetched
        $expectedTextValue = TextValue::fromApplier(
            value: 'Les chaussures dont vous avez besoin !',
            uuid: '87939c45-1d85-4134-9579-d594fff65030',
            code: 'title',
            channel: null,
            locale: 'fr_FR'
        );
        $this->assertSame("Les chaussures dont vous avez besoin !", $expectedTextValue->getValue());
        $this->assertNull($expectedTextValue->getChannel());
        $this->assertSame('fr_FR', $expectedTextValue->getLocale()?->getValue());

        $this->assertNull($socksCategory->getAttributes());
    }

    public function testGetCategoryByCodesWithoutEnrichedCategories(): void
    {
        $parameters['sqlWhere'] = 'category.code IN (:category_codes)';
        $parameters['sqlLimitOffset'] = 'LIMIT 10';
        $parameters['params'] = [
            'category_codes' => ['shoes'],
            'with_enriched_attributes' => false
        ];
        $parameters['types'] = [
            'category_codes' => Connection::PARAM_STR_ARRAY,
            'with_enriched_attributes' => \PDO::PARAM_BOOL
        ];
        $retrievedCategories = $this->get(GetCategoriesInterface::class)->execute($parameters);
        $this->assertIsArray($retrievedCategories);
        // we retrieve 1 out of the 3 existing categories
        $this->assertCount(1, $retrievedCategories);

        $shoesCategory = null;

        foreach ($retrievedCategories as $category) {
                $this->assertEmpty($shoesCategory);
                $shoesCategory = $category;
        }

        $this->assertNotEmpty($shoesCategory);

        // we check labels of retrieved categories
        $this->assertEqualsCanonicalizing(
            [
                'fr_FR' => 'Chaussures',
                'en_US' => 'Shoes'
            ],
            $shoesCategory->getLabels()->normalize()
        );

        $this->assertNull($shoesCategory->getAttributes());
    }

    public function testGetCategoryWithLimitSetToOneAndOffsetToTwo(): void{
        $parameters['sqlWhere'] = '1=1';
        $parameters['sqlLimitOffset'] = 'LIMIT 1 OFFSET 2';
        $parameters['params'] = [
            'with_enriched_attributes' => false
        ];
        $parameters['types'] = [
            'with_enriched_attributes' => \PDO::PARAM_BOOL
        ];
        $retrievedCategory = $this->get(GetCategoriesInterface::class)->execute($parameters);
        $this->assertIsArray($retrievedCategory);

        // we retrieve only 1 out of the 3 existing categories
        $this->assertCount(1, $retrievedCategory);

        // we check that we retrieved the correct category according to the OFFSET
        $this->assertSame('shoes', (string) $retrievedCategory[0]->getCode());
    }

    protected function getConfiguration(): Configuration
    {
        return $this->catalog->useMinimalCatalog();
    }
}
