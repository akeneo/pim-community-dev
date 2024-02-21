<?php

declare(strict_types=1);

namespace Akeneo\Category\back\tests\Integration\ServiceApi;

use Akeneo\Category\Domain\ValueObject\Attribute\Value\AbstractValue;
use Akeneo\Category\Infrastructure\Component\Model\CategoryInterface as CategoryDoctrine;
use Akeneo\Category\ServiceApi\Category;
use Akeneo\Category\ServiceApi\CategoryQueryInterface;
use Akeneo\Test\Integration\Configuration;
use Akeneo\Test\Integration\TestCase;
use PHPUnit\Framework\Assert;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

class CategoryQueryIntegration extends TestCase
{
    private CategoryDoctrine $category;
    private CategoryDoctrine $category2;
    private CategoryDoctrine $category3;

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
                "title" . AbstractValue::SEPARATOR . "87939c45-1d85-4134-9579-d594fff65030" . AbstractValue::SEPARATOR . "en_US" => [
                    "data" => "All the shoes you need!",
                    "type" => "text",
                    "channel" => null,
                    "locale" => "en_US",
                    "attribute_code" => "title" . AbstractValue::SEPARATOR . "87939c45-1d85-4134-9579-d594fff65030",
                ],
                "title" . AbstractValue::SEPARATOR . "87939c45-1d85-4134-9579-d594fff65030" . AbstractValue::SEPARATOR . "fr_FR" => [
                    "data" => "Les chaussures dont vous avez besoin !",
                    "type" => "text",
                    "channel" => null,
                    "locale" => "fr_FR",
                    "attribute_code" => "title" . AbstractValue::SEPARATOR . "87939c45-1d85-4134-9579-d594fff65030",
                ],
                "photo" . AbstractValue::SEPARATOR . "8587cda6-58c8-47fa-9278-033e1d8c735c" => [
                    "data" => [
                        "size" => 168107,
                        "extension" => "jpg",
                        "file_path" => "8/8/3/d/883d041fc9f22ce42fee07d96c05b0b7ec7e66de_shoes.jpg",
                        "mime_type" => "image/jpeg",
                        "original_filename" => "shoes.jpg"
                    ],
                    "type" => "image",
                    "channel" => null,
                    "locale" => null,
                    "attribute_code" => "photo" . AbstractValue::SEPARATOR . "8587cda6-58c8-47fa-9278-033e1d8c735c",
                ]
            ], JSON_THROW_ON_ERROR),
            'code' => $this->category->getCode()
        ]);

        $this->category2 = $this->createCategory([
            'code' => 'led_tv',
            'labels' => [
                'fr_FR' => 'Tv Led',
                'en_US' => 'Led Tv'
            ]
        ]);

        $this->category3 = $this->createCategory([
            'code' => 'tshirt',
            'labels' => [
                'fr_FR' => 'T-shirt',
                'en_US' => 'T-shirt'
            ]
        ]);
    }

    public function testItGetCategoryById(): void
    {
        $category = $this->getHandler()->byId($this->category->getId());

        Assert::assertInstanceOf(Category::class, $category);
    }

    public function testItDoesNotGetCategoryById(): void
    {
        $this->expectException(NotFoundHttpException::class);

        $maxId = $this->get('database_connection')->fetchOne('SELECT MAX(id) FROM pim_catalog_category');
        $this->getHandler()->byId($maxId + 1);
    }

    public function testItGetCategoryByCode(): void
    {
        $category = $this->getHandler()->byCode('socks');

        Assert::assertInstanceOf(Category::class, $category);
    }

    public function testItDoNotGetCategoryByCode(): void
    {
        $this->expectException(NotFoundHttpException::class);

        $this->getHandler()->byCode('wrong_code');
    }

    public function testItGetCategoriesByCodes(): void
    {
        $searchCategoriesByCode = ['socks','tshirt'];
        $categories = $this->getHandler()->byCodes($searchCategoriesByCode);
        $categoriesCode = [];

        foreach ($categories as $category) {
            Assert::assertInstanceOf(Category::class, $category);
            $categoriesCode[] = $category->getCode();
        }
        Assert::assertCount(2, $categoriesCode);
        Assert::assertSame($searchCategoriesByCode, $categoriesCode);
    }

    public function testItGetCategoriesByIds(): void
    {
        $searchCategoriesByIds = [
            $this->category2->getId(),
            $this->category3->getId(),
        ];
        $categories = $this->getHandler()->byIds($searchCategoriesByIds);
        $categoriesIds = [];

        foreach ($categories as $category) {
            Assert::assertInstanceOf(Category::class, $category);
            $categoriesIds[] = $category->getId();
        }
        Assert::assertCount(2, $categoriesIds);
        Assert::assertSame($searchCategoriesByIds, $categoriesIds);
    }

    public function testItDoNotGetCategoriesByCodes(): void
    {
        $searchCategoriesByCode = ['bike','paddle'];
        $categories = $this->getHandler()->byCodes($searchCategoriesByCode);

        Assert::assertCount(0, iterator_to_array($categories, false));
    }

    public function testItDoNotGetCategoriesByIds(): void
    {
        $searchCategoriesByIds = [9999999,10000000];
        $categories = $this->getHandler()->byIds($searchCategoriesByIds);

        Assert::assertCount(0, iterator_to_array($categories, false));
    }

    protected function getConfiguration(): Configuration
    {
        return $this->catalog->useMinimalCatalog();
    }

    private function getHandler(): CategoryQueryInterface
    {
        return $this->get(CategoryQueryInterface::class);
    }
}
