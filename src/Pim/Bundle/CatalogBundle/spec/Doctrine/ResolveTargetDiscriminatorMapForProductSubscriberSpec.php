<?php

namespace spec\Pim\Bundle\CatalogBundle\Doctrine;

use Doctrine\Common\Persistence\Event\LoadClassMetadataEventArgs;
use Doctrine\ORM\Mapping\ClassMetadata;
use PhpSpec\Exception\Example\NotEqualException;
use PhpSpec\ObjectBehavior;
use Pim\Component\Catalog\Model\AbstractProduct;

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
}
