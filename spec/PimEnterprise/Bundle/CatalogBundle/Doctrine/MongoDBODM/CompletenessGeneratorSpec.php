<?php

namespace spec\PimEnterprise\Bundle\CatalogBundle\Doctrine\MongoDBODM;

use Akeneo\Component\StorageUtils\Cursor\CursorInterface;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\MongoDB\Query\Builder;
use Doctrine\ODM\MongoDB\DocumentManager;
use Doctrine\ODM\MongoDB\Query\Query;
use Doctrine\ORM\EntityManagerInterface;
use PhpSpec\ObjectBehavior;
use Pim\Component\Catalog\Model\ProductInterface;
use Pim\Component\Catalog\Query\Filter\Operators;
use Pim\Component\Catalog\Query\ProductQueryBuilderFactoryInterface;
use Pim\Component\Catalog\Query\ProductQueryBuilderInterface;
use Pim\Component\Catalog\Repository\AttributeRepositoryInterface;
use Pim\Component\Catalog\Repository\ChannelRepositoryInterface;
use Pim\Component\Catalog\Repository\FamilyRepositoryInterface;
use PimEnterprise\Component\ProductAsset\Model\AssetInterface;
use PimEnterprise\Component\ProductAsset\Repository\AssetRepositoryInterface;
use Prophecy\Argument;

/**
 * @require Doctrine\ODM\MongoDB\DocumentManager
 */
class CompletenessGeneratorSpec extends ObjectBehavior
{
    public function let(
        DocumentManager $documentManager,
        ChannelRepositoryInterface $channelRepository,
        FamilyRepositoryInterface $familyRepository,
        AssetRepositoryInterface $assetRepository,
        AttributeRepositoryInterface $attributeRepository,
        EntityManagerInterface $manager,
        ProductQueryBuilderFactoryInterface $pqbFactory
    ) {
        $productClass = 'Pim\Component\Catalog\Model\ProductInterface';

        $this->beConstructedWith(
            $documentManager,
            $channelRepository,
            $familyRepository,
            $assetRepository,
            $attributeRepository,
            $manager,
            $productClass,
            $pqbFactory
        );
    }

    public function it_is_an_enterpriseCompletenessGenerator()
    {
        $this->shouldImplement('PimEnterprise\Bundle\CatalogBundle\Doctrine\CompletenessGeneratorInterface');
        $this->shouldBeAnInstanceOf('Pim\Bundle\CatalogBundle\Doctrine\MongoDBODM\CompletenessGenerator');
    }

    public function it_can_schedule_completeness_for_an_asset(
        $documentManager,
        $attributeRepository,
        $pqbFactory,
        ProductQueryBuilderInterface $pqb,
        CursorInterface $products,
        Builder $qb,
        Query $query,
        AssetInterface $asset,
        ProductInterface $product1,
        ProductInterface $product2
    ) {
        $documentManager->createQueryBuilder('Pim\Component\Catalog\Model\ProductInterface')
            ->willReturn($qb);

        $attributeRepository->getAttributeCodesByType('pim_assets_collection')->willReturn(['gallery', 'foobar']);

        $asset->getCode()->willReturn('my_asset');

        $pqbFactory->create()->willReturn($pqb);

        $pqb->addFilter('gallery', Operators::IN_LIST, ['my_asset'])->shouldBeCalled();
        $pqb->addFilter('foobar', Operators::IN_LIST, ['my_asset'])->shouldBeCalled();

        $pqb->execute()->willReturn($products);
        $products->count()->willReturn(2);

        $products->rewind()->shouldBeCalled();
        $products->valid()->willReturn(true, true, false);
        $products->current()->willReturn($product1, $product2);
        $products->next()->shouldBeCalled();

        $product1->getId()->willReturn('ID_1');
        $product2->getId()->willReturn('ID_2');

        $qb->update()->willReturn($qb);
        $qb->multiple(true)->willReturn($qb);

        $qb->expr()->willReturn($qb);
        $qb->addOr(\Prophecy\Argument::any())->willReturn($qb);

        $qb->field('id')->willReturn($qb);
        $qb->equals('ID_1')->willReturn($qb);
        $qb->field('id')->willReturn($qb);
        $qb->equals('ID_2')->willReturn($qb);

        $qb->field('completenesses')->willReturn($qb);
        $qb->unsetField()->willReturn($qb);
        $qb->field('normalizedData.completenesses')->willReturn($qb);
        $qb->unsetField()->willReturn($qb);
        $qb->getQuery()->willReturn($query);

        $query->execute()->shouldBeCalled();

        $product1->getCompletenesses()->willReturn(new ArrayCollection());
        $product2->getCompletenesses()->willReturn(new ArrayCollection());

        $this->scheduleForAsset($asset);
    }
}
