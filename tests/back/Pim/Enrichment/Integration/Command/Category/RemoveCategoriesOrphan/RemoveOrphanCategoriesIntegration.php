<?php

namespace AkeneoTest\Pim\Enrichment\Integration\Command\Category\RemoveCategoriesOrphan;

use Akeneo\Category\Domain\Model\Enrichment\Category;
use Akeneo\Category\Domain\Query\GetCategoryInterface;
use Akeneo\Test\Integration\Configuration;
use Akeneo\Test\Integration\TestCase;
use Doctrine\DBAL\Driver\Connection;
use Symfony\Bundle\FrameworkBundle\Console\Application;
use Symfony\Component\Console\Tester\CommandTester;

/**
 * @copyright 2022 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php Open Software License (OSL 3.0)
 */
class RemoveOrphanCategoriesIntegration extends TestCase
{
    protected function getConfiguration(): Configuration
    {
        return $this->catalog->useMinimalCatalog();
    }

    public function testCommandRemoveOrphanCategories(): void
    {
        $this->createCategory(['code' => 'parent']);
        $this->createCategory(['code' => 'child1', 'parent' => 'parent']);
        $this->createCategory(['code' => 'child2', 'parent' => 'parent']);
        $this->createCategory(['code' => 'child3', 'parent' => 'parent']);
        $this->createCategory(['code' => 'child4', 'parent' => 'child3']);
        $this->createCategory(['code' => 'child5', 'parent' => 'child4']);

        $output = $this->runRemoveOrphanCategoriesCommand();

        $this->assertStringContainsString('0 rows removed', $output);
        /** @var ?Category $child3 */
        $child3 = $this->get(GetCategoryInterface::class)->byCode('child3');
        $this->assertNotNull($child3->getParentId());

        $this->setCategoryParentIdToNull('child3');
        $child3 = $this->get(GetCategoryInterface::class)->byCode('child3');
        $this->assertNull($child3->getParentId());

        $output = $this->runRemoveOrphanCategoriesCommand();
        $this->assertStringContainsString('3 rows removed', $output);

        $nonOrphanCategories = $this->get(GetCategoryInterface::class)->byCodes(['parent', 'child1', 'child2', 'child3', 'child4', 'child5']);
        $this->assertCount( 3, $nonOrphanCategories);
    }

    private function runRemoveOrphanCategoriesCommand(): string
    {
        $kernel = self::bootKernel();
        $application = new Application($kernel);

        $command = $application->find('akeneo:categories:remove-orphans');

        $commandTester = new CommandTester($command);
        $commandTester->execute(['command' => $command->getName()]);

        return $commandTester->getDisplay();
    }

    private function setCategoryParentIdToNull(string $code): void
    {
        $sql = <<< SQL
            UPDATE pim_catalog_category
            SET parent_id = NULL
            WHERE code = :code;
        SQL;
        $this->getConnection()->executeQuery($sql, ['code' => $code]);
    }

    private function getConnection(): Connection
    {
        return $this->get('doctrine.orm.entity_manager')->getConnection();
    }
}
