<?php

declare(strict_types=1);

namespace Akeneo\Category\back\tests\Integration\Infrastructure\Storage\Sql;

use Akeneo\Category\Application\Query\GetCategoriesInterface;
use Akeneo\Category\Domain\ValueObject\ValueCollection;
use Akeneo\Test\Integration\Configuration;
use Akeneo\Test\Integration\TestCase;

/**
 * @copyright 2022 Akeneo SAS (https://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class GetCategoriesSqlIntegration extends TestCase
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
        $category = $this->get(GetCategoriesInterface::class)->byCodes(['wrong_code']);
        $this->assertIsArray($category);
        $this->assertEmpty($category);
    }

    public function testGetCategoryByCodes(): void
    {
        $retrievedCategories = $this->get(GetCategoriesInterface::class)->byCodes(['socks', 'shoes']);
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

        // we check we fetched attributes of categories when they exist
        $this->assertSame(
            [
                "data" => "Les chaussures dont vous avez besoin !",
                "locale" => "fr_FR",
                "attribute_code" => "title" . ValueCollection::SEPARATOR . "87939c45-1d85-4134-9579-d594fff65030"
            ],
            $shoesCategory->getAttributes()->getValue(
                'title',
                '87939c45-1d85-4134-9579-d594fff65030',
                'fr_FR'
            )
        );

        $this->assertNull($socksCategory->getAttributes());
    }

    private function updateCategoryWithValues(string $code): void
    {
        $query = <<<SQL
UPDATE pim_catalog_category SET value_collection = :value_collection WHERE code = :code;
SQL;

        $this->get('database_connection')->executeQuery($query, [
            'value_collection' => json_encode([
                "attribute_codes" => [
                    "title" . ValueCollection::SEPARATOR . "87939c45-1d85-4134-9579-d594fff65030",
                    "photo" . ValueCollection::SEPARATOR . "8587cda6-58c8-47fa-9278-033e1d8c735c",
                ],
                "title" . ValueCollection::SEPARATOR . "87939c45-1d85-4134-9579-d594fff65030" . ValueCollection::SEPARATOR . "en_US" => [
                    "data" => "All the shoes you need!",
                    "locale" => "en_US",
                    "attribute_code" => "title" . ValueCollection::SEPARATOR . "87939c45-1d85-4134-9579-d594fff65030",
                ],
                "title" . ValueCollection::SEPARATOR . "87939c45-1d85-4134-9579-d594fff65030" . ValueCollection::SEPARATOR . "fr_FR" => [
                    "data" => "Les chaussures dont vous avez besoin !",
                    "locale" => "fr_FR",
                    "attribute_code" => "title" . ValueCollection::SEPARATOR . "87939c45-1d85-4134-9579-d594fff65030"
                ],
                "photo" . ValueCollection::SEPARATOR . "8587cda6-58c8-47fa-9278-033e1d8c735c" => [
                    "data" => [
                        "size" => 168107,
                        "extension" => "jpg",
                        "file_path" => "8/8/3/d/883d041fc9f22ce42fee07d96c05b0b7ec7e66de_shoes.jpg",
                        "mime_type" => "image/jpeg",
                        "original_filename" => "shoes.jpg"
                    ],
                    "locale" => null,
                    "attribute_code" => "photo" . ValueCollection::SEPARATOR . "8587cda6-58c8-47fa-9278-033e1d8c735c"
                ]
            ], JSON_THROW_ON_ERROR),
            'code' => $code
        ]);
    }

    protected function getConfiguration(): Configuration
    {
        return $this->catalog->useMinimalCatalog();
    }
}
