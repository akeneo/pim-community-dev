<?php

namespace spec\PimEnterprise\Bundle\CatalogBundle\Doctrine\ORM;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\EntityManagerInterface;
use PhpSpec\ObjectBehavior;
use Pim\Bundle\CatalogBundle\Model\ProductInterface;
use PimEnterprise\Component\ProductAsset\Model\AssetInterface;
use PimEnterprise\Component\ProductAsset\Repository\AssetRepositoryInterface;
use Prophecy\Argument;

class CompletenessGeneratorSpec extends ObjectBehavior
{
    public function let(
        EntityManagerInterface $manager,
        $productClass,
        $productValueClass,
        $attributeClass,
        AssetRepositoryInterface $assetRepository,
        $assetClass
    ) {
        $this->beConstructedWith(
            $manager,
            $productClass,
            $productValueClass,
            $attributeClass,
            $assetRepository,
            $assetClass
        );
    }

    public function it_is_an_enterpriseCompletenessGenerator()
    {
        $this->shouldImplement('PimEnterprise\Bundle\CatalogBundle\Doctrine\EnterpriseCompletenessGeneratorInterface');
        $this->shouldBeAnInstanceOf('Pim\Bundle\CatalogBundle\Doctrine\ORM\CompletenessGenerator');
    }

    public function it_can_schedule_completeness_for_an_asset(
        $assetRepository,
        AssetInterface $asset,
        ProductInterface $product1,
        ProductInterface $product2
    ) {
        $product1->getCompletenesses()->willReturn(new ArrayCollection());
        $product2->getCompletenesses()->willReturn(new ArrayCollection());
        $assetRepository->findProducts($asset)->willReturn([$product1, $product2]);
        $this->scheduleForAsset($asset);
    }
}
