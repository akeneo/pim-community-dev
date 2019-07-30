<?php

namespace Specification\Akeneo\Pim\WorkOrganization\TeamworkAssistant\Bundle\Doctrine\ORM\Repository;

use Doctrine\DBAL\Connection;
use Doctrine\ORM\EntityManager;
use PhpSpec\ObjectBehavior;
use Akeneo\Pim\Enrichment\Component\Product\Model\ProductInterface;
use Akeneo\Pim\WorkOrganization\TeamworkAssistant\Bundle\Doctrine\ORM\Repository\PreProcessingRepository;
use Akeneo\Pim\WorkOrganization\TeamworkAssistant\Bundle\Doctrine\ORM\TableNameMapper;
use Akeneo\Pim\WorkOrganization\TeamworkAssistant\Component\Model\ProjectInterface;
use Akeneo\Pim\WorkOrganization\TeamworkAssistant\Component\Repository\PreProcessingRepositoryInterface;

class PreProcessingRepositorySpec extends ObjectBehavior
{
    function let(EntityManager $entityManager, Connection $connection)
    {
        $this->beConstructedWith($entityManager);

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
        Connection $connection,
        ProjectInterface $project,
        ProductInterface $product
    ) {
        $project->getId()->willReturn(13);
        $product->getId()->willReturn(37);

        $entityManager->getConnection()->willReturn($connection);

        $connection->insert('pimee_teamwork_assistant_project_product', [
            'project_id' => 13,
            'product_id' => 37,
        ])->shouldBeCalled();

        $this->addProduct($project, $product);
    }

    function it_prepares_the_project_calculation_by_deleting_associated_products(
        $connection,
        ProjectInterface $project
    ) {
        $project->getId()->willReturn(40);

        $connection->delete('pimee_teamwork_assistant_project_product', [
            'project_id' => 40,
        ])->shouldBeCalled();

        $this->prepareProjectCalculation($project)->shouldReturn(null);
    }
}
