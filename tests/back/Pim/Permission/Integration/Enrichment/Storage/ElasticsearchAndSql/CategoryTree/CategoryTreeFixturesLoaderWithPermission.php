<?php

declare(strict_types=1);

namespace AkeneoTest\Pim\Permission\Integration\Enrichment\Storage\ElasticsearchAndSql\CategoryTree;

use Akeneo\Pim\Permission\Component\Attributes;
use PHPUnit\Framework\Assert;
use Psr\Container\ContainerInterface;

class CategoryTreeFixturesLoaderWithPermission
{
    /** @var  ContainerInterface */
    private $container;

    /**
     * @param ContainerInterface $container
     */
    public function __construct(ContainerInterface $container)
    {
        $this->container = $container;
    }

    /**
     * @param array       $categories
     * @param null|string $parentCode
     */
    public function givenTheCategoryTreesWithoutViewPermission(array $categories, ?string $parentCode = null): void
    {
        $accessManager = $this->container->get('pimee_security.manager.category_access');
        $entityManager = $this->container->get('doctrine.orm.default_entity_manager');
        $groupRepository = $this->container->get('pim_user.repository.group');

        $userGroup = $groupRepository->findOneByIdentifier('Manager');

        foreach ($categories as $categoryCode => $children) {
            $category = $this->container->get('pim_catalog.factory.category')->create();
            $this->container->get('pim_catalog.updater.category')->update($category, [
                'code' => $categoryCode,
                'parent' => $parentCode ?? null,
                'labels' => ['en_US' => ucfirst($categoryCode)]
            ]);
            Assert::assertEquals(0, $this->container->get('validator')->validate($category)->count());
            $this->container->get('pim_catalog.saver.category')->save($category);

            $accessManager->revokeAccess($category);
            $entityManager->flush($category);
            $accessManager->grantAccess($category, $userGroup, Attributes::VIEW_ITEMS);

            $this->givenTheCategoryTreesWithoutViewPermission($children, $categoryCode);
        }
    }

    /**
     * @param array $products
     */
    public function givenTheProductsWithCategories(array $products): void
    {
        foreach ($products as $identifier => $categories) {
            $product = $this->container->get('pim_catalog.builder.product')->createProduct($identifier);
            $this->container->get('pim_catalog.updater.product')->update($product, [
                'categories' => $categories
            ]);
            $constraintList = $this->container->get('pim_catalog.validator.product')->validate($product);
            Assert::assertEquals(0, $constraintList->count());
            $this->container->get('pim_catalog.saver.product')->save($product);
        }

        $this->container->get('akeneo_elasticsearch.client.product')->refreshIndex();
    }

    /**
     * @param array $categoryCodes
     */
    public function givenTheViewableCategories(array $categoryCodes): void
    {
        $accessManager = $this->container->get('pimee_security.manager.category_access');
        $entityManager = $this->container->get('doctrine')->getEntityManager();
        $groupRepository = $this->container->get('pim_user.repository.group');
        $categoryRepository = $this->container->get('pim_catalog.repository.product_category');

        foreach ($categoryCodes as $categoryCode) {
            $category = $categoryRepository->findOneByIdentifier($categoryCode);
            $userGroup = $groupRepository->findOneByIdentifier('IT support');
            $accessManager->revokeAccess($category);
            $entityManager->flush($category);
            $accessManager->grantAccess($category, $userGroup, Attributes::VIEW_ITEMS);
        }
    }
}
