<?php

namespace AkeneoTest\Pim\Enrichment\Integration\Command\Category;

use Akeneo\Pim\Enrichment\Bundle\Command\Category\Category;
use Akeneo\Pim\Enrichment\Bundle\Doctrine\Common\Saver\CategorySaver;
use Akeneo\Test\Integration\Configuration;
use Akeneo\Test\Integration\TestCase;
use Doctrine\DBAL\Driver\Connection;
use Doctrine\DBAL\FetchMode;
use Symfony\Bundle\FrameworkBundle\Console\Application;
use Symfony\Component\Console\Tester\CommandTester;

/**
 * @copyright 2022 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php Open Software License (OSL 3.0)
 */
class CheckCategoryTreesIntegration extends TestCase
{
    protected function getConfiguration(): Configuration
    {
        return $this->catalog->useMinimalCatalog();
    }

    public function testCommandReorderingCategoryTrees(): void
    {
        $this->createCategory(['code' => 'parent']);
        $this->createCategory(['code' => 'child1', 'parent' => 'parent']);
        $this->createCategory(['code' => 'child2', 'parent' => 'parent']);
        $this->createCategory(['code' => 'child3', 'parent' => 'parent']);

        $this->updateOrder('parent', 1, 8);
        $this->updateOrder('child1', 3, 2);
        $this->updateOrder('child2', 4, 7);
        $this->updateOrder('child3', 6, 7);

        $output = $this->runCheckCategoryTreesCommand();

//        $category = $this->getCategory('child1');
//
//        dump($category);

        $this->assertStringContainsString('test', $output);
    }

    private function runCheckCategoryTreesCommand(): string
    {
        $kernel = self::bootKernel();
        $application = new Application($kernel);

        $command = $application->find('pim:categories:check-order');

        $commandTester = new CommandTester($command);
        $commandTester->execute([
            'command' => $command->getName(),
            '--dump-corruptions' => true,
            '--reorder' => true,
        ]);

        return $commandTester->getDisplay();
    }

    private function getCategory(string $code): array
    {
        $sql = <<< SQL
SELECT id, parent_id, root, code, lvl, lft, rgt
FROM pim_catalog_category
WHERE code = :code
SQL;
        return $this->getConnection()->executeQuery($sql, ['code' => $code])->fetchAll(FetchMode::ASSOCIATIVE);
    }

    private function updateOrder(string $code, int $left, int $right): array
    {
        $sql = <<< SQL
UPDATE pim_catalog_category
SET lft = :left, rgt = :right
WHERE code = :code
SQL;
        return $this->getConnection()->executeQuery($sql, ['code' => $code, 'left' => $left, 'right' => $right])->fetchAll(FetchMode::ASSOCIATIVE);
    }

    private function getConnection(): Connection
    {
        return $this->get('doctrine.orm.entity_manager')->getConnection();
    }
}
