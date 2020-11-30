<?php

declare(strict_types=1);

namespace Oro\Bundle\PimDataGridBundle\tests\Integration\Repository;

use Akeneo\Test\Integration\TestCase;
use Akeneo\UserManagement\Component\Model\UserInterface;
use Akeneo\UserManagement\Component\Repository\UserRepositoryInterface;
use Doctrine\Common\Collections\Collection;
use Oro\Bundle\PimDataGridBundle\Entity\DatagridView;
use Oro\Bundle\PimDataGridBundle\Repository\DatagridViewRepositoryInterface;
use Webmozart\Assert\Assert;

/**
 * @author Pierre Allard <pierre.allard@akeneo.com>
 */
class DatagridViewRepositoryIntegration extends TestCase
{
    private DatagridViewRepositoryInterface $datagridViewRepository;
    private UserRepositoryInterface $userRepository;

    protected function setUp(): void
    {
        parent::setUp();
        $this->datagridViewRepository = $this->get('pim_datagrid.repository.datagrid_view');
        $this->userRepository = $this->get('pim_user.repository.user');
    }

    /**
     * {@inheritdoc}
     */
    protected function getConfiguration()
    {
        return $this->catalog->useTechnicalCatalog();
    }

    public function test_that_it_returns_all_views(): void
    {
        $view1Id = $this->createDatagridView('view 1', 'product-grid', DatagridView::TYPE_PUBLIC)->getId();
        $view2Id = $this->createDatagridView('view 2', 'product-grid', DatagridView::TYPE_PUBLIC)->getId();
        $view3Id = $this->createDatagridView('view 3', 'product-grid', DatagridView::TYPE_PUBLIC)->getId();
        $adminUser = $this->userRepository->findOneBy(['username' => 'admin']);
        $view4Id = $this->createDatagridView('view 4', 'product-grid', DatagridView::TYPE_PRIVATE, $adminUser)->getId();
        $juliaUser = $this->userRepository->findOneBy(['username' => 'julia']);
        $this->createDatagridView('view 5', 'product-grid', DatagridView::TYPE_PRIVATE, $juliaUser)->getId();
        $view6Id = $this->createDatagridView('view 6', 'product-grid', DatagridView::TYPE_PUBLIC, $juliaUser)->getId();

        $result = $this->datagridViewRepository->findDatagridViewBySearch($adminUser, 'product-grid');
        Assert::isArray($result);
        Assert::notEmpty($result);
        Assert::isInstanceOf($result[0], DatagridView::class);
        Assert::same(array_map(function ($view) {
            return $view->getId();
        }, $result), [$view1Id, $view2Id, $view3Id, $view4Id, $view6Id]);
    }

    public function test_that_it_filters_by_ids(): void
    {
        $view1Id = $this->createDatagridView('view 1', 'product-grid', DatagridView::TYPE_PUBLIC)->getId();
        $view2Id = $this->createDatagridView('view 2', 'product-grid', DatagridView::TYPE_PUBLIC)->getId();
        $this->createDatagridView('view 3', 'product-grid', DatagridView::TYPE_PUBLIC);
        $user = $this->userRepository->findOneBy(['username' => 'admin']);

        $result = $this->datagridViewRepository->findDatagridViewBySearch($user, 'product-grid', '', [
            'identifiers' => [$view1Id, $view2Id]
        ]);
        Assert::same(array_map(function ($view) {
            return $view->getId();
        }, $result), [$view1Id, $view2Id]);
    }

    public function test_it_returns_view_aliases_for_a_given_user(): void
    {
        $adminUser = $this->userRepository->findOneBy(['username' => 'admin']);
        $juliaUser = $this->userRepository->findOneBy(['username' => 'julia']);

        $this->createDatagridView('view 1', 'product-grid', DatagridView::TYPE_PRIVATE, $adminUser)->getId();
        $this->createDatagridView('view 3', 'other-grid', DatagridView::TYPE_PRIVATE, $adminUser)->getId();
        $aliases = $this->datagridViewRepository->getDatagridViewAliasesByUser($adminUser);
        Assert::same($aliases, ['product-grid', 'other-grid']);

        $aliases = $this->datagridViewRepository->getDatagridViewAliasesByUser($juliaUser);
        Assert::same($aliases, []);

        $this->createDatagridView('view 2', 'product-grid', DatagridView::TYPE_PUBLIC, $adminUser)->getId();
        $this->createDatagridView('view 4', 'another-grid', DatagridView::TYPE_PUBLIC, $adminUser)->getId();
        $aliases = $this->datagridViewRepository->getDatagridViewAliasesByUser($juliaUser);
        Assert::same($aliases, ['product-grid', 'another-grid']);
    }

    private function createDatagridView(
        string $label,
        string $alias,
        string $type,
        ?UserInterface $user = null
    ): DatagridView {
        $view = new DatagridView();
        $view->setLabel($label);
        $view->setDatagridAlias($alias);
        $view->setColumns(['created_at']);
        $view->setType($type);
        if ($user) {
            $view->setOwner($user);
        }

        static::assertCount(0, $this->get('validator')->validate($view));
        $this->get('pim_datagrid.saver.datagrid_view')->save($view);

        return $view;
    }
}
