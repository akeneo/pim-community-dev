<?php

namespace AkeneoTest\Pim\Enrichment\Integration\Command\Category\RemoveCategoriesOrphan;

use Akeneo\Category\back\tests\Integration\Helper\CategoryTestCase;
use Akeneo\Category\Domain\Model\Enrichment\Category;
use Akeneo\Category\Domain\Query\GetCategoryInterface;
use Akeneo\Category\Domain\ValueObject\Code;
use Akeneo\Category\Domain\ValueObject\PermissionCollection;
use Symfony\Bundle\FrameworkBundle\Console\Application;
use Symfony\Component\Console\Tester\CommandTester;

/**
 * @copyright 2023 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php Open Software License (OSL 3.0)
 */
class RemoveOrphanCategoriesIntegration extends CategoryTestCase
{
    public function testCommandRemoveOrphanCategories(): void
    {
        $parent = $this->createOrUpdateCategory(
            code: 'parent',
        );
        $child1 = $this->createOrUpdateCategory(
            code: 'child1',
            parentId: $parent->getId()->getValue(),
            rootId: $parent->getId()->getValue(),
        );
        $child2 = $this->createOrUpdateCategory(
            code: 'child2',
            parentId: $parent->getId()->getValue(),
            rootId: $parent->getId()->getValue(),
        );
        $child3 = $this->createOrUpdateCategory(
            code: 'child3',
            parentId: $parent->getId()->getValue(),
            rootId: $parent->getId()->getValue(),
        );

        $child4 = $this->createOrUpdateCategory(
            code: 'child4',
            parentId: $child3->getId()->getValue(),
            rootId: $parent->getId()->getValue(),
        );

        $this->createOrUpdateCategory(
            code: 'child5',
            parentId: $child4->getId()->getValue(),
            rootId: $parent->getId()->getValue(),
        );

        $output = $this->runRemoveOrphanCategoriesCommand();
        $this->assertStringContainsString('0 rows removed', $output);

        $this->assertNotNull($child3->getParentId());
        $this->setCategoryParentIdToNull('child3');
        $child3 = $this->get(GetCategoryInterface::class)->byCode('child3');
        $this->assertNull($child3->getParentId());

        $output = $this->runRemoveOrphanCategoriesCommand();
        $this->assertStringContainsString('3 rows removed', $output);

        $nonOrphanCategoriesGenerator = $this->get(GetCategoryInterface::class)->byCodes(['parent', 'child1', 'child2', 'child3', 'child4', 'child5']);
        $nonOrphanCategories= [];
        foreach ($nonOrphanCategoriesGenerator as $category) {
            $nonOrphanCategories[] = $category;
        }
        $expectedCategories = [
            $parent,
            $child1,
            $child2,
        ];

        $this->assertCount( 3, $nonOrphanCategories);
        $this->assertEqualsCanonicalizing($expectedCategories, $nonOrphanCategories);
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
        $this->get('database_connection')->executeQuery($sql, ['code' => $code]);
    }
}
