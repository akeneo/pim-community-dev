<?php

namespace spec\Pim\Bundle\CatalogBundle\Manager;

use Akeneo\Component\StorageUtils\Saver\BulkSaverInterface;
use PhpSpec\ObjectBehavior;
use Pim\Bundle\CatalogBundle\AttributeType\AttributeTypeRegistry;
use Pim\Bundle\CatalogBundle\Factory\AttributeFactory;
use Pim\Component\Catalog\Model\AttributeInterface;
use Pim\Component\Catalog\Repository\AttributeRepositoryInterface;

class AttributeManagerSpec extends ObjectBehavior
{
    const ATTRIBUTE_CLASS = 'Pim\Bundle\CatalogBundle\Entity\Attribute';
    const PRODUCT_CLASS   = 'Pim\Component\Catalog\Model\Product';

    function let(
        AttributeTypeRegistry $registry,
        BulkSaverInterface $optionSaver,
        AttributeRepositoryInterface $repository,
        AttributeFactory $factory
    ) {
        $this->beConstructedWith(
            self::ATTRIBUTE_CLASS,
            $registry,
            $optionSaver,
            $repository,
            $factory
        );
    }

    function it_provides_the_list_of_attribute_types($registry)
    {
        $registry->getAliases()->willReturn(['foo', 'bar']);

        $this->getAttributeTypes()->shouldReturn(['bar' => 'bar', 'foo' => 'foo']);
    }
}
