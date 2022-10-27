<?php

namespace AkeneoTestEnterprise\Pim\WorkOrganization\Integration\Persistence\Sql;

use Akeneo\Pim\WorkOrganization\TeamworkAssistant\Bundle\Persistence\Query\GetProjectCode;
use Akeneo\Pim\WorkOrganization\TeamworkAssistant\Component\Model\Project;
use Akeneo\Test\Integration\Configuration;
use Akeneo\Test\Integration\TestCase;
use Akeneo\UserManagement\Component\Model\UserInterface;
use Oro\Bundle\PimDataGridBundle\Entity\DatagridView;
use PHPUnit\Framework\Assert;

class GetProjectCodeIntegration extends TestCase
{
    /**
     * @test
     */
    public function it_returns_all_projects_codes()
    {
        $query = $this->getQuery();
        $codes = $query->fetchAll();

        $expected = [
            'project1-ecommerce-fr-fr',
            'project2-tablet-fr-fr',
            'project3-ecommerce-fr-fr',
        ];
        Assert::assertSame($expected, $codes);
    }

    protected function setUp(): void
    {
        parent::setUp();

        $this->createProject('project1', 'ecommerce');
        $this->createProject('project2', 'tablet');
        $this->createProject('project3', 'ecommerce');
    }

    protected function getConfiguration(): Configuration
    {
        return $this->catalog->useTechnicalCatalog(featureFlags: ['permission']);
    }

    private function getQuery(): GetProjectCode
    {
        return $this->get(GetProjectCode::class);
    }

    private function createProject(string $code, string $channel): Project
    {
        $user = $this->get('pim_user.repository.user')->findOneByIdentifier('mary');

        $locale = $this->get('pim_catalog.repository.locale')->findOneByIdentifier('fr_FR');
        $view = $this->createDatagridView('view 1', 'product-grid', DatagridView::TYPE_PUBLIC, $user);
        $channel = $this->get('pim_catalog.repository.channel')->findOneBy(['code' => $channel]);

        $project = new Project();
        $project->setLabel($code);
        $project->setOwner($user);
        $project->setLocale($locale);
        $project->setChannel($channel);
        $project->setDatagridView($view);
        $project->setDueDate(new \DateTime());
        $this->get('pimee_teamwork_assistant.saver.project')->save($project);

        return $project;
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

        Assert::assertCount(0, $this->get('validator')->validate($view));
        $this->get('pim_datagrid.saver.datagrid_view')->save($view);

        return $view;
    }
}
