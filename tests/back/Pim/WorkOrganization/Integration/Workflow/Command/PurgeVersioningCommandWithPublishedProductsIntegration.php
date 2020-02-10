<?php

declare(strict_types=1);

namespace AkeneoTestEnterprise\Pim\WorkOrganization\Integration\Workflow\Command;

use Akeneo\Pim\Enrichment\Component\Product\Model\Product;
use Akeneo\Pim\WorkOrganization\Workflow\Component\Model\PublishedProductInterface;
use Akeneo\Test\Integration\TestCase;
use Akeneo\Tool\Component\Versioning\Model\Version;
use Doctrine\DBAL\Connection;
use Symfony\Bundle\FrameworkBundle\Console\Application;
use Symfony\Component\Console\Input\ArrayInput;
use Symfony\Component\Console\Output\BufferedOutput;

/**
 * @copyright 2020 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class PurgeVersioningCommandWithPublishedProductsIntegration extends TestCase
{
    /**
     * @test
     */
    public function it_purges_versions_but_keeps_published_product_versions(): void
    {
        $publishedProduct1 = $this->createPublishedProduct('my_published_product1', ['categories' => ['categoryA']]);
        $product1 = $publishedProduct1->getOriginalProduct();
        $this->addVersion($product1->getId(), 4);
        $this->addVersion($product1->getId(), 5);
        $this->changeVersionsDate($product1->getId(), new \DateTime('now -5 DAYS'));

        $versions = $this->getConnection()->executeQuery('SELECT id, resource_id, version, logged_at FROM pim_versioning_version')->fetchAll();
        $this->assertCount(5, $versions);

        $output = $this->runPurgeCommand();
        $result = $output->fetch();

        $this->assertContains(sprintf('Start purging versions of %s (1/1)', Product::class), $result);
        $this->assertContains('Successfully deleted 2 versions', $result);

        $versions = $this->getConnection()->executeQuery('SELECT id, resource_id, version FROM pim_versioning_version')->fetchAll();
        $this->assertCount(3, $versions);
        $this->assertEquals(1, $versions[0]['version']);
        $this->assertEquals(3, $versions[1]['version']);
        $this->assertEquals(5, $versions[2]['version']);
    }

    /**
     * @inheritDoc
     */
    protected function getConfiguration()
    {
        return $this->catalog->useTechnicalCatalog();
    }

    private function getConnection(): Connection
    {
        return $this->get('database_connection');
    }

    protected function setUp()
    {
        parent::setUp();

        $this->getConnection()->executeQuery('DELETE FROM pim_versioning_version');
    }

    /**
     * Create a product with 3 versions and publish the last one.
     */
    private function createPublishedProduct(string $identifier, array $data): PublishedProductInterface
    {
        $product = $this->get('pim_catalog.builder.product')->createProduct($identifier, 'familyA2');

        $data = array_merge([
            'values' => [
                'a_metric' => [
                    ['data' => ['amount' => 1, 'unit' => 'WATT'], 'locale' => null, 'scope' => null],
                ],
            ],
        ], $data);

        $this->get('pim_catalog.updater.product')->update($product, $data);
        $this->get('pim_catalog.saver.product')->save($product);

        $this->addVersion($product->getId(), 2);
        $this->addVersion($product->getId(), 3);

        return $this->get('pimee_workflow.manager.published_product')->publish($product);
    }

    private function addVersion(
        int $resourceId,
        int $versionNumber = 1
    ): void {
        $loggedAt = new \DateTime('now');
        $this->get('database_connection')->executeQuery(
            'INSERT INTO pim_versioning_version (resource_name, resource_id, version, logged_at, author, changeset, pending) 
             VALUES (:resource_name, :resource_id, :version, :logged_at, "test", :changeset, false)',
            [
                'resource_name' => Product::class,
                'resource_id' => $resourceId,
                'version' => $versionNumber,
                'logged_at' => $loggedAt->format('Y-m-d H:i:s'),
                'changeset' => serialize([]),
            ],
            [
                'resource_name' => \PDO::PARAM_STR,
                'resource_id' => \PDO::PARAM_INT,
                'version' => \PDO::PARAM_INT,
                'logged_at' => \PDO::PARAM_STR,
                'changeset' => \PDO::PARAM_STR,
            ]
        );
    }

    private function changeVersionsDate(
        int $resourceId,
        \DateTime $newDate
    ): void {
        $this->get('database_connection')->executeQuery(
            'UPDATE pim_versioning_version SET logged_at = :logged_at WHERE resource_id = :resource_id',
            [
                'resource_id' => $resourceId,
                'logged_at' => $newDate->format('Y-m-d H:i:s'),
            ],
            [
                'resource_id' => \PDO::PARAM_INT,
                'logged_at' => \PDO::PARAM_STR,
            ]
        );
    }

    private function runPurgeCommand(array $arrayInput = []): BufferedOutput
    {
        $application = new Application(static::$kernel);
        $application->setAutoExit(false);

        $defaultArrayInput = [
            'command' => 'pim:versioning:purge',
            'entity' => Product::class,
            '--more-than-days' => 0,
            '--force' => null,
        ];

        $arrayInput = array_merge($defaultArrayInput, $arrayInput);
        if (isset($arrayInput['--config'])) {
            $arrayInput['--config'] = json_encode($arrayInput['--config']);
        }

        $input = new ArrayInput($arrayInput);
        $output = new BufferedOutput();
        $application->run($input, $output);

        return $output;
    }
}
