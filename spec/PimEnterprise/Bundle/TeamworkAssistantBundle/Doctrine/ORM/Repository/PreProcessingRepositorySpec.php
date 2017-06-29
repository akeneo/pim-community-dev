<?php

namespace spec\PimEnterprise\Bundle\TeamworkAssistantBundle\Doctrine\ORM\Repository;

use Akeneo\Component\Classification\Model\CategoryInterface;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\DBAL\Connection;
use Doctrine\ORM\EntityManager;
use PhpSpec\ObjectBehavior;
use Pim\Component\Catalog\Model\ProductInterface;
use PimEnterprise\Bundle\TeamworkAssistantBundle\Doctrine\ORM\Repository\PreProcessingRepository;
use PimEnterprise\Bundle\TeamworkAssistantBundle\Doctrine\ORM\TableNameMapper;
use PimEnterprise\Component\TeamworkAssistant\Model\ProjectInterface;
use PimEnterprise\Component\TeamworkAssistant\Repository\PreProcessingRepositoryInterface;

class PreProcessingRepositorySpec extends ObjectBehavior
{
    function let(EntityManager $entityManager, TableNameMapper $tableNameMapper, Connection $connection)
    {
        $this->beConstructedWith($entityManager, $tableNameMapper);

        $entityManager->getConnection()->willReturn($connection);
    }

    function it_is_pre_processing_repository()
    {
        $this->shouldImplement(PreProcessingRepositoryInterface::class);
    }

    function it_is_initializable()
    {
        $this->shouldHaveType(PreProcessingRepository::class);
    }

    function it_adds_products_to_a_project(
        $entityManager,
        $tableNameMapper,
        Connection $connection,
        ProjectInterface $project,
        ProductInterface $product
    ) {
        $project->getId()->willReturn(13);
        $product->getId()->willReturn(37);

        $entityManager->getConnection()->willReturn($connection);

        $tableNameMapper->getTableName('pimee_teamwork_assistant.project_product')
            ->willReturn('pimee_teamwork_assistant_project_product');

        $connection->insert('pimee_teamwork_assistant_project_product', [
            'project_id' => 13,
            'product_id' => 37,
        ])->shouldBeCalled();

        $this->addProduct($project, $product);
    }

    function it_prepares_the_project_calculation_by_deleting_associated_products(
        $connection,
        $tableNameMapper,
        ProjectInterface $project
    ) {
        $project->getId()->willReturn(40);

        $tableNameMapper->getTableName('pimee_teamwork_assistant.project_product')
            ->willReturn('pimee_teamwork_assistant_project_product');

        $connection->delete('pimee_teamwork_assistant_project_product', [
            'project_id' => 40,
        ])->shouldBeCalled();

        $this->prepareProjectCalculation($project)->shouldReturn(null);
    }
}
