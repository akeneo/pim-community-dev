<?php

declare(strict_types=1);

namespace Akeneo\Category\back\tests\Integration\Infrastructure\Storage\Sql;

use Akeneo\Category\back\tests\Integration\Helper\CategoryTestCase;
use Akeneo\Category\Domain\Model\Enrichment\Category;
use Akeneo\Category\Domain\Query\GetCategoryInterface;
use Akeneo\Category\Domain\ValueObject\ValueCollection;
use Akeneo\Category\Infrastructure\Component\Model\CategoryInterface as CategoryDoctrine;
use Akeneo\Test\Integration\Configuration;

/**
 * @copyright 2022 Akeneo SAS (https://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class GetCategorySqlIntegration extends CategoryTestCase
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

        $this->updateCategoryWithValues($this->category->getCode());
    }

    public function testDoNotGetCategoryByCode(): void
    {
        $category = $this->get(GetCategoryInterface::class)->byCode('wrong_code');
        $this->assertNull($category);
    }

    public function testGetCategoryByCode(): void
    {
        $category = $this->get(GetCategoryInterface::class)->byCode($this->category->getCode());
        $this->assertInstanceOf(Category::class, $category);
        $this->assertSame('Chaussettes', $category->getLabels()->getTranslation('fr_FR'));
        $this->assertSame('Socks', $category->getLabels()->getTranslation('en_US'));
        $this->assertSame(
            [
                "data" => "Les chaussures dont vous avez besoin !",
                "locale" => "fr_FR",
                "attribute_code" => "title" . ValueCollection::SEPARATOR . "87939c45-1d85-4134-9579-d594fff65030"
            ],
            $category->getAttributes()->getValue(
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
                "attribute_code" => "photo" . ValueCollection::SEPARATOR . "8587cda6-58c8-47fa-9278-033e1d8c735c"
            ],
            $category->getAttributes()->getValue(
                attributeCode: 'photo',
                attributeUuid: '8587cda6-58c8-47fa-9278-033e1d8c735c',
                localeCode: null
            )
        );
    }

    public function testDoNotGetCategoryById(): void
    {
        $nonExistingId = $this->getLastCategoryId() + 1;
        $category = $this->get(GetCategoryInterface::class)->byId($nonExistingId);
        $this->assertNull($category);
    }

    public function testGetCategoryById(): void
    {
        $category = $this->get(GetCategoryInterface::class)->byId($this->category->getId());
        $this->assertInstanceOf(Category::class, $category);
        $this->assertSame('socks', (string)$category->getCode());
        $this->assertSame('Chaussettes', $category->getLabels()->getTranslation('fr_FR'));
        $this->assertSame('Socks', $category->getLabels()->getTranslation('en_US'));
        $this->assertSame(
            [
                "data" => "Les chaussures dont vous avez besoin !",
                "locale" => "fr_FR",
                "attribute_code" => "title" . ValueCollection::SEPARATOR . "87939c45-1d85-4134-9579-d594fff65030",
            ],
            $category->getAttributes()->getValue(
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
                "attribute_code" => "photo" . ValueCollection::SEPARATOR . "8587cda6-58c8-47fa-9278-033e1d8c735c",
            ],
            $category->getAttributes()->getValue(
                attributeCode: 'photo',
                attributeUuid: '8587cda6-58c8-47fa-9278-033e1d8c735c',
                localeCode: null
            )
        );
    }

    public function testGetCategoryWithNoRelatedTranslations(): void
    {
        $ties = $this->createCategory([
            'code' => 'ties'
        ]);

        $hats = $this->createCategory([
            'code' => 'hats',
            'labels' => []
        ]);

        $tiesCategory = $this->get(GetCategoryInterface::class)->byCode($ties->getCode());
        $this->assertInstanceOf(Category::class, $tiesCategory);
        $this->assertNull($tiesCategory->getLabels());

        $hatsCategory = $this->get(GetCategoryInterface::class)->byCode($hats->getCode());
        $this->assertInstanceOf(Category::class, $hatsCategory);
        $this->assertNull($hatsCategory->getLabels());
    }

    private function getLastCategoryId(): int
    {
        $query = <<<SQL
SELECT id from pim_catalog_category ORDER BY id DESC
LIMIT 1
SQL;

        return (int) $this->get('database_connection')->fetchOne('SELECT MAX(id) FROM pim_catalog_category');
    }

    protected function getConfiguration(): Configuration
    {
        return $this->catalog->useMinimalCatalog();
    }
}
