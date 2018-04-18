<?php

namespace spec\Pim\Bundle\CatalogBundle\Doctrine;

use Doctrine\Common\Persistence\Event\LoadClassMetadataEventArgs;
use Doctrine\ORM\Mapping\ClassMetadata;
use PhpSpec\ObjectBehavior;
use Pim\Component\Catalog\Model\AbstractProduct;
use Pim\Component\Catalog\Model\Product;
use Pim\Component\Catalog\Model\VariantProduct;
use Prophecy\Argument;

class ResolveTargetDiscriminatorMapForProductSubscriberSpec extends ObjectBehavior
{
    function let()
    {
        $this->beConstructedWith('foo', 'bar');
    }

    function it_resolves_the_discriminator_map_of_the_abstract_product_class(
        LoadClassMetadataEventArgs $args,
        ClassMetadata $classMetadata
    ) {
        $args->getClassMetadata()->willReturn($classMetadata);
        $classMetadata->getName()->willReturn(AbstractProduct::class);
        $classMetadata->setDiscriminatorMap(['product' => 'foo', 'variant_product' => 'bar'])->shouldBeCalled();

        $this->loadClassMetadata($args);
    }

    function it_adds_the_discriminator_value_on_the_product_class(
        LoadClassMetadataEventArgs $args,
        ClassMetadata $classMetadata
    ) {
        $args->getClassMetadata()->willReturn($classMetadata);
        $classMetadata->getName()->willReturn(Product::class);
        $classMetadata->setDiscriminatorMap(Argument::any())->shouldNotBeCalled();

        $this->loadClassMetadata($args);
    }

    function it_adds_the_discriminator_value_on_the_variant_product_class(
        LoadClassMetadataEventArgs $args,
        ClassMetadata $classMetadata
    ) {
        $args->getClassMetadata()->willReturn($classMetadata);
        $classMetadata->getName()->willReturn(VariantProduct::class);
        $classMetadata->setDiscriminatorMap(Argument::any())->shouldNotBeCalled();

        $this->loadClassMetadata($args);
    }
}
