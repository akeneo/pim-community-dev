<?php

declare(strict_types=1);

namespace Akeneo\Category\back\tests\Integration\Infrastructure\Storage\Sql;

use Akeneo\Category\Domain\Model\Category;
use Akeneo\Category\Domain\Query\GetCategoryInterface;
use Akeneo\Test\Integration\Configuration;
use Akeneo\Test\Integration\TestCase;
use Doctrine\DBAL\Connection;

/**
 * @copyright 2022 Akeneo SAS (https://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class GetCategoryIntegration extends TestCase
{
    private Connection $db;

    protected function setUp(): void
    {
        parent::setUp();

        $this->db = $this->get('database_connection');
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

        $valueCollection = json_encode([
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
            ]
        ]);

        $query = <<<SQL
UPDATE pim_catalog_category SET value_collection = :value_collection WHERE code = :code;
SQL;

        $this->db->executeQuery($query, [
            'value_collection' => $valueCollection,
            'code' => $category->getCode()
        ]);

        $category = $this->get(GetCategoryInterface::class)->byCode($category->getCode());
        $this->assertInstanceOf(Category::class, $category);
        $this->assertSame('Chaussettes', $category->getLabelCollection()->getLabel('fr_FR'));
        $this->assertSame('Socks', $category->getLabelCollection()->getLabel('en_US'));
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

        $category = $this->get(GetCategoryInterface::class)->byId($category->getId());
        $this->assertInstanceOf(Category::class, $category);
        $this->assertSame('pants', (string)$category->getCode());
        $this->assertSame('Pantalons', $category->getLabelCollection()->getLabel('fr_FR'));
        $this->assertSame('Pants', $category->getLabelCollection()->getLabel('en_US'));
    }

    protected function getConfiguration(): Configuration
    {
        return $this->catalog->useMinimalCatalog();
    }
}
