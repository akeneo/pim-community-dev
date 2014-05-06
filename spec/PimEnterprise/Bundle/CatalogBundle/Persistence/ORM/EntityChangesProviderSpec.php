<?php

namespace spec\PimEnterprise\Bundle\CatalogBundle\Persistence\ORM;

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


class EntityChangesProviderSpec extends ObjectBehavior
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

        $normalizer->normalize($sku, 'json', Argument::any())->willReturn(['value' => 'foo'], ['value' => 'foo']);
        $sku->getId()->willReturn(1);
        $sku->getData()->willReturn(null); // FIXME Remove this when product prices refreshing is working

        $normalizer->normalize($name, 'json', Argument::any())->willReturn(['value' => 'bar'], ['value' => 'baz']);
        $name->getId()->willReturn(2);
        $name->getData()->willReturn(null); // FIXME Remove this when product prices refreshing is working

        $normalizer->normalize($description, 'json', Argument::any())->willReturn(['value' => ''], ['value' => 'desc']);
        $description->getId()->willReturn(3);
        $description->getData()->willReturn(null); // FIXME Remove this when product prices refreshing is working

        $manager->refresh($sku)->shouldBeCalled();
        $manager->refresh($name)->shouldBeCalled();
        $manager->refresh($description)->shouldBeCalled();

        $engine->diff(
            [
                1 => ['value' => 'foo'],
                2 => ['value' => 'baz'],
                3 => ['value' => 'desc'],
            ],
            [
                1 => ['value' => 'foo'],
                2 => ['value' => 'bar'],
                3 => ['value' => ''],
            ]
        )->willReturn('differences');

        $this->computeNewValues($product)->shouldReturn('differences');
    }
}
