<?php

namespace spec\Pim\Bundle\BaseConnectorBundle\Reader;

use PhpSpec\ObjectBehavior;
use Pim\Bundle\CatalogBundle\Model\ProductInterface;

class CachedReaderSpec extends ObjectBehavior
{
    function it_is_an_item_reader()
    {
        $this->shouldImplement('\Akeneo\Bundle\BatchBundle\Item\ItemReaderInterface');
    }

    function it_provides_configuration_fields()
    {
        $this->getConfigurationFields()->shouldReturn([]);
    }

    function it_adds_and_provides_items_from_their_index(ProductInterface $mugProduct, ProductInterface $shirtProduct)
    {
        $this->getItem('mug')->shouldReturn(null);
        $this->getItem('shirt')->shouldReturn(null);

        $this->addItem([$mugProduct], 'mug');
        $this->addItem([$shirtProduct], 'shirt');

        $this->getItem('mug')->shouldReturn([$mugProduct]);
        $this->getItem('shirt')->shouldReturn([$shirtProduct]);
    }

    function it_adds_and_provides_items(ProductInterface $mugProduct, ProductInterface $shirtProduct)
    {
        $this->getItem(0)->shouldReturn(null);
        $this->getItem(1)->shouldReturn(null);

        $this->addItem([$mugProduct]);
        $this->addItem([$shirtProduct]);

        $this->getItem(0)->shouldReturn([$mugProduct]);
        $this->getItem(1)->shouldReturn([$shirtProduct]);
    }

    function it_reads_cached_items_one_by_one_and_reindex_them(
        ProductInterface $mugProduct,
        ProductInterface $shirtProduct
    ) {
        $this->addItem([$mugProduct]);
        $this->addItem([$shirtProduct]);

        $this->getItem(0)->shouldReturn([$mugProduct]);
        $this->read()->shouldReturn([$mugProduct]);

        $this->getItem(0)->shouldReturn([$shirtProduct]);
        $this->read()->shouldReturn([$shirtProduct]);

        $this->getItem(0)->shouldReturn(null);
    }
}
