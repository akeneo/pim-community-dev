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
    private array $fixtureViewIds = [];

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
        $response = $this->callSaveController('product-grid', [
            'view' => [
                'label' => 'My private view',
                'type' => DatagridView::TYPE_PRIVATE,
                'columns' => 'identifier,created,updated,enabled',
                'filters' => 'i=1&p=25&s[updated]=1&f[scope][value]=ecommerce&f[category][value][treeId]=1&f[category][value][categoryId]=-2&f[category][type]=1&t=product-grid',
            ],
        ]);

        $this->assertStatusCode($response, Response::HTTP_OK);
        $id = \json_decode($response->getContent(), true)['id'] ?? null;

        $this->assertIsInt($id);
        $datagridView = $this->datagridViewRepository->find($id);
        $this->assertNotNull($datagridView);
        $this->assertSame('product-grid', $datagridView->getDatagridAlias());
        $this->assertSame($this->loggedUser->getUsername(), $datagridView->getOwner()->getUsername());
        $this->assertSame(DatagridView::TYPE_PRIVATE, $datagridView->getType());
    }

    /** @test */
    public function it_creates_a_public_view(): void
    {
        $response = $this->callSaveController('product-grid', [
            'view' => [
                'label' => 'My public view',
                'type' => DatagridView::TYPE_PUBLIC,
                'columns' => 'identifier,created,updated,enabled',
                'filters' => 'i=1&p=25&s[updated]=1&f[scope][value]=ecommerce&f[category][value][treeId]=1&f[category][value][categoryId]=-2&f[category][type]=1&t=product-grid',
            ],
        ]);

        $this->assertStatusCode($response, Response::HTTP_OK);
        $id = \json_decode($response->getContent(), true)['id'] ?? null;

        $this->assertIsInt($id);
        $datagridView = $this->datagridViewRepository->find($id);
        $this->assertNotNull($datagridView);
        $this->assertSame('product-grid', $datagridView->getDatagridAlias());
        $this->assertSame($this->loggedUser->getUsername(), $datagridView->getOwner()->getUsername());
        $this->assertSame(DatagridView::TYPE_PUBLIC, $datagridView->getType());
    }

    /** @test */
    public function it_edits_an_existing_public_view(): void
    {
        $datagridView = $this->datagridViewRepository->find($this->fixtureViewIds['admin_view']);
        $this->assertNotNull($datagridView);

        $response = $this->callSaveController('product-grid', [
            'view' => [
                'id' => $datagridView->getId(),
                'label' => 'Edited view',
                'type' => DatagridView::TYPE_PRIVATE,
                'columns' => 'identifier,created,updated,enabled',
                'filters' => 'i=1&p=25&s[updated]=1&f[scope][value]=ecommerce&f[category][value][treeId]=1&f[category][value][categoryId]=-2&f[category][type]=1&t=product-grid',
            ],
        ]);

        $this->assertStatusCode($response, Response::HTTP_OK);
        $id = \json_decode($response->getContent(), true)['id'] ?? null;

        $this->assertIsInt($id);
        $this->entityManagerClearer->clear();
        $datagridView = $this->datagridViewRepository->find($id);
        $this->assertNotNull($datagridView);
        $this->assertSame('product-grid', $datagridView->getDatagridAlias());
        $this->assertSame('Edited view', $datagridView->getLabel());
        $this->assertSame($this->loggedUser->getUsername(), $datagridView->getOwner()->getUsername());
        $this->assertSame(DatagridView::TYPE_PUBLIC, $datagridView->getType(), 'Type cannot be changed');
        $this->assertSame(['identifier', 'created', 'updated' , 'enabled'], $datagridView->getColumns());
    }

    /** @test */
    public function it_edits_an_existing_private_view(): void
    {
        $datagridView = $this->datagridViewRepository->find($this->fixtureViewIds['admin_private_view']);
        $this->assertNotNull($datagridView);

        $response = $this->callSaveController('product-grid', [
            'view' => [
                'id' => $datagridView->getId(),
                'label' => 'Edited private view',
                'type' => DatagridView::TYPE_PUBLIC,
                'columns' => 'identifier,created,updated,enabled',
                'filters' => 'i=1&p=25&s[updated]=1&f[scope][value]=ecommerce&f[category][value][treeId]=1&f[category][value][categoryId]=-2&f[category][type]=1&t=product-grid',
            ],
        ]);

        $this->assertStatusCode($response, Response::HTTP_OK);
        $id = \json_decode($response->getContent(), true)['id'] ?? null;

        $this->assertIsInt($id);
        $this->entityManagerClearer->clear();
        $datagridView = $this->datagridViewRepository->find($id);
        $this->assertNotNull($datagridView);
        $this->assertSame('product-grid', $datagridView->getDatagridAlias());
        $this->assertSame('Edited private view', $datagridView->getLabel());
        $this->assertSame($this->loggedUser->getUsername(), $datagridView->getOwner()->getUsername());
        $this->assertSame(DatagridView::TYPE_PRIVATE, $datagridView->getType(), 'Type cannot be changed');
        $this->assertSame(['identifier', 'created', 'updated' , 'enabled'], $datagridView->getColumns());
    }

    /** @test */
    public function it_cannot_edit_an_unknown_view(): void
    {
        $response = $this->callSaveController('product-grid', [
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
    public function it_cannot_edit_a_view_with_a_bad_datagrid_alias(): void
    {
        $datagridView = $this->datagridViewRepository->find($this->fixtureViewIds['admin_view']);
        $this->assertNotNull($datagridView);

        $response = $this->callSaveController('unknown', [
            'view' => [
                'id' => $datagridView->getId(),
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
        $datagridView = $this->datagridViewRepository->find($this->fixtureViewIds['mary_view']);
        $this->assertNotNull($datagridView);
        $response = $this->callSaveController('product-grid', [
            'view' => [
                'id' => $datagridView->getId(),
                'label' => 'Edited view',
                'type' => DatagridView::TYPE_PUBLIC,
                'columns' => 'identifier,created,updated,enabled',
                'filters' => 'i=1&p=25&s[updated]=1&f[scope][value]=ecommerce&f[category][value][treeId]=1&f[category][value][categoryId]=-2&f[category][type]=1&t=product-grid',
            ],
        ]);
        $this->assertStatusCode($response, Response::HTTP_FORBIDDEN);

        $datagridView = $this->datagridViewRepository->find($this->fixtureViewIds['mary_private_view']);
        $this->assertNotNull($datagridView);
        $response = $this->callSaveController('product-grid', [
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

    private function loadFixtures(): void
    {
        $datagridView = new DatagridView();
        $datagridView->setDatagridAlias('product-grid');
        $datagridView->setLabel('a view');
        $datagridView->setType(DatagridView::TYPE_PUBLIC);
        $datagridView->setOwner($this->loggedUser);
        $datagridView->setColumns(['identifier']);
        $datagridView->setFilters('filters');
        $this->datagridViewSaver->save($datagridView);
        $this->fixtureViewIds['admin_view'] = $datagridView->getId();

        $datagridView = new DatagridView();
        $datagridView->setDatagridAlias('product-grid');
        $datagridView->setLabel('a private view');
        $datagridView->setType(DatagridView::TYPE_PRIVATE);
        $datagridView->setOwner($this->loggedUser);
        $datagridView->setColumns(['identifier']);
        $datagridView->setFilters('filters');
        $this->datagridViewSaver->save($datagridView);
        $this->fixtureViewIds['admin_private_view'] = $datagridView->getId();

        $datagridView = new DatagridView();
        $datagridView->setDatagridAlias('product-grid');
        $datagridView->setLabel('Mary\'s view');
        $datagridView->setType(DatagridView::TYPE_PUBLIC);
        $datagridView->setOwner($this->otherUser);
        $datagridView->setColumns(['identifier']);
        $datagridView->setFilters('filters');
        $this->datagridViewSaver->save($datagridView);
        $this->fixtureViewIds['mary_view'] = $datagridView->getId();

        $datagridView = new DatagridView();
        $datagridView->setDatagridAlias('product-grid');
        $datagridView->setLabel('Mary\'s private view');
        $datagridView->setType(DatagridView::TYPE_PRIVATE);
        $datagridView->setOwner($this->otherUser);
        $datagridView->setColumns(['identifier']);
        $datagridView->setFilters('filters');
        $this->datagridViewSaver->save($datagridView);
        $this->fixtureViewIds['mary_private_view'] = $datagridView->getId();
    }

    public function getConfiguration(): Configuration
    {
        return $this->catalog->useTechnicalCatalog();
    }
}
