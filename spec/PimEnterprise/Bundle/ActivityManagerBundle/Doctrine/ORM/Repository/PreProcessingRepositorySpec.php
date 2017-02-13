<?php

namespace spec\PimEnterprise\Bundle\ActivityManagerBundle\Doctrine\ORM\Repository;

use Akeneo\Component\Classification\Model\CategoryInterface;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\DBAL\Connection;
use Doctrine\ORM\EntityManager;
use PhpSpec\ObjectBehavior;
use Pim\Component\Catalog\Model\ChannelInterface;
use Pim\Component\Catalog\Model\LocaleInterface;
use Pim\Component\Catalog\Model\ProductInterface;
use PimEnterprise\Bundle\ActivityManagerBundle\Doctrine\ORM\TableNameMapper;
use PimEnterprise\Bundle\ActivityManagerBundle\Doctrine\ORM\Repository\PreProcessingRepository;
use PimEnterprise\Component\ActivityManager\Model\AttributeGroupCompleteness;
use PimEnterprise\Component\ActivityManager\Model\ProjectInterface;
use PimEnterprise\Component\ActivityManager\Repository\PreProcessingRepositoryInterface;

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

    function it_adds_the_pre_processed_completeness_for_product(
        $connection,
        $tableNameMapper,
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

        $tableNameMapper->getTableName('pimee_activity_manager.completeness_per_attribute_group')
            ->willReturn('pimee_activity_manager_completeness_per_attribute_group');

        $connection->delete(
            'pimee_activity_manager_completeness_per_attribute_group',
            [
                'product_id' => 42,
                'channel_id' => 13,
                'locale_id' => 37,
            ]
        )->shouldBeCalled();

        $connection->insert(
            'pimee_activity_manager_completeness_per_attribute_group',
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
            'pimee_activity_manager_completeness_per_attribute_group',
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
        $tableNameMapper,
        Connection $connection,
        ProjectInterface $project,
        ProductInterface $product
    ) {
        $project->getId()->willReturn(13);
        $product->getId()->willReturn(37);

        $entityManager->getConnection()->willReturn($connection);

        $tableNameMapper->getTableName('pimee_activity_manager.project_product')
            ->willReturn('pimee_activity_manager_project_product');

        $connection->insert('pimee_activity_manager_project_product', [
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

        $tableNameMapper->getTableName('pimee_activity_manager.project_product')
            ->willReturn('pimee_activity_manager_project_product');

        $connection->delete('pimee_activity_manager_project_product', [
            'project_id' => 40,
        ])->shouldBeCalled();

        $this->prepareProjectCalculation($project)->shouldReturn(null);
    }

    function it_links_between_product_and_category(
        $connection,
        $tableNameMapper,
        ProductInterface $product,
        CategoryInterface $category,
        CategoryInterface $otherCategory,
        ArrayCollection $categories,
        \Iterator $iterator
    ) {
        $categories->getIterator()->willReturn($iterator);
        $iterator->rewind()->shouldBeCalled();
        $iterator->valid()->willReturn(true, true, false);
        $iterator->current()->willReturn($category, $otherCategory);
        $iterator->next()->shouldBeCalled();

        $category->getId()->willReturn(40);
        $otherCategory->getId()->willReturn(33);
        $product->getId()->willReturn('fdsqf121s3s'); // mongo

        $tableNameMapper->getTableName('pimee_activity_manager.product_category')
            ->willReturn('pimee_activity_manager_product_category');

        $connection->delete('pimee_activity_manager_product_category', [
            'product_id' => 'fdsqf121s3s'
        ])->shouldBeCalled();

        $connection->insert('pimee_activity_manager_product_category', [
            'product_id' => 'fdsqf121s3s',
            'category_id' => 40,
        ])->shouldBeCalled();

        $connection->insert('pimee_activity_manager_product_category', [
            'product_id' => 'fdsqf121s3s',
            'category_id' => 33,
        ])->shouldBeCalled();

        $this->link($product, $categories)->shouldReturn(null);
    }
}
