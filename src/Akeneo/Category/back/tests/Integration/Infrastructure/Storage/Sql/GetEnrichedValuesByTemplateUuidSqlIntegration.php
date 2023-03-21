<?php

declare(strict_types=1);

namespace Akeneo\Category\back\tests\Integration\Infrastructure\Storage\Sql;

use Akeneo\Category\back\tests\Integration\Helper\CategoryTestCase;
use Akeneo\Category\Domain\Model\Enrichment\Category;
use Akeneo\Category\Domain\Query\GetCategoryInterface;
use Akeneo\Category\Domain\Query\GetEnrichedValuesByTemplateUuid;
use Akeneo\Category\Domain\ValueObject\Template\TemplateUuid;
use PHPUnit\Framework\Assert;

/**
 * @copyright 2023 Akeneo SAS (https://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class GetEnrichedValuesByTemplateUuidSqlIntegration extends CategoryTestCase
{
    protected function setUp(): void
    {
        parent::setUp();
        $socksCategory = $this->useTemplateFunctionalCatalog('6344aa2a-2be9-4093-b644-259ca7aee50c', 'socks');
        $winterSocksCategory = $this->createOrUpdateCategory(
            code: 'winter_socks',
            parentId: $socksCategory->getId()?->getValue(),
            rootId: $socksCategory->getId()?->getValue(),
        );
        $summerSocksCategory = $this->createOrUpdateCategory(
            code: 'summer_socks',
            parentId: $socksCategory->getId()?->getValue(),
            rootId: $socksCategory->getId()?->getValue(),
        );
        $japaneseSummerSocksCategory = $this->createOrUpdateCategory(
            code: 'japanese_summer_socks',
            parentId: $summerSocksCategory->getId()?->getValue(),
            rootId: $socksCategory->getId()?->getValue(),
        );
        $this->updateCategoryWithValues((string) $socksCategory->getCode());
        $this->updateCategoryWithValues((string) $winterSocksCategory->getCode());
        $this->updateCategoryWithValues((string) $summerSocksCategory->getCode());
        $this->updateCategoryWithValues((string) $japaneseSummerSocksCategory->getCode());

        $attributesUuids = $this->generateRandomUuidList(13);
        $pantsCategory = $this->useTemplateFunctionalCatalog('1294e06d-48a4-4055-abda-986d92bef8a2', 'pants', $attributesUuids);
        $this->updateCategoryWithValues((string) $pantsCategory->getCode());
    }

    public function testItRetrievesRelatedCategoriesByTemplateUuid(): void{
        $fetchedCategories = [];
        foreach ($this->get(GetEnrichedValuesByTemplateUuid::class)->byBatchesOf(
            TemplateUuid::fromString('6344aa2a-2be9-4093-b644-259ca7aee50c'),
            5
        ) as $valuesByCategoryCode){
            $fetchedCategories[] = $valuesByCategoryCode;
        }

        Assert::assertArrayHasKey('socks', $fetchedCategories[0]);
        Assert::assertArrayHasKey('winter_socks', $fetchedCategories[0]);
        Assert::assertArrayHasKey('summer_socks', $fetchedCategories[0]);
        Assert::assertArrayHasKey('japanese_summer_socks', $fetchedCategories[0]);
        Assert::assertNotEmpty($fetchedCategories[0]['socks']->getValues());
        Assert::assertNotEmpty($fetchedCategories[0]['winter_socks']->getValues());
        Assert::assertNotEmpty($fetchedCategories[0]['summer_socks']->getValues());
        Assert::assertNotEmpty($fetchedCategories[0]['japanese_summer_socks']->getValues());
        Assert::assertCount(4, $fetchedCategories[0]);
    }

    public function testItRetrievesRelatedCategoriesByTemplateUuidWithoutNull(): void{
        /** @var Category $socksCategory */
        $socksCategory = $this->get(GetCategoryInterface::class)->byCode('socks');
        $this->createOrUpdateCategory(
            code: 'winter_socks_null',
            parentId: $socksCategory->getId()?->getValue(),
            rootId: $socksCategory->getId()?->getValue(),
        );

        $fetchedCategories = [];
        foreach ($this->get(GetEnrichedValuesByTemplateUuid::class)->byBatchesOf(
            TemplateUuid::fromString('6344aa2a-2be9-4093-b644-259ca7aee50c'),
            5
        ) as $valuesByCategoryCode){
            $fetchedCategories[] = $valuesByCategoryCode;
        }

        Assert::assertArrayHasKey('socks', $fetchedCategories[0]);
        Assert::assertArrayHasKey('winter_socks', $fetchedCategories[0]);
        Assert::assertArrayHasKey('summer_socks', $fetchedCategories[0]);
        Assert::assertArrayHasKey('japanese_summer_socks', $fetchedCategories[0]);
        Assert::assertArrayNotHasKey('winter_socks_null', $fetchedCategories[0]);
        Assert::assertNotEmpty($fetchedCategories[0]['socks']->getValues());
        Assert::assertNotEmpty($fetchedCategories[0]['winter_socks']->getValues());
        Assert::assertNotEmpty($fetchedCategories[0]['summer_socks']->getValues());
        Assert::assertNotEmpty($fetchedCategories[0]['japanese_summer_socks']->getValues());
        Assert::assertCount(4, $fetchedCategories[0]);
    }
}
