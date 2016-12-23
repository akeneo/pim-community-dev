<?php

namespace spec\PimEnterprise\Bundle\ActivityManagerBundle\Doctrine\ORM\Repository;

use Doctrine\DBAL\Connection;
use Doctrine\ORM\EntityManager;
use PhpSpec\ObjectBehavior;
use PimEnterprise\Bundle\ActivityManagerBundle\Doctrine\ORM\Repository\PreProcessingRepository;
use PimEnterprise\Component\ActivityManager\Repository\PreProcessingRepositoryInterface;

class PreProcessingRepositorySpec extends ObjectBehavior
{
    function let(EntityManager $entityManager, Connection $connection)
    {
        $this->beConstructedWith($entityManager);

        $entityManager->getConnection()->willReturn($connection);
    }

    function it_is_pre_pressing_repository()
    {
        $this->shouldImplement(PreProcessingRepositoryInterface::class);
    }

    function it_is_initializable()
    {
        $this->shouldHaveType(PreProcessingRepository::class);
    }

    function it_saves_the_pre_processed_completeness($connection)
    {
        $connection->delete(
            'akeneo_activity_manager_completeness_per_attribute_group',
            [
                'product_id' => 42,
            ]
        )->shouldBeCalled();

        $connection->insert(
            'akeneo_activity_manager_completeness_per_attribute_group',
            [
                'product_id' => 42,
                'channel_id' => 13,
                'locale_id' => 37,
                'attribute_group_id' => 40,
                'has_at_least_one_required_attribute_filled' => 0,
                'is_complete' => 1,
            ]
        )->shouldBeCalled();

        $connection->insert(
            'akeneo_activity_manager_completeness_per_attribute_group',
            [
                'product_id' => 42,
                'channel_id' => 13,
                'locale_id' => 37,
                'attribute_group_id' => 33,
                'has_at_least_one_required_attribute_filled' => 1,
                'is_complete' => 1,
            ]
        )->shouldBeCalled();

        $this->save(42, 13, 37,[
            [40,  0, 1],
            [33,  1, 1],
        ])->shouldReturn(null);
    }
}
