<?php

namespace spec\PimEnterprise\Bundle\ActivityManagerBundle\Doctrine\ORM\Repository;

use Doctrine\DBAL\Connection;
use Doctrine\ORM\EntityManager;
use PhpSpec\ObjectBehavior;
use Pim\Component\Catalog\Model\ChannelInterface;
use Pim\Component\Catalog\Model\LocaleInterface;
use Pim\Component\Catalog\Model\ProductInterface;
use PimEnterprise\Bundle\ActivityManagerBundle\Doctrine\ORM\Repository\PreProcessingRepository;
use PimEnterprise\Component\ActivityManager\Model\AttributeGroupCompleteness;
use PimEnterprise\Component\ActivityManager\Model\ProjectInterface;
use PimEnterprise\Component\ActivityManager\Repository\PreProcessingRepositoryInterface;
use Prophecy\Argument;

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

    function it_adds_the_pre_processed_completeness_for_product(
        $connection,
        ProductInterface $product,
        ProjectInterface $project,
        ChannelInterface $channel,
        LocaleInterface $locale
    ) {
        $project->getChannel()->willreturn($channel);
        $channel->getId()->willreturn(13);

        $project->getLocale()->willreturn($locale);
        $locale->getId()->willreturn(37);

        $product->getId()->willreturn(42);

        $connection->executeQuery(
            Argument::type('string'),
            [
                'product_id' => 42,
                'channel_id' => 13,
                'locale_id' => 37,
                'attribute_group_id' => 40,
                'has_at_least_one_required_attribute_filled' => 0,
                'is_complete' => 1,
            ]
        )->shouldBeCalled();

        $connection->executeQuery(
            Argument::type('string'),
            [
                'product_id' => 42,
                'channel_id' => 13,
                'locale_id' => 37,
                'attribute_group_id' => 33,
                'has_at_least_one_required_attribute_filled' => 1,
                'is_complete' => 1,
            ]
        )->shouldBeCalled();

        $this->addAttributeGroupCompleteness($product, $project,[
            new AttributeGroupCompleteness(40,  0, 1),
            new AttributeGroupCompleteness(33,  1, 1),
        ])->shouldReturn(null);
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

        $connection->insert('pimee_activity_manager_project_product', [
            'project_id' => 13,
            'product_id' => 37,
        ])->shouldBeCalled();

        $this->addProduct($project, $product);
    }

    function it_prepare_the_project_calculation($connection, ProjectInterface $project)
    {
        $project->getId()->willReturn(40);

        $connection->delete('pimee_activity_manager_project_product', [
            'project_id' => 40,
        ])->shouldBeCalled();

        $this->prepareProjectCalculation($project)->shouldReturn(null);
    }
}
