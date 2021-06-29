<?php

declare(strict_types=1);

namespace Oro\Bundle\PimDataGridBundle\tests\Integration\Repository;

use Akeneo\Test\Integration\TestCase;
use Akeneo\UserManagement\Component\Model\UserInterface;
use Akeneo\UserManagement\Component\Repository\UserRepositoryInterface;
use Oro\Bundle\PimDataGridBundle\Entity\DatagridView;
use Oro\Bundle\PimDataGridBundle\Repository\DatagridViewRepositoryInterface;
use PHPUnit\Framework\Assert;

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
        Assert::assertIsArray($result);
        Assert::assertNotEmpty($result);
        Assert::assertInstanceOf(DatagridView::class, $result[0]);
        Assert::assertSame(
            [$view1Id, $view2Id, $view3Id, $view4Id, $view6Id],
            array_map(fn ($view) => $view->getId(), $result)
        );
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
        Assert::assertSame(
            [$view1Id, $view2Id],
            array_map(fn ($view) => $view->getId(), $result)
        );
    }

    public function test_it_returns_view_aliases_for_a_given_user(): void
    {
        $adminUser = $this->userRepository->findOneBy(['username' => 'admin']);
        $juliaUser = $this->userRepository->findOneBy(['username' => 'julia']);

        $this->createDatagridView('view 1', 'product-grid', DatagridView::TYPE_PRIVATE, $adminUser)->getId();
        $this->createDatagridView('view 3', 'other-grid', DatagridView::TYPE_PRIVATE, $adminUser)->getId();
        $this->createDatagridView('view 5', 'product-grid', DatagridView::TYPE_PRIVATE, $adminUser)->getId();
        $aliases = $this->datagridViewRepository->getDatagridViewAliasesByUser($adminUser);
        Assert::assertSame(['product-grid', 'other-grid'], $aliases);

        $aliases = $this->datagridViewRepository->getDatagridViewAliasesByUser($juliaUser);
        Assert::assertSame([], $aliases);

        $this->createDatagridView('view 2', 'product-grid', DatagridView::TYPE_PUBLIC, $adminUser)->getId();
        $this->createDatagridView('view 4', 'another-grid', DatagridView::TYPE_PUBLIC, $adminUser)->getId();
        $aliases = $this->datagridViewRepository->getDatagridViewAliasesByUser($juliaUser);
        Assert::assertSame(['product-grid', 'another-grid'], $aliases);
    }

    public function test_it_finds_public_view_by_label(): void
    {
        $julia = $this->userRepository->findOneBy(['username' => 'julia']);

        $publicDatagridView = $this->createDatagridView('view 1', 'product-grid-public', DatagridView::TYPE_PUBLIC, $julia);
        $privateDatagridView = $this->createDatagridView('view 1', 'product-grid-private', DatagridView::TYPE_PRIVATE, $julia);
        $foundDatagridView = $this->datagridViewRepository->findPublicDatagridViewByLabel('view 1');

        Assert::assertSame($publicDatagridView, $foundDatagridView);
    }

    public function test_it_finds_private_view_by_label(): void
    {
        $adminUser = $this->userRepository->findOneBy(['username' => 'admin']);

        $datagridView1 = $this->createDatagridView('view 1', 'product-grid', DatagridView::TYPE_PRIVATE, $adminUser);
        $datagridView2 = $this->createDatagridView('view 2', 'other-grid', DatagridView::TYPE_PRIVATE, $adminUser);
        $foundDatagridView = $this->datagridViewRepository->findPrivateDatagridViewByLabel('view 2', $adminUser);

        Assert::assertSame($datagridView2, $foundDatagridView);
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
