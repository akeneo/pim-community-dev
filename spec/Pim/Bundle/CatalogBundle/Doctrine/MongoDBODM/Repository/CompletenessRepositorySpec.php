<?php

namespace spec\Pim\Bundle\CatalogBundle\Doctrine\MongoDBODM\Repository;

use Doctrine\MongoDB\Cursor;
use Doctrine\MongoDB\Query\Query;
use Doctrine\ODM\MongoDB\DocumentManager;
use Doctrine\ODM\MongoDB\Query\Builder;
use Doctrine\ORM\QueryBuilder;
use PhpSpec\ObjectBehavior;
use Pim\Bundle\CatalogBundle\Doctrine\MongoDBODM\Repository\ProductRepository;
use Pim\Component\Catalog\Model\CategoryInterface;
use Pim\Component\Catalog\Model\ChannelInterface;
use Pim\Component\Catalog\Model\LocaleInterface;
use Akeneo\Component\Classification\Repository\CategoryRepositoryInterface;
use Pim\Component\Catalog\Repository\ChannelRepositoryInterface;
use Prophecy\Argument;

/**
 * @require Doctrine\ODM\MongoDB\DocumentManager
 */
class CompletenessRepositorySpec extends ObjectBehavior
{
    function let(
        DocumentManager $manager,
        ChannelInterface $ecommerce,
        ChannelInterface $mobile,
        LocaleInterface $enUs,
        LocaleInterface $frFr,
        CategoryInterface $category,
        ChannelRepositoryInterface $channelRepository,
        CategoryRepositoryInterface $categoryRepository,
        ProductRepository $productRepository,
        QueryBuilder $ormQb,
        Builder $odmQb,
        Query $odmQuery,
        Cursor $cursor
    ) {
        $enUs->getCode()->willReturn('en_US');
        $frFr->getCode()->willReturn('fr_FR');

        $ecommerce->getCode()->willReturn('ecommerce');
        $ecommerce->getLabel()->willReturn('ECommerce');
        $ecommerce->getLocales()->willReturn([$enUs, $frFr]);
        $ecommerce->getCategory()->willReturn($category);

        $mobile->getCode()->willReturn('mobile');
        $mobile->getLabel()->willReturn('Mobile');
        $mobile->getLocales()->willReturn([$enUs]);
        $mobile->getCategory()->willReturn($category);

        $odmQuery->execute()->willReturn($cursor);

        $productRepository->createQueryBuilder()->willReturn($odmQb);
        $odmQb->hydrate(Argument::any())->willReturn($odmQb);
        $odmQb->field(Argument::any())->willReturn($odmQb);
        $odmQb->in(Argument::any())->willReturn($odmQb);
        $odmQb->equals(Argument::any())->willReturn($odmQb);
        $odmQb->select('_id')->willReturn($odmQb);
        $odmQb->getQuery()->willReturn($odmQuery);

        $categoryRepository->getAllChildrenQueryBuilder($category, true)->willReturn($ormQb);
        $categoryRepository->getCategoryIds($category, $ormQb)->willReturn([1, 2, 3]);

        $channelRepository->findAll()->willReturn([$ecommerce, $mobile]);
        $manager->getRepository('pim_product_class')->willReturn($productRepository);

        $this->beConstructedWith($manager, $channelRepository, $categoryRepository, 'pim_product_class');
    }

    function it_is_a_completeness_repository()
    {
        $this->shouldImplement('Pim\Bundle\CatalogBundle\Repository\CompletenessRepositoryInterface');
    }

    function it_counts_products_per_channels(Cursor $cursor)
    {
        $countList = [3, 2];

        $cursor->count()->will(function () use (&$countList) {
            return array_shift($countList);
        });

        $this->getProductsCountPerChannels()->shouldReturn(
            [
                ['label' => 'ECommerce', 'total' => 3],
                ['label' => 'Mobile', 'total' => 2],
            ]
        );
    }

    function it_counts_complete_products_per_channels(Cursor $cursor)
    {
        $countList = [0, 1, 2];

        $cursor->count()->will(function () use (&$countList) {
            return array_shift($countList);
        });

        $this->getCompleteProductsCountPerChannels()->shouldReturn(
            [
                ['locale' => 'en_US', 'label' => 'ECommerce', 'total' => 0],
                ['locale' => 'fr_FR', 'label' => 'ECommerce', 'total' => 1],
                ['locale' => 'en_US', 'label' => 'Mobile', 'total' => 2],
            ]
        );
    }
}
