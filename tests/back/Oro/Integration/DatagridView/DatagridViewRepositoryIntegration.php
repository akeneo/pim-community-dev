<?php

declare(strict_types=1);

namespace AkeneoTest\Oro\Integration\DatagridView;

use Akeneo\Test\Integration\TestCase;
use Akeneo\UserManagement\Component\Repository\UserRepositoryInterface;
use Oro\Bundle\PimDataGridBundle\Entity\DatagridView;
use Oro\Bundle\PimDataGridBundle\Repository\DatagridViewRepositoryInterface;
use Webmozart\Assert\Assert;

/**
 * @author Pierre Allard <pierre.allard@akeneo.com>
 */
class DatagridViewRepositoryIntegration extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();
        $this->createAdminUser();
    }

    /**
     * {@inheritdoc}
     */
    protected function getConfiguration()
    {
        return $this->catalog->useMinimalCatalog();
    }

    public function test_that_it_returns_all_views(): void
    {
        $view1Id = $this->createDatagridView('view 1', 'product-grid')->getId();
        $view2Id = $this->createDatagridView('view 2', 'product-grid')->getId();
        $view3Id = $this->createDatagridView('view 3', 'product-grid')->getId();
        $user = $this->getUserRepository()->findOneBy(['username' => 'admin']);
        $result = $this->getDatagridViewRepository()->findDatagridViewBySearch($user, 'product-grid');
        $expected = [$view1Id, $view2Id, $view3Id];
        Assert::same(array_map(function ($view) {
            return $view->getId();
        }, $result), $expected);
    }

    public function test_that_it_filters_by_ids(): void
    {
        $view1Id = $this->createDatagridView('view 1', 'product-grid')->getId();
        $view2Id = $this->createDatagridView('view 2', 'product-grid')->getId();
        $this->createDatagridView('view 3', 'product-grid');
        $user = $this->getUserRepository()->findOneBy(['username' => 'admin']);
        $result = $this->getDatagridViewRepository()->findDatagridViewBySearch($user, 'product-grid', '', [
            'identifiers' => [$view1Id, $view2Id]
        ]);
        $expected = [$view1Id, $view2Id];
        Assert::same(array_map(function ($view) {
            return $view->getId();
        }, $result), $expected);
    }

    private function getDatagridViewRepository(): DatagridViewRepositoryInterface
    {
        return $this->get('pim_datagrid.repository.datagrid_view');
    }

    private function getUserRepository(): UserRepositoryInterface
    {
        return $this->get('pim_user.repository.user');
    }

    private function createDatagridView(string $label, string $alias): DatagridView
    {
        $view = new DatagridView();
        $view->setLabel($label);
        $view->setDatagridAlias($alias);
        $view->setColumns(['created_at']);

        static::assertCount(0, $this->get('validator')->validate($view));
        $this->get('pim_datagrid.saver.datagrid_view')->save($view);

        return $view;
    }
}
