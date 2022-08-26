<?php

declare(strict_types=1);

/**
 * @copyright 2022 Akeneo SAS (https://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */

namespace Akeneo\Category\back\tests\Integration\Infrastructure\Storage\Save\Query;
use Akeneo\Category\Application\Storage\Save\Query\UpsertCategoryTranslations;
use Akeneo\Category\back\tests\Integration\Helper\CategoryTestCase;
use Akeneo\Category\Infrastructure\Storage\Save\Query\UpsertCategoryTranslationsSql;
use Akeneo\Category\Infrastructure\Storage\Sql\GetCategorySql;
use Akeneo\Test\Integration\Configuration;
use Akeneo\Test\Integration\TestCase;

class UpsertCategoryTranslationsSqlIntegration extends TestCase
{
    use CategoryTestCase;

    private GetCategorySql $getCategory;

    public function setUp(): void
    {
        parent::setUp();
        $this->getCategory = $this->get(GetCategorySql::class);

    }

    public function testInsertTranslationsInDatabase(): void
    {
        /** @var UpsertCategoryTranslationsSql $upsertCategoryTranslationsQuery */
        $upsertCategoryTranslationsQuery = $this->get(UpsertCategoryTranslations::class);
        $this->assertEquals(UpsertCategoryTranslationsSql::class, $upsertCategoryTranslationsQuery::class);

        $categoryCode = 'myCategory';
        $createdCategory = $this->createOrUpdateCategory(
            code: $categoryCode,
            labels: ['en_US' => 'socks']
        );

        $createdCategory->setLabel('fr_FR', 'chaussettes');
        $upsertCategoryTranslationsQuery->execute($createdCategory);
        $translations = $this->getCategory->byCode((string) $createdCategory->getCode())->getLabelCollection()->getLabels();

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

    public function testUpdateCategoryTranslationsInDatabase(): void
    {
        /** @var UpsertCategoryTranslationsSql $upsertCategoryTranslationsQuery */
        $upsertCategoryTranslationsQuery = $this->get(UpsertCategoryTranslations::class);
        $this->assertEquals(UpsertCategoryTranslationsSql::class, $upsertCategoryTranslationsQuery::class);

        $categoryCode = 'myCategory';
        $createdCategory = $this->createOrUpdateCategory(
            code: $categoryCode,
            labels: ['en_US' => 'socks', 'fr_FR' => 'chaussettes']
        );

        $createdCategory->setLabel('en_US', 'shirts');
        $createdCategory->setLabel('fr_FR', 'chemises');
        $upsertCategoryTranslationsQuery->execute($createdCategory);
        $translations = $this->getCategory->byCode((string) $createdCategory->getCode())->getLabelCollection()->getLabels();

        $this->assertNotNull($translations);
        $this->assertEquals('shirts', $translations['en_US']);
        $this->assertEquals('chemises', $translations['fr_FR']);
    }



    protected function getConfiguration(): Configuration
    {
        return $this->catalog->useMinimalCatalog();
    }
}
