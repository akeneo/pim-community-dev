<?php

declare(strict_types=1);

namespace Akeneo\Category\back\tests\Integration\Infrastructure\Storage\Sql;

use Akeneo\Category\Application\Storage\Save\Saver\CategoryTemplateAttributeSaver;
use Akeneo\Category\Application\Storage\Save\Saver\CategoryTemplateSaver;
use Akeneo\Category\Application\Storage\Save\Saver\CategoryTreeTemplateSaver;
use Akeneo\Category\back\tests\Integration\Helper\CategoryTestCase;
use Akeneo\Category\Domain\Model\Enrichment\Category;
use Akeneo\Category\Domain\Query\GetCategoryInterface;
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

    /**
     * @var array<array<string, mixed>>
     */
    private array $attributes;

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

        $this->attributes = [
                [
                    'type' => 'image',
                    'uuid' => '8587cda6-58c8-47fa-9278-033e1d8c735c',
                    'code' => 'photo',
                    'order' => 1,
                    'is_required' => true,
                    'is_scopable' => false,
                    'is_localizable' => false,
                    'labels' => ['en_US' => 'photo'],
                    'template_uuid' => '02274dac-e99a-4e1d-8f9b-794d4c3ba330',
                    'additional_properties' => [],
                ],
                [
                    'type' => 'text',
                    'uuid' => '57665726-8a6e-4550-9bcf-06f81c0d1e24',
                    'code' => 'description',
                    'order' => 1,
                    'is_required' => true,
                    'is_scopable' => true,
                    'is_localizable' => true,
                    'labels' => ['en_US' => 'description'],
                    'template_uuid' => '02274dac-e99a-4e1d-8f9b-794d4c3ba330',
                    'additional_properties' => [],
                ],
                [
                    'type' => 'text',
                    'uuid' => '87939c45-1d85-4134-9579-d594fff65030',
                    'code' => 'title',
                    'order' => 2,
                    'is_required' => true,
                    'is_scopable' => true,
                    'is_localizable' => true,
                    'labels' => ['en_US' => 'title'],
                    'template_uuid' => '02274dac-e99a-4e1d-8f9b-794d4c3ba330',
                    'additional_properties' => [],
                ],
            ];

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

        /** @var TextValue|null $expectedTextValue */
        $expectedTextValue = $category->getAttributes()->getValue(
            attributeCode: 'title',
            attributeUuid: '87939c45-1d85-4134-9579-d594fff65030',
            channel: 'ecommerce',
            localeCode: 'fr_FR'
        );
        $this->assertSame('Les chaussures dont vous avez besoin !', $expectedTextValue->getValue());
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
        $this->assertSame('jpg', $expectedImageValue->getValue()->getExtension());
        $this->assertSame('8/8/3/d/883d041fc9f22ce42fee07d96c05b0b7ec7e66de_shoes.jpg', $expectedImageValue->getValue()->getFilePath());
        $this->assertSame('image/jpeg', $expectedImageValue->getValue()->getMimeType());
        $this->assertSame('shoes.jpg', $expectedImageValue->getValue()->getOriginalFilename());
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
        $this->assertSame('Les chaussures dont vous avez besoin !', $expectedTextValue->getValue());
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
        $this->assertSame('jpg', $expectedImageValue->getValue()->getExtension());
        $this->assertSame('8/8/3/d/883d041fc9f22ce42fee07d96c05b0b7ec7e66de_shoes.jpg', $expectedImageValue->getValue()->getFilePath());
        $this->assertSame('image/jpeg', $expectedImageValue->getValue()->getMimeType());
        $this->assertSame('shoes.jpg', $expectedImageValue->getValue()->getOriginalFilename());
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

    public function testItIgnoresDeactivatedTemplate(): void
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

    public function testItDoesNotRetrieveDeactivatedAttributesValueForOneCategory(): void
    {
        $category = $this->get(GetCategoryInterface::class)->byCode('socks');
        $this->updateCategoryWithValues('socks');

        $templateModel = $this->generateMockedCategoryTemplateModel(
            categoryTreeId: $category->getId()->getValue(),
            templateAttributes: $this->attributes,
        );

        $this->get(CategoryTemplateSaver::class)->insert($templateModel);
        $this->get(CategoryTreeTemplateSaver::class)->insert($templateModel);
        $this->get(CategoryTemplateAttributeSaver::class)->insert(
            $templateModel->getUuid(),
            $templateModel->getAttributeCollection(),
        );

        $this->deactivateAttribute('87939c45-1d85-4134-9579-d594fff65030');
        $this->deactivateAttribute('57665726-8a6e-4550-9bcf-06f81c0d1e24');

        /** @var Category $filteredCategory */
        $filteredCategory = $this->get(GetCategoryInterface::class)->byCode('socks');
        $values = $filteredCategory->getAttributes()?->getValues();
        $this->assertCount(1, $values);
        $this->assertEquals('photo', $values[0]->getCode());

    }

    public function testItDoesNotRetrieveDeactivatedAttributesValueForSeveralCategories(): void
    {
        $category = $this->get(GetCategoryInterface::class)->byCode('socks');
        $this->updateCategoryWithValues('socks');

        $templateModel = $this->generateMockedCategoryTemplateModel(
            categoryTreeId: $category->getId()->getValue(),
            templateAttributes: $this->attributes,
        );

        $this->get(CategoryTemplateSaver::class)->insert($templateModel);
        $this->get(CategoryTreeTemplateSaver::class)->insert($templateModel);
        $this->get(CategoryTemplateAttributeSaver::class)->insert(
            $templateModel->getUuid(),
            $templateModel->getAttributeCollection(),
        );

        $this->createOrUpdateCategory(
            code: 'japanese_socks',
            parentId: $category->getId()->getValue(),
            rootId: $category->getId()->getValue()
        );
        $this->updateCategoryWithValues('japanese_socks');

        $this->deactivateAttribute('87939c45-1d85-4134-9579-d594fff65030');
        $this->deactivateAttribute('57665726-8a6e-4550-9bcf-06f81c0d1e24');

        /** @var Category $filteredCategory */
        foreach ($this->get(GetCategoryInterface::class)->byCodes(['socks', 'japanese_socks']) as $filteredCategory)
        {
            $values = $filteredCategory->getAttributes()?->getValues();
            $this->assertCount(1, $values);
            $this->assertEquals('photo', $values[0]->getCode());
        }
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
