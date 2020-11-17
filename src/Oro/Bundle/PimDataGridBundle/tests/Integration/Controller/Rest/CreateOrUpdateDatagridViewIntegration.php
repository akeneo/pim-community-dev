<?php

declare(strict_types=1);

namespace Oro\Bundle\PimDataGridBundle\tests\Integration\Controller\Rest;

use Akeneo\Test\Integration\Configuration;
use Akeneo\Tool\Component\StorageUtils\Cache\EntityManagerClearerInterface;
use Akeneo\Tool\Component\StorageUtils\Saver\SaverInterface;
use Akeneo\UserManagement\Component\Model\User;
use Oro\Bundle\PimDataGridBundle\Entity\DatagridView;
use Oro\Bundle\PimDataGridBundle\Repository\DatagridViewRepositoryInterface;
use Oro\Bundle\PimDataGridBundle\tests\Integration\Controller\ControllerIntegrationTestCase;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

class CreateOrUpdateDatagridViewIntegration extends ControllerIntegrationTestCase
{
    private DatagridViewRepositoryInterface $datagridViewRepository;
    private SaverInterface $datagridViewSaver;
    private EntityManagerClearerInterface $entityManagerClearer;
    private User $loggedUser;
    private User $otherUser;

    public function setUp(): void
    {
        parent::setUp();

        $this->logIn('julia');
        $this->loggedUser = $this->get('pim_user.repository.user')->findOneByIdentifier('julia');
        $this->otherUser = $this->get('pim_user.repository.user')->findOneByIdentifier('mary');

        $this->datagridViewRepository = $this->get('pim_datagrid.repository.datagrid_view');
        $this->datagridViewSaver = $this->get('pim_datagrid.saver.datagrid_view');
        $this->entityManagerClearer = $this->get('pim_connector.doctrine.cache_clearer');
        $this->loadFixtures();
    }

    /** @test */
    public function it_creates_a_personal_view(): void
    {
        $response = $this->callSaveController('my_private_view', [
            'view' => [
                'label' => 'My private view',
                'type' => DatagridView::TYPE_PRIVATE,
                'columns' => 'identifier,created,updated,enabled',
                'filters' => 'i=1&p=25&s[updated]=1&f[scope][value]=ecommerce&f[category][value][treeId]=1&f[category][value][categoryId]=-2&f[category][type]=1&t=product-grid',
            ],
        ]);

        $this->assertStatusCode($response, Response::HTTP_OK);

        $datagridView = $this->findDatagridView('my_private_view');
        $this->assertNotNull($datagridView);
        $this->assertSame('my_private_view', $datagridView->getDatagridAlias());
        $this->assertSame($this->loggedUser->getUsername(), $datagridView->getOwner()->getUsername());
        $this->assertSame(DatagridView::TYPE_PRIVATE, $datagridView->getType());
    }

    /** @test */
    public function it_creates_a_public_view(): void
    {
        $response = $this->callSaveController('my_public_view', [
            'view' => [
                'label' => 'My public view',
                'type' => DatagridView::TYPE_PUBLIC,
                'columns' => 'identifier,created,updated,enabled',
                'filters' => 'i=1&p=25&s[updated]=1&f[scope][value]=ecommerce&f[category][value][treeId]=1&f[category][value][categoryId]=-2&f[category][type]=1&t=product-grid',
            ],
        ]);

        $this->assertStatusCode($response, Response::HTTP_OK);

        $datagridView = $this->findDatagridView('my_public_view');
        $this->assertNotNull($datagridView);
        $this->assertSame('my_public_view', $datagridView->getDatagridAlias());
        $this->assertSame($this->loggedUser->getUsername(), $datagridView->getOwner()->getUsername());
        $this->assertSame(DatagridView::TYPE_PUBLIC, $datagridView->getType());
    }

    /** @test */
    public function it_edits_an_existing_view(): void
    {
        $datagridView = $this->findDatagridView('view1');
        $this->assertNotNull($datagridView);

        $response = $this->callSaveController('whatever', [
            'view' => [
                'id' => $datagridView->getId(),
                'label' => 'Edited view',
                'type' => DatagridView::TYPE_PRIVATE,
                'columns' => 'identifier,created,updated,enabled',
                'filters' => 'i=1&p=25&s[updated]=1&f[scope][value]=ecommerce&f[category][value][treeId]=1&f[category][value][categoryId]=-2&f[category][type]=1&t=product-grid',
            ],
        ]);

        $this->assertStatusCode($response, Response::HTTP_OK);

        $this->entityManagerClearer->clear();
        $datagridView = $this->findDatagridView('view1');
        $this->assertNotNull($datagridView);
        $this->assertSame('view1', $datagridView->getDatagridAlias());
        $this->assertSame('Edited view', $datagridView->getLabel());
        $this->assertSame($this->loggedUser->getUsername(), $datagridView->getOwner()->getUsername());
        $this->assertSame(DatagridView::TYPE_PUBLIC, $datagridView->getType(), 'Type cannot be changed');
        $this->assertSame(['identifier', 'created', 'updated' , 'enabled'], $datagridView->getColumns());
    }

    /** @test */
    public function it_cannot_edit_an_unknown_view(): void
    {
        $response = $this->callSaveController('view1', [
            'view' => [
                'id' => -1,
                'label' => 'Edited view',
                'type' => DatagridView::TYPE_PRIVATE,
                'columns' => 'identifier,created,updated,enabled',
                'filters' => 'i=1&p=25&s[updated]=1&f[scope][value]=ecommerce&f[category][value][treeId]=1&f[category][value][categoryId]=-2&f[category][type]=1&t=product-grid',
            ],
        ]);

        $this->assertStatusCode($response, Response::HTTP_NOT_FOUND);
    }

    /** @test */
    public function it_cannot_edit_a_view_not_owned(): void
    {
        $datagridView = $this->findDatagridView('mary_view');
        $this->assertNotNull($datagridView);

        $response = $this->callSaveController('mary_view', [
            'view' => [
                'id' => $datagridView->getId(),
                'label' => 'Edited view',
                'type' => DatagridView::TYPE_PRIVATE,
                'columns' => 'identifier,created,updated,enabled',
                'filters' => 'i=1&p=25&s[updated]=1&f[scope][value]=ecommerce&f[category][value][treeId]=1&f[category][value][categoryId]=-2&f[category][type]=1&t=product-grid',
            ],
        ]);

        $this->assertStatusCode($response, Response::HTTP_FORBIDDEN);
    }

    private function callSaveController(string $alias, array $data): Response
    {
        $this->callApiRoute(
            $this->client,
            'pim_datagrid_view_rest_save',
            ['alias' => $alias],
            Request::METHOD_POST,
            [],
            \json_encode($data)
        );

        return $this->client->getResponse();
    }

    private function findDatagridView(string $alias): ?DatagridView
    {
        $datagridViews = $this->datagridViewRepository->findDatagridViewBySearch(
            $this->loggedUser,
            $alias
        );

        return $datagridViews[0] ?? null;
    }

    private function loadFixtures(): void
    {
        $datagridView = new DatagridView();
        $datagridView->setDatagridAlias('view1');
        $datagridView->setLabel('a view');
        $datagridView->setType(DatagridView::TYPE_PUBLIC);
        $datagridView->setOwner($this->loggedUser);
        $datagridView->setColumns(['identifier']);
        $datagridView->setFilters('filters');
        $this->datagridViewSaver->save($datagridView);

        $datagridView = new DatagridView();
        $datagridView->setDatagridAlias('mary_view');
        $datagridView->setLabel('Mary\'s view');
        $datagridView->setType(DatagridView::TYPE_PUBLIC);
        $datagridView->setOwner($this->otherUser);
        $datagridView->setColumns(['identifier']);
        $datagridView->setFilters('filters');
        $this->datagridViewSaver->save($datagridView);
    }

    public function getConfiguration(): Configuration
    {
        return $this->catalog->useTechnicalCatalog();
    }
}
