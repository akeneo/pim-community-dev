<?php

namespace spec\Akeneo\ActivityManager\Bundle\Repository\NativeSql;

use Akeneo\ActivityManager\Bundle\Repository\NativeSql\ProjectRepository;
use Akeneo\ActivityManager\Component\Model\ProjectInterface;
use Akeneo\ActivityManager\Component\Repository\ProjectRepositoryInterface;
use Doctrine\DBAL\Connection;
use Doctrine\ORM\EntityManagerInterface;
use PhpSpec\ObjectBehavior;
use Pim\Component\Catalog\Model\ProductInterface;

class ProjectRepositorySpec extends ObjectBehavior
{
    function let(EntityManagerInterface $objectManager, Connection $connection)
    {
        $objectManager->getConnection()->willReturn($connection);

        $this->beConstructedWith($objectManager);
    }

    function it_is_initializable()
    {
        $this->shouldHaveType(ProjectRepository::class);
    }

    function it_is_a_project_repository()
    {
        $this->shouldImplement(ProjectRepositoryInterface::class);
    }

    function it_adds_product_to_the_project($connection, ProjectInterface $project, ProductInterface $product)
    {
        $project->getId()->willReturn(42);
        $product->getId()->willReturn(1337);

        $connection->insert('akeneo_activity_manager_project_user_group', ['project_id' => 42, 'product_id' => 1337]);

        $this->addProduct($project, $product);
    }
}
