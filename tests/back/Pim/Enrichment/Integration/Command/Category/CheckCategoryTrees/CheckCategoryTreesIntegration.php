<?php

namespace AkeneoTest\Pim\Enrichment\Integration\Command\Category\CheckCategoryTrees;

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
        $this->createCategory(['code' => 'child4', 'parent' => 'child3']);
        $this->createCategory(['code' => 'child5', 'parent' => 'child3']);

        // Disorder the category trees
        $this->updateOrder('parent', 1, 3);
        $this->updateOrder('child1', 3, 2);
        $this->updateOrder('child2', 4, 7);
        $this->updateOrder('child3', 6, 10);
        $this->updateOrder('child4', 8, 9);
        $this->updateOrder('child5', 9, 10);

        // After reordering, the category trees should be:
        // code, level, left, right
        // parent, 0, 1, 8
        // child1, 1, 2, 3
        // child2, 1, 4, 5
        // child3, 1, 6, 11
        // child4, 1, 7, 8
        // child5, 1, 9, 10
        $output = $this->runCheckCategoryTreesCommand();

        $this->assertStringContainsString('code=parent is CORRUPTED', $output);

        $child1 = $this->getCategory('child1');
        $this->assertSame('2', $child1['lft']);
        $this->assertSame('3', $child1['rgt']);

        $child2 = $this->getCategory('child2');
        $this->assertSame('4', $child2['lft']);
        $this->assertSame('5', $child2['rgt']);

        $child3 = $this->getCategory('child3');
        $this->assertSame('6', $child3['lft']);
        $this->assertSame('11', $child3['rgt']);

        $child3 = $this->getCategory('child4');
        $this->assertSame('7', $child3['lft']);
        $this->assertSame('8', $child3['rgt']);

        $child3 = $this->getCategory('child5');
        $this->assertSame('9', $child3['lft']);
        $this->assertSame('10', $child3['rgt']);

        // Verify that the category tree is now sane
        $output = $this->runCheckCategoryTreesCommand();
        $this->assertStringContainsString('code=parent is SANE', $output);
    }

    private function runCheckCategoryTreesCommand(): string
    {
        $kernel = self::bootKernel();
        $application = new Application($kernel);

        $command = $application->find('akeneo:categories:check-order');

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
        return $this->getConnection()->executeQuery($sql, ['code' => $code])->fetch(FetchMode::ASSOCIATIVE);
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
