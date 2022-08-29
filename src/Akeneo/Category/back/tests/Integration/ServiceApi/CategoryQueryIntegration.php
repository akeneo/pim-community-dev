<?php

declare(strict_types=1);

namespace Akeneo\Category\back\tests\Integration\ServiceApi;

use Akeneo\Category\Domain\ValueObject\ValueCollection;
use Akeneo\Category\ServiceApi\Category;
use Akeneo\Category\ServiceApi\CategoryInterface;
use Akeneo\Test\Integration\Configuration;
use Akeneo\Test\Integration\TestCase;
use PHPUnit\Framework\Assert;

class CategoryQueryIntegration extends TestCase
{
    public function testItGetCategory(): void
    {
        $this->insertCategory();

        $category = $this->getHandler()->byCode('socks');

        Assert::assertInstanceOf(Category::class, $category);
    }

    private function insertCategory(): void
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

        $this->get('database_connection')->executeQuery($query, [
            'value_collection' => json_encode([
                "attribute_codes" => [
                    "title" . ValueCollection::SEPARATOR . "87939c45-1d85-4134-9579-d594fff65030",
                    "photo" . ValueCollection::SEPARATOR . "8587cda6-58c8-47fa-9278-033e1d8c735c",
                ],
                "title" . ValueCollection::SEPARATOR . "87939c45-1d85-4134-9579-d594fff65030" . ValueCollection::SEPARATOR . "en_US" => [
                    "data" => "All the shoes you need!",
                    "locale" => "en_US",
                ],
                "title" . ValueCollection::SEPARATOR . "87939c45-1d85-4134-9579-d594fff65030" . ValueCollection::SEPARATOR . "fr_FR" => [
                    "data" => "Les chaussures dont vous avez besoin !",
                    "locale" => "fr_FR",
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
                ]
            ], JSON_THROW_ON_ERROR),
            'code' => $category->getCode()
        ]);
    }

    protected function getConfiguration(): Configuration
    {
        return $this->catalog->useMinimalCatalog();
    }

    private function getHandler(): CategoryInterface
    {
        return $this->get(CategoryInterface::class);
    }
}
