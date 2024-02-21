<?php

declare(strict_types=1);

namespace Akeneo\Test\Category\Integration\Infrastructure\Storage\Sql\Update;

use Akeneo\Category\back\tests\Integration\Helper\CategoryTestCase;
use Akeneo\Category\Domain\Model\Enrichment\Category;
use Akeneo\Category\Domain\Query\GetCategoryInterface;
use Akeneo\Category\Domain\Query\UpdateCategoryUpdatedDate;

/**
 * @copyright 2023 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php Open Software License (OSL 3.0)
 */
class UpdateCategoryUpdatedDateSqlIntegration extends CategoryTestCase
{

    protected function setUp(): void
    {
        parent::setUp();
        $this->createOrUpdateCategory(
            code: 'socks',
            labels: ['en_US' => 'socks'],
        );
    }

    public function testItUpdatesCategoryUpdatedDate(): void
    {
        /** @var Category $previousCategory */
        $previousCategory = $this->get(GetCategoryInterface::class)->byCode('master');

        $this->get(UpdateCategoryUpdatedDate::class)->execute('master');

        /** @var Category $updatedCategory */
        $updatedCategory = $this->get(GetCategoryInterface::class)->byCode('master');

        $this->assertEquals((string)$updatedCategory->getCode(), (string)$previousCategory->getCode());
        $this->assertGreaterThan(
            $previousCategory->getUpdated()->format('Y-m-d H:i:s'),
            $updatedCategory->getUpdated()->format('Y-m-d H:i:s')
        );
    }

    public function testItDoesNotUpdateCategoryUpdatedDate(): void
    {
        /** @var Category $previousCategory */
        $previousCategory = $this->get(GetCategoryInterface::class)->byCode('master');

        $this->get(UpdateCategoryUpdatedDate::class)->execute('socks');

        /** @var Category $notUpdatedCategory */
        $notUpdatedCategory = $this->get(GetCategoryInterface::class)->byCode('master');

        $this->assertEquals((string)$notUpdatedCategory->getCode(), (string)$previousCategory->getCode());
        $this->assertEquals(
            $previousCategory->getUpdated()->format('Y-m-d H:i:s'),
            $notUpdatedCategory->getUpdated()->format('Y-m-d H:i:s')
        );
    }
}
