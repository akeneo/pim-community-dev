<?php

namespace spec\PimEnterprise\Bundle\CatalogBundle\Persistence;

use PhpSpec\ObjectBehavior;
use Prophecy\Argument;
use Doctrine\Common\Persistence\ObjectManager;
use Doctrine\ORM\EntityManager;
use Doctrine\ORM\UnitOfWork;
use Pim\Bundle\CatalogBundle\Model\ProductInterface;
use Pim\Bundle\CatalogBundle\Model\AbstractProductValue;
use Doctrine\ORM\Mapping\ClassMetadata;
use Doctrine\Common\Persistence\ManagerRegistry;
use PimEnterprise\Bundle\CatalogBundle\Persistence\Engine\ArrayDiff;
use Symfony\Component\Serializer\Normalizer\NormalizerInterface;


class ProductChangesProviderSpec extends ObjectBehavior
{
    function let(ManagerRegistry $registry, NormalizerInterface $normalizer, ArrayDiff $engine)
    {
        $this->beConstructedWith($registry, $normalizer, $engine);
    }

    function it_computes_new_values_through_the_normalized_values(
        $registry,
        $normalizer,
        $engine,
        ProductInterface $product,
        AbstractProductValue $sku,
        AbstractProductValue $name,
        AbstractProductValue $description,
        EntityManager $manager
    ) {
        $registry->getManagerForClass(get_class($product->getWrappedObject()))->willReturn($manager);

        $product->getValues()->willReturn([$sku, $name, $description]);

        $normalizer->normalize($product, 'csv')->willReturn(['current'], ['previous']);

        $manager->contains($sku)->willReturn(true); // FIXME Remove this when product values refreshing is working
        $manager->contains($name)->willReturn(true); // FIXME Remove this when product values refreshing is working
        $manager->contains($description)->willReturn(true); // FIXME Remove this when product values refreshing is working
        $manager->refresh($product)->shouldBeCalled();
        $manager->refresh($sku)->shouldBeCalled(); // FIXME Remove this when product values refreshing is working
        $manager->refresh($name)->shouldBeCalled(); // FIXME Remove this when product values refreshing is working
        $manager->refresh($description)->shouldBeCalled(); // FIXME Remove this when product values refreshing is working
        $sku->getData()->willReturn(null); // FIXME Remove this when product prices refreshing is working
        $name->getData()->willReturn(null); // FIXME Remove this when product prices refreshing is working
        $description->getData()->willReturn(null); // FIXME Remove this when product prices refreshing is working

        $engine->diff(['previous'], ['current'])->willReturn('differences');

        $this->computeChanges($product)->shouldReturn('differences');
    }
}
