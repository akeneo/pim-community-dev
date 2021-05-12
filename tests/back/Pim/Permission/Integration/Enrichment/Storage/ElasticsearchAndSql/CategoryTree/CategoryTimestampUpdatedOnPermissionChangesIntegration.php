<?php

declare(strict_types=1);

namespace AkeneoTestEnterprise\Pim\Permission\Integration\Enrichment\Storage\ElasticsearchAndSql\CategoryTree;

use Akeneo\Pim\Enrichment\Component\Category\Model\CategoryInterface;
use Akeneo\Pim\Enrichment\Component\Product\Repository\ProductCategoryRepositoryInterface;
use Akeneo\Pim\Permission\Bundle\Manager\CategoryAccessManager;
use Akeneo\Pim\Permission\Component\Attributes;
use Akeneo\Test\Integration\Configuration;
use Akeneo\Test\Integration\TestCase;
use Akeneo\UserManagement\Component\Repository\GroupRepositoryInterface;
use Doctrine\Persistence\ObjectManager;
use PHPUnit\Framework\Assert;

/**
 * @copyright 2021 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class CategoryTimestampUpdatedOnPermissionChangesIntegration extends TestCase
{
    private ProductCategoryRepositoryInterface $categoryRepository;
    private GroupRepositoryInterface $groupRepository;
    private CategoryAccessManager $categoryAccessManager;
    private ObjectManager $objectManager;

    protected function setUp(): void
    {
        parent::setUp();

        $this->createAdminUser();

        $this->categoryRepository = $this->get('pim_catalog.repository.product_category');
        $this->groupRepository = $this->get('pim_user.repository.group');
        $this->objectManager = $this->get('doctrine.orm.default_entity_manager');
        $this->categoryAccessManager = $this->get('pimee_security.manager.category_access');

        /** @var CategoryTreeFixturesLoaderWithPermission $fixturesLoader */
        $fixturesLoader = $this->get('akeneo_integration_tests.loader.category_tree_loader_with_permissions');

        $fixturesLoader->adminUserAsRedactorAndITSupport();
        $fixturesLoader->givenTheCategoryTreesWithoutViewPermission(
            [
                'tree_1' => [
                    'tree_1_child_1_level_1' => [],
                ],
            ]
        );
    }

    /**
     * @inheritDoc
     */
    protected function getConfiguration(): Configuration
    {
        return $this->catalog->useMinimalCatalog();
    }

    private function addPermissions(CategoryInterface $category)
    {
        $itSupportUserGroup = $this->groupRepository->findOneByIdentifier('IT support');
        $this->categoryAccessManager->revokeAccess($category);
        $this->objectManager->flush();
        $this->categoryAccessManager->grantAccess($category, $itSupportUserGroup, Attributes::VIEW_ITEMS);
    }

    private function removePermissions(CategoryInterface $category)
    {
        $this->categoryAccessManager->revokeAccess($category);
        $this->objectManager->flush();
    }


    public function test_timestamp_updated_when_category_permissions_have_been_added()
    {
        /** @var CategoryInterface $category */
        $category = $this->categoryRepository->findOneByIdentifier('tree_1');
        $categoryUpdatedAtBefore = $category->getUpdated();

        $this->addPermissions($category);
        $categoryUpdatedAtAfter = $category->getUpdated();

        Assert::assertGreaterThan($categoryUpdatedAtBefore, $categoryUpdatedAtAfter);
    }

    public function test_timestamp_updated_when_category_permissions_have_been_removed()
    {
        /** @var CategoryInterface $category */
        $category = $this->categoryRepository->findOneByIdentifier('tree_1');
        $this->addPermissions($category);

        $category = $this->categoryRepository->findOneByIdentifier('tree_1');
        $categoryUpdatedAtBefore = $category->getUpdated();

        $this->removePermissions($category);

        $category = $this->categoryRepository->findOneByIdentifier('tree_1');
        $categoryUpdatedAtAfter = $category->getUpdated();

        Assert::assertGreaterThan($categoryUpdatedAtBefore, $categoryUpdatedAtAfter);
    }
}
