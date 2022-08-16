<?php

declare(strict_types=1);

/**
 * @copyright 2022 Akeneo SAS (https://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */

namespace Akeneo\Category\back\tests\Integration\Infrastructure\Storage\Save\Query;
use Akeneo\Category\Application\Storage\Save\Query\UpsertCategoryTranslations;
use Akeneo\Category\back\tests\Integration\Helper\CategoryTrait;
use Akeneo\Category\Domain\Model\Category;
use Akeneo\Category\Domain\ValueObject\CategoryId;
use Akeneo\Category\Domain\ValueObject\Code;
use Akeneo\Category\Domain\ValueObject\LabelCollection;
use Akeneo\Category\Infrastructure\Storage\Save\Query\SqlUpsertCategoryTranslations;
use Akeneo\Test\Integration\Configuration;
use Akeneo\Test\Integration\TestCase;

class SqlUpsertCategoryTranslationsIntegration extends TestCase
{
    use CategoryTrait;

    public function testInsertNewTranslationsInDatabase()
    {
        /** @var SqlUpsertCategoryTranslations $upsertCategoryTranslationsQuery */
        $upsertCategoryTranslationsQuery = $this->get(UpsertCategoryTranslations::class);
        $this->assertEquals(SqlUpsertCategoryTranslations::class, $upsertCategoryTranslationsQuery::class);

        $categoryCode = 'myCategory';
        $createdCategory = $this->createOrUpdateCategory(
            code: $categoryCode,
            labels: ['en_US' => 'sausages']
        );

        $createdCategory->setLabel('fr_FR', 'saucisses');

        $upsertCategoryTranslationsQuery->execute($createdCategory);
        $translations = $this->getCategoryTranslationsDataByCategoryCode((string) $createdCategory->getCode());

        $this->assertNotNull($translations);
        $this->assertEqualsCanonicalizing(
            $createdCategory->getLabelCollection()->getLabel('en_US'),
            $translations['en_US']
        );
        $this->assertEqualsCanonicalizing(
            $createdCategory->getLabelCollection()->getLabel('fr_FR'),
            $translations['fr_FR']
        );
    }

    public function testUpdateExistingCategoryTranslationsInDatabase()
    {
        /** @var SqlUpsertCategoryTranslations $upsertCategoryTranslationsQuery */
        $upsertCategoryTranslationsQuery = $this->get(UpsertCategoryTranslations::class);
        $this->assertEquals(SqlUpsertCategoryTranslations::class, $upsertCategoryTranslationsQuery::class);

        $categoryCode = 'myCategory';
        $category = new Category(
            null,
            new Code($categoryCode),
            LabelCollection::fromArray([]),
            null
        );

        $categoryId = $upsertCategoryTranslationsQuery->execute($category);
        $result = $this->getCategoryTranslationsDataByCategoryCode($categoryId);

        $this->assertNotNull($result);
        $this->assertSame((string) $category->getCode(), $result['code']);

        $updatedCategory = new Category(
            new CategoryId((int) $categoryId),
            new Code('updatedCode'),
            LabelCollection::fromArray([]),
            null
        );

        $newCategoryId = $upsertCategoryTranslationsQuery->execute($updatedCategory);
        $this->assertSame($categoryId, $newCategoryId);

        $newResult = $this->getCategoryTranslationsDataByCategoryCode($categoryId);

        $this->assertNotNull($newResult);
        $this->assertSame((string) $updatedCategory->getCode(), $newResult['code']);
        $this->assertSame($categoryId, (int) $newResult['id']);

        // TODO on ajoute les tests sur les locales
    }



    protected function getConfiguration(): Configuration
    {
        return $this->catalog->useMinimalCatalog();
    }
}
