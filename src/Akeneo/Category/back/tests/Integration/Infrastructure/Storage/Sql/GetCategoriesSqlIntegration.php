<?php

declare(strict_types=1);

namespace Akeneo\Category\back\tests\Integration\Infrastructure\Storage\Sql;

use Akeneo\Category\Application\Query\GetCategoriesInterface;
use Akeneo\Category\back\tests\Integration\Helper\CategoryTestCase;
use Akeneo\Category\Domain\ValueObject\Attribute\Value\AbstractValue;
use Akeneo\Category\Domain\ValueObject\Attribute\Value\TextValue;
use Akeneo\Test\Integration\Configuration;

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
        $category = $this->get(GetCategoriesInterface::class)->byCodes(['wrong_code'], true);
        $this->assertIsArray($category);
        $this->assertEmpty($category);
    }

    public function testGetCategoryByCodes(): void
    {
        $retrievedCategories = $this->get(GetCategoriesInterface::class)->byCodes(['socks', 'shoes'], true);
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
            channel: 'ecommerce',
            locale: 'fr_FR'
        );
        $this->assertSame("Les chaussures dont vous avez besoin !", $expectedTextValue->getValue());
        $this->assertSame('ecommerce', $expectedTextValue->getChannel()?->getValue());
        $this->assertSame('fr_FR', $expectedTextValue->getLocale()?->getValue());

        $this->assertNull($socksCategory->getAttributes());
    }

    public function testGetCategoryByCodesWithoutEnrichedCategories(): void
    {
        $retrievedCategories = $this->get(GetCategoriesInterface::class)->byCodes(['shoes'], false);
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

    protected function getConfiguration(): Configuration
    {
        return $this->catalog->useMinimalCatalog();
    }
}
