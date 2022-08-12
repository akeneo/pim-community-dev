<?php

declare(strict_types=1);

namespace Akeneo\Category\back\tests\Integration\Infrastructure\Storage\Sql;

use Akeneo\Category\Domain\Model\Category;
use Akeneo\Category\Infrastructure\Component\Model\Category as CategoryDoctrine;
use Akeneo\Category\Infrastructure\Storage\Sql\GetCategorySql;
use Akeneo\Test\Integration\Configuration;
use Akeneo\Test\Integration\TestCase;
use Akeneo\Tool\Component\StorageUtils\Exception\InvalidPropertyTypeException;

/**
 * @copyright 2022 Akeneo SAS (https://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class GetCategorySqlIntegration extends TestCase
{
    private CategoryDoctrine|Category $category;

    protected function setUp(): void
    {
        parent::setUp();

        $this->category = $this->createCategory([
            'code' => 'socks',
            'labels' => [
                'fr_FR' => 'Chaussettes',
                'en_US' => 'Socks'
            ]
        ]);

        $query = <<<SQL
UPDATE pim_catalog_category SET value_collection = :value_collection WHERE code = :code;
SQL;

        $this->get('database_connection')->executeQuery($query, [
            'value_collection' => json_encode([
                "attribute_codes" => [
                    "title_87939c45-1d85-4134-9579-d594fff65030",
                    "photo_8587cda6-58c8-47fa-9278-033e1d8c735c",
                ],
                "title_87939c45-1d85-4134-9579-d594fff65030_en_US" => [
                    "data" => "All the shoes you need!",
                    "locale" => "en_US",
                ],
                "title_87939c45-1d85-4134-9579-d594fff65030_fr_FR" => [
                    "data" => "Les chaussures dont vous avez besoin !",
                    "locale" => "fr_FR",
                ],
                "photo_8587cda6-58c8-47fa-9278-033e1d8c735c" => [
                    "data" => [
                        "size" => 168107,
                        "extension" => "jpg",
                        "file_path" => "8/8/3/d/883d041fc9f22ce42fee07d96c05b0b7ec7e66de_shoes.jpg",
                        "mime_type" => "image/jpeg",
                        "original_filename" => "shoes.jpg"
                    ],
                    "locale" => null,
                ]
            ]),
            'code' => $this->category->getCode()
        ]);
    }

    public function testDoNotGetCategoryByCode(): void
    {
        $category = $this->get(GetCategorySql::class)->byCode('wrong_code');
        $this->assertNull($category);
    }

    public function testGetCategoryByCode(): void
    {
        $category = $this->get(GetCategorySql::class)->byCode($this->category->getCode());
        $this->assertInstanceOf(Category::class, $category);
        $this->assertSame('Chaussettes', $category->getLabelCollection()->getLabel('fr_FR'));
        $this->assertSame('Socks', $category->getLabelCollection()->getLabel('en_US'));
        $this->assertSame(
            [
                "data" => "Les chaussures dont vous avez besoin !",
                "locale" => "fr_FR",
            ],
            $category->getValueCollection()->getAttributeTextData(
                'title',
                '87939c45-1d85-4134-9579-d594fff65030',
                'fr_FR'
            )
        );
        $this->assertSame(
            [
                "data" => [
                    "size" => 168107,
                    "extension" => "jpg",
                    "file_path" => "8/8/3/d/883d041fc9f22ce42fee07d96c05b0b7ec7e66de_shoes.jpg",
                    "mime_type" => "image/jpeg",
                    "original_filename" => "shoes.jpg"
                ],
                "locale" => null,
            ],
            $category->getValueCollection()->getAttributeData(
                'photo',
                '8587cda6-58c8-47fa-9278-033e1d8c735c'
            )
        );
    }

    public function testDoNotGetCategoryById(): void
    {
        $category = $this->get(GetCategorySql::class)->byId(999);
        $this->assertNull($category);
    }

    public function testGetCategoryById(): void
    {
        $category = $this->get(GetCategorySql::class)->byId($this->category->getId());
        $this->assertInstanceOf(Category::class, $category);
        $this->assertSame('socks', (string)$category->getCode());
        $this->assertSame('Chaussettes', $category->getLabelCollection()->getLabel('fr_FR'));
        $this->assertSame('Socks', $category->getLabelCollection()->getLabel('en_US'));
        $this->assertSame(
            [
                "data" => "Les chaussures dont vous avez besoin !",
                "locale" => "fr_FR",
            ],
            $category->getValueCollection()->getAttributeTextData(
                'title',
                '87939c45-1d85-4134-9579-d594fff65030',
                'fr_FR'
            )
        );
        $this->assertSame(
            [
                "data" => [
                    "size" => 168107,
                    "extension" => "jpg",
                    "file_path" => "8/8/3/d/883d041fc9f22ce42fee07d96c05b0b7ec7e66de_shoes.jpg",
                    "mime_type" => "image/jpeg",
                    "original_filename" => "shoes.jpg"
                ],
                "locale" => null,
            ],
            $category->getValueCollection()->getAttributeData(
                'photo',
                '8587cda6-58c8-47fa-9278-033e1d8c735c'
            )
        );
    }

    protected function getConfiguration(): Configuration
    {
        return $this->catalog->useMinimalCatalog();
    }
}
