<?php

declare(strict_types=1);

namespace Pim\Component\Catalog\tests\integration\Category;

use PHPUnit\Framework\Assert;
use Psr\Container\ContainerInterface;

class CategoryTreeFixturesLoader
{
    /** @var  ContainerInterface */
    private $container;

    public function __construct(ContainerInterface $container)
    {
        $this->container = $container;
    }

    public function givenTheCategoryTrees(array $categories, ?string $parentCode = null): void
    {
        foreach ($categories as $categoryCode => $children) {
            $category = $this->container->get('pim_catalog.factory.category')->create();
            $this->container->get('pim_catalog.updater.category')->update($category, [
                'code' => $categoryCode,
                'parent' => $parentCode ?? null,
                'labels' => ['en_US' => ucfirst($categoryCode)]
            ]);
            Assert::assertEquals(0, $this->container->get('validator')->validate($category)->count());
            $this->container->get('pim_catalog.saver.category')->save($category);

            $this->givenTheCategoryTrees($children, $categoryCode);
        }
    }
}
