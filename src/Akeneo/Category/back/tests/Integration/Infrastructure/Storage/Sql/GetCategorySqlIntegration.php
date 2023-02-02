<?php

declare(strict_types=1);

namespace Akeneo\Category\back\tests\Integration\Infrastructure\Storage\Sql;

use Akeneo\Category\Application\Storage\Save\Saver\CategoryTemplateSaver;
use Akeneo\Category\Application\Storage\Save\Saver\CategoryTreeTemplateSaver;
use Akeneo\Category\back\tests\Integration\Helper\CategoryTestCase;
use Akeneo\Category\Domain\Model\Enrichment\Category;
use Akeneo\Category\Domain\Query\GetCategoryInterface;
use Akeneo\Category\Domain\ValueObject\Attribute\Value\AbstractValue;
use Akeneo\Category\Domain\ValueObject\Attribute\Value\ImageValue;
use Akeneo\Category\Domain\ValueObject\Attribute\Value\TextValue;
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
        $this->assertEquals([
            "photo" . AbstractValue::SEPARATOR . "8587cda6-58c8-47fa-9278-033e1d8c735c",
            "title" . AbstractValue::SEPARATOR . "87939c45-1d85-4134-9579-d594fff65030"
        ], $category->getAttributeCodes());
        $this->assertSame('Chaussettes', $category->getLabels()->getTranslation('fr_FR'));
        $this->assertSame('Socks', $category->getLabels()->getTranslation('en_US'));

        /** @var TextValue|null $expectedTextValue */
        $expectedTextValue = $category->getAttributes()->getValue(
            attributeCode: 'title',
            attributeUuid: '87939c45-1d85-4134-9579-d594fff65030',
            channel: 'ecommerce',
            localeCode: 'fr_FR'
        );
        $this->assertSame("Les chaussures dont vous avez besoin !", $expectedTextValue->getValue());
        $this->assertSame('ecommerce', $expectedTextValue->getChannel()?->getValue());
        $this->assertSame('fr_FR', $expectedTextValue->getLocale()?->getValue());

        /** @var ImageValue $expectedImageValue */
        $expectedImageValue = $category->getAttributes()->getValue(
            attributeCode: 'photo',
            attributeUuid: '8587cda6-58c8-47fa-9278-033e1d8c735c',
            channel: null,
            localeCode: null
        );
        $this->assertSame(168107, $expectedImageValue->getValue()->getSize());
        $this->assertSame("jpg", $expectedImageValue->getValue()->getExtension());
        $this->assertSame("8/8/3/d/883d041fc9f22ce42fee07d96c05b0b7ec7e66de_shoes.jpg", $expectedImageValue->getValue()->getFilePath());
        $this->assertSame("image/jpeg", $expectedImageValue->getValue()->getMimeType());
        $this->assertSame("shoes.jpg", $expectedImageValue->getValue()->getOriginalFilename());
        $this->assertNull($expectedImageValue->getChannel());
        $this->assertNull($expectedImageValue->getLocale());
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

        /** @var TextValue $expectedTextValue */
        $expectedTextValue = $category->getAttributes()->getValue(
            attributeCode: 'title',
            attributeUuid: '87939c45-1d85-4134-9579-d594fff65030',
            channel: 'ecommerce',
            localeCode: 'fr_FR'
        );
        $this->assertSame("Les chaussures dont vous avez besoin !", $expectedTextValue->getValue());
        $this->assertSame('ecommerce', $expectedTextValue->getChannel()?->getValue());
        $this->assertSame('fr_FR', $expectedTextValue->getLocale()?->getValue());

        /** @var ImageValue $expectedImageValue */
        $expectedImageValue = $category->getAttributes()->getValue(
            attributeCode: 'photo',
            attributeUuid: '8587cda6-58c8-47fa-9278-033e1d8c735c',
            channel: null,
            localeCode: null
        );
        $this->assertSame(168107, $expectedImageValue->getValue()->getSize());
        $this->assertSame("jpg", $expectedImageValue->getValue()->getExtension());
        $this->assertSame("8/8/3/d/883d041fc9f22ce42fee07d96c05b0b7ec7e66de_shoes.jpg", $expectedImageValue->getValue()->getFilePath());
        $this->assertSame("image/jpeg", $expectedImageValue->getValue()->getMimeType());
        $this->assertSame("shoes.jpg", $expectedImageValue->getValue()->getOriginalFilename());
        $this->assertNull($expectedImageValue->getChannel());
        $this->assertNull($expectedImageValue->getLocale());
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
        $this->assertSame($tiesCategory->getLabels()->getTranslations(), []);

        $hatsCategory = $this->get(GetCategoryInterface::class)->byCode($hats->getCode());
        $this->assertInstanceOf(Category::class, $hatsCategory);
        $this->assertSame($hatsCategory->getLabels()->getTranslations(), []);
    }

    public function testItIgnoresDeactivateTemplate(): void
    {
        $category = $this->get(GetCategoryInterface::class)->byId($this->category->getId());

        $templateUuid = '02274dac-e99a-4e1d-8f9b-794d4c3ba330';
        $templateModel = $this->givenTemplate($templateUuid, $category->getId());

        $this->get(CategoryTemplateSaver::class)->insert($templateModel);
        $this->get(CategoryTreeTemplateSaver::class)->insert($templateModel);
        $this->deactivateTemplate($templateUuid);

        $retrievedCategory = $this->get(GetCategoryInterface::class)->byId($this->category->getId());

        $this->assertNull($retrievedCategory->getTemplateUuid());
    }

    private function getLastCategoryId(): int
    {
        return (int) $this->get('database_connection')->fetchOne('SELECT MAX(id) FROM pim_catalog_category');
    }

    protected function getConfiguration(): Configuration
    {
        return $this->catalog->useMinimalCatalog();
    }
}
