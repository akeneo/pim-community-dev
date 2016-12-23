<?php

namespace spec\PimEnterprise\Bundle\ActivityManagerBundle\Doctrine\ORM\Repository;

use Doctrine\DBAL\Connection;
use Pim\Component\Catalog\Model\ProductInterface;
use PimEnterprise\Bundle\ActivityManagerBundle\Doctrine\ORM\Repository\ProjectRepository;
use Akeneo\Component\StorageUtils\Repository\IdentifiableObjectRepositoryInterface;
use Akeneo\Component\StorageUtils\Repository\SearchableRepositoryInterface;
use Doctrine\ORM\EntityManager;
use Doctrine\ORM\Mapping\ClassMetadata;
use PhpSpec\ObjectBehavior;
use PimEnterprise\Component\ActivityManager\Model\ProjectInterface;

class ProjectRepositorySpec extends ObjectBehavior
{
    function let(EntityManager $entityManager, ClassMetadata $classMetadata)
    {
        $entityManager->getClassMetadata('Project')->willReturn($classMetadata);

        $this->beConstructedWith($entityManager, 'Project');
    }

    function it_is_initializable()
    {
        $this->shouldHaveType(ProjectRepository::class);
    }

    function it_is_an_object_identifiable_repository()
    {
        $this->shouldImplement(IdentifiableObjectRepositoryInterface::class);
    }

    function it_is_a_searchable_repository()
    {
        $this->shouldImplement(SearchableRepositoryInterface::class);
    }

    function its_identifier_is_id()
    {
        $this->getIdentifierProperties()->shouldReturn(['code']);
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

        $connection->delete('akeneo_activity_manager_project_product', [
            'product_id' => 37,
        ])->shouldBeCalled();

        $connection->insert('akeneo_activity_manager_project_product', [
            'project_id' => 13,
            'product_id' => 37,
        ])->shouldBeCalled();

        $this->addProduct($project, $product);
    }
}
