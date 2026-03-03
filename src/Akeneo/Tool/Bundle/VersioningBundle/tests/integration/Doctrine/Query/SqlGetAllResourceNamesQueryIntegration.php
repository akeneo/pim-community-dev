<?php

declare(strict_types=1);

namespace Akeneo\Tool\Bundle\VersioningBundle\tests\integration\Doctrine\Query;

use Akeneo\Pim\Enrichment\Component\Product\Model\ProductModel;
use Akeneo\Pim\Structure\Component\Model\Attribute;
use Akeneo\Test\Integration\TestCase;
use Akeneo\Tool\Bundle\VersioningBundle\Doctrine\Query\SqlGetAllResourceNamesQuery;
use Akeneo\Tool\Component\Versioning\Model\Version;

/**
 * @copyright 2019 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class SqlGetAllResourceNamesQueryIntegration extends TestCase
{
    /**
     * @test
     */
    public function it_returns_all_the_versions_resource_names(): void
    {
        $expectedResourceNames = [
            ProductModel::class,
            Attribute::class,
        ];

        $this->createVersionsForResourceNames($expectedResourceNames);

        $resourceNames = $this->getQuery()->execute();
        $this->assertSame($expectedResourceNames, $resourceNames);
    }

    /**
     * @inheritDoc
     */
    protected function getConfiguration()
    {
        return $this->catalog->useMinimalCatalog();
    }

    protected function setUp():void
    {
        parent::setUp();

        $this->get('database_connection')->executeQuery('DELETE FROM pim_versioning_version');
    }

    private function getQuery(): SqlGetAllResourceNamesQuery
    {
        return $this->get('pim_versioning.query.get_all_resource_names');
    }

    private function createVersionsForResourceNames(array $resourceNames): void
    {
        $entityManager = $this->get('doctrine.orm.default_entity_manager');

        foreach ($resourceNames as $resourceName) {
            for ($versionNumber = 1; $versionNumber < 3; $versionNumber++) {
                $version = new Version($resourceName, 12142, null, 'system');
                $version->setVersion($versionNumber);
                $entityManager->persist($version);
            }
        }

        $entityManager->flush();
    }
}
