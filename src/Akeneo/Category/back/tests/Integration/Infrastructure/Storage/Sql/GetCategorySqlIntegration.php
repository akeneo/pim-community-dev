<?php

declare(strict_types=1);

namespace Akeneo\Category\back\tests\Integration\Infrastructure\Storage\Sql;

use Akeneo\Category\Domain\Model\Category;
use Akeneo\Category\Infrastructure\Storage\Sql\GetCategorySql;
use Akeneo\Test\Integration\Configuration;
use Akeneo\Test\Integration\TestCase;
use Doctrine\DBAL\Connection;

/**
 * @copyright 2022 Akeneo SAS (https://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class GetCategorySqlIntegration extends TestCase
{
    private Connection $db;
    private string $valueCollection;

    protected function setUp(): void
    {
        parent::setUp();

        $this->db = $this->get('database_connection');
        $this->valueCollection = json_encode([
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
        ]);
    }

    public function testGetCategoryByCode(): void
    {
        $category = $this->createCategory([
            'code' => 'socks',
            'labels' => [
                'fr_FR' => 'Chaussettes',
                'en_US' => 'Socks'
            ]
        ]);

        $query = <<<SQL
UPDATE pim_catalog_category SET value_collection = :value_collection WHERE code = :code;
SQL;

        $this->db->executeQuery($query, [
            'value_collection' => $this->valueCollection,
            'code' => $category->getCode()
        ]);

        $category = $this->get(GetCategorySql::class)->byCode($category->getCode());
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

    public function testGetCategoryById(): void
    {
        $category = $this->createCategory([
            'code' => 'pants',
            'labels' => [
                'fr_FR' => 'Pantalons',
                'en_US' => 'Pants'
            ]
        ]);

        $query = <<<SQL
UPDATE pim_catalog_category SET value_collection = :value_collection WHERE code = :code;
SQL;

        $this->db->executeQuery($query, [
            'value_collection' => $this->valueCollection,
            'code' => $category->getCode()
        ]);

        $category = $this->get(GetCategorySql::class)->byId($category->getId());
        $this->assertInstanceOf(Category::class, $category);
        $this->assertSame('pants', (string)$category->getCode());
        $this->assertSame('Pantalons', $category->getLabelCollection()->getLabel('fr_FR'));
        $this->assertSame('Pants', $category->getLabelCollection()->getLabel('en_US'));
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
