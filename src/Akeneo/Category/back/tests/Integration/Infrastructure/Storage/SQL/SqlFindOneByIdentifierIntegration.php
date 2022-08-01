<?php

declare(strict_types=1);

namespace Akeneo\Category\back\tests\Integration\Infrastructure\Storage\SQL;

use Akeneo\Category\Application\Query\FindCategoryByIdentifier;
use Akeneo\Category\Domain\Model\Category;
use Akeneo\Test\Integration\TestCase;

/**
 * @copyright 2022 Akeneo SAS (https://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class SqlFindOneByIdentifierIntegration extends TestCase
{
    public function testFindOneByIdentifier(): void
    {
        $category = $this->get(FindCategoryByIdentifier::class)(2);
        $this->assertInstanceOf(Category::class, $category);
        $this->assertSame('master', (string)$category->getCode());
        $this->assertSame('Master catalog', $category->getLabelCollection()->getLabel('en_US'));
    }

    protected function getConfiguration()
    {
        return $this->catalog->useMinimalCatalog();
    }
}
