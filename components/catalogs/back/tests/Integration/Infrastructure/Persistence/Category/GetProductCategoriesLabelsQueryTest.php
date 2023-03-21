<?php

declare(strict_types=1);

namespace Akeneo\Catalogs\Test\Integration\Infrastructure\Persistence\Category;

use Akeneo\Catalogs\Infrastructure\Persistence\Category\GetProductCategoriesLabelsQuery;
use Akeneo\Catalogs\Test\Integration\IntegrationTestCase;
use Akeneo\Pim\Enrichment\Product\API\Command\UserIntent\SetCategories;
use Ramsey\Uuid\Uuid;

/**
 * @copyright 2022 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 *
 * @covers \Akeneo\Catalogs\Infrastructure\Persistence\Category\GetProductCategoriesLabelsQuery
 */
class GetProductCategoriesLabelsQueryTest extends IntegrationTestCase
{
    private ?GetProductCategoriesLabelsQuery $query;

    protected function setUp(): void
    {
        parent::setUp();

        $this->purgeDataAndLoadMinimalCatalog();
        $this->createUser('admin', ['IT support'], ['ROLE_ADMINISTRATOR']);
        $this->query = self::getContainer()->get(GetProductCategoriesLabelsQuery::class);
        $this->logAs('admin');
    }

    public function testItGetsCategoriesLabels(): void
    {
        $this->createCategory(['code' => 'cameras', 'labels' => ['en_US' => 'Cameras']]);
        $this->createCategory(['code' => 'digital_cameras', 'labels' => ['en_US' => 'Digital cameras']]);

        $this->createProduct(Uuid::fromString('008cc715-77f4-4061-ab7b-8cb6d9fc4ce3'), [
            new SetCategories(['cameras', 'digital_cameras']),
        ]);

        $result = $this->query->execute('008cc715-77f4-4061-ab7b-8cb6d9fc4ce3', 'en_US');

        $this->assertEquals([
            'Cameras',
            'Digital cameras',
        ], $result);
    }

    public function testItGetsCategoriesCodeWhenLabelIsNotFound(): void
    {
        $this->createCategory(['code' => 'cameras', 'labels' => ['en_US' => 'Cameras']]);
        $this->createCategory(['code' => 'digital_cameras', 'labels' => ['en_US' => 'Digital cameras']]);

        $this->createProduct(Uuid::fromString('008cc715-77f4-4061-ab7b-8cb6d9fc4ce3'), [
            new SetCategories(['cameras', 'digital_cameras']),
        ]);

        $result = $this->query->execute('008cc715-77f4-4061-ab7b-8cb6d9fc4ce3', 'fr_FR');

        $this->assertEquals([
            '[cameras]',
            '[digital_cameras]',
        ], $result);
    }

    public function testItReturnsAnEmptyArrayForInvalidCodeList(): void
    {
        $this->createProduct(Uuid::fromString('008cc715-77f4-4061-ab7b-8cb6d9fc4ce3'));

        $result = $this->query->execute('008cc715-77f4-4061-ab7b-8cb6d9fc4ce3', 'fr_FR');

        $this->assertEmpty($result, 'No category should be found');
    }
}
