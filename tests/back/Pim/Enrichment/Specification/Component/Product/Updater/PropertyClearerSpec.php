<?php

declare(strict_types=1);

namespace Specification\Akeneo\Pim\Enrichment\Component\Product\Updater;

use Akeneo\Pim\Enrichment\Component\Product\Model\Product;
use Akeneo\Pim\Enrichment\Component\Product\Updater\Clearer\ClearerInterface;
use Akeneo\Pim\Enrichment\Component\Product\Updater\Clearer\ClearerRegistryInterface;
use Akeneo\Pim\Enrichment\Component\Product\Updater\PropertyClearer;
use Akeneo\Tool\Component\StorageUtils\Exception\UnknownPropertyException;
use Akeneo\Tool\Component\StorageUtils\Updater\PropertyClearerInterface;
use PhpSpec\ObjectBehavior;

/**
 * @copyright 2020 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class PropertyClearerSpec extends ObjectBehavior
{
    function let(ClearerRegistryInterface $clearerRegistry)
    {
        $this->beConstructedWith($clearerRegistry);
    }

    function it_is_initializable()
    {
        $this->shouldBeAnInstanceOf(PropertyClearer::class);
    }

    function it_is_a_property_clearer()
    {
        $this->shouldImplement(PropertyClearerInterface::class);
    }

    function it_clears_a_product_property(
        ClearerRegistryInterface $clearerRegistry,
        ClearerInterface $clearer
    ) {
        $product = new Product();
        $clearerRegistry->getClearer('title')->willReturn($clearer);

        $clearer->clear($product, 'title', ['locale' => 'en_US', 'scope' => 'ecommerce'])
            ->shouldBeCalled();

        $this->clear($product, 'title', ['locale' => 'en_US', 'scope' => 'ecommerce']);
    }

    function it_fails_when_no_clearer_is_found(ClearerRegistryInterface $clearerRegistry)
    {
        $product = new Product();
        $clearerRegistry->getClearer('unknown')->willReturn(null);

        $this->shouldThrow(UnknownPropertyException::class)
            ->during('clear', [$product, 'unknown', ['option1' => 'value1']]);
    }
}
