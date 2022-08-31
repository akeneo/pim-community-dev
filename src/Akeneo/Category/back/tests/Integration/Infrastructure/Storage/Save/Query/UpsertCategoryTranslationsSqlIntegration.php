<?php

declare(strict_types=1);

/**
 * @copyright 2022 Akeneo SAS (https://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */

namespace Akeneo\Test\Category\Integration\Infrastructure\Storage\Save\Query;

use Akeneo\Category\Application\Storage\Save\Query\UpsertCategoryTranslations;
use Akeneo\Category\back\tests\Integration\Helper\CategoryTestCase;
use Akeneo\Category\Domain\Query\GetCategoryInterface;
use Akeneo\Test\Integration\Configuration;

class UpsertCategoryTranslationsSqlIntegration extends CategoryTestCase
{
    private GetCategoryInterface $getCategory;
    private UpsertCategoryTranslations $upsertCategoryTranslations;

    public function setUp(): void
    {
        parent::setUp();

        $this->getCategory = $this->get(GetCategoryInterface::class);
        $this->upsertCategoryTranslations = $this->get(UpsertCategoryTranslations::class);
    }

    public function testInsertTranslationsInDatabase(): void
    {
        $categoryCode = 'myCategory';
        $createdCategory = $this->createOrUpdateCategory(
            code: $categoryCode,
            labels: ['en_US' => 'socks']
        );

        $createdCategory->setLabel('fr_FR', 'chaussettes');
        $this->upsertCategoryTranslations->execute($createdCategory);
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
        $categoryCode = 'myCategory';
        $createdCategory = $this->createOrUpdateCategory(
            code: $categoryCode,
            labels: ['en_US' => 'socks', 'fr_FR' => 'chaussettes']
        );

        $createdCategory->setLabel('en_US', 'shirts');
        $createdCategory->setLabel('fr_FR', 'chemises');
        $this->upsertCategoryTranslations->execute($createdCategory);
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
