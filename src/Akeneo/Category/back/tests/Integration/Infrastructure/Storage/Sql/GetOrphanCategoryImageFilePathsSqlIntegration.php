<?php

declare(strict_types=1);

namespace Akeneo\Category\back\tests\Integration\Infrastructure\Storage\Sql;

use Akeneo\Category\back\tests\Integration\Helper\CategoryTestCase;
use Akeneo\Category\Domain\DTO\IteratorStatus;
use Akeneo\Category\Domain\ImageFile\GetOrphanCategoryImageFilePaths;

/**
 * @copyright 2023 Akeneo SAS (https://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class GetOrphanCategoryImageFilePathsSqlIntegration extends CategoryTestCase
{
    public function testItGetsOrphanCategoryImageFilePaths(): void
    {
        $categorySocks = $this->useTemplateFunctionalCatalog('6344aa2a-2be9-4093-b644-259ca7aee50c', 'socks');
        $this->updateCategoryWithValues((string) $categorySocks->getCode());

        // Linked to an attribute value
        $categoryImageFileName = '883d041fc9f22ce42fee07d96c05b0b7ec7e66de_shoes.jpg';
        $categoryImageFilePath = '8/8/3/d/' . $categoryImageFileName;
        $this->insertFileStorage($categoryImageFilePath, $categoryImageFileName);

        // Linked to any attribute value -> orphan
        $orphanFileName = 'file1.jpg';
        $orphanFilePath = 'a_category/' . $orphanFileName;
        $this->insertFileStorage($orphanFilePath, $orphanFileName);

        $iterator = ($this->get(GetOrphanCategoryImageFilePaths::class))();
        $results = iterator_to_array($iterator);

        $expected = [
            IteratorStatus::inProgress(),
            IteratorStatus::done([$orphanFilePath])
        ];
        $this->assertEquals($expected, $results);
    }
}
