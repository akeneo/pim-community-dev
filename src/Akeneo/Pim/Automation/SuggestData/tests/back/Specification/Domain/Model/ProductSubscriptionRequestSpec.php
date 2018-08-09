<?php

declare(strict_types=1);

/*
 * This file is part of the Akeneo PIM Enterprise Edition.
 *
 * (c) 2018 Akeneo SAS (http://www.akeneo.com)
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Specification\Akeneo\Pim\Automation\SuggestData\Domain\Model;

use Akeneo\Pim\Automation\SuggestData\Domain\Model\IdentifiersMapping;
use Akeneo\Pim\Automation\SuggestData\Domain\Model\ProductSubscriptionRequest;
use Akeneo\Pim\Enrichment\Component\Product\Model\ProductInterface;
use Akeneo\Pim\Enrichment\Component\Product\Model\ValueInterface;
use Akeneo\Pim\Structure\Component\Model\AttributeInterface;
use PhpSpec\ObjectBehavior;

/**
 * @author Mathias METAYER <mathias.metayer@akeneo.com>
 */
class ProductSubscriptionRequestSpec extends ObjectBehavior
{
    public function let(ProductInterface $product)
    {
        $this->beConstructedWith($product);
    }

    public function it_is_a_product_subscription_request()
    {
        $this->shouldHaveType(ProductSubscriptionRequest::class);
    }

    public function it_does_not_take_missing_values_into_account(
        $product,
        AttributeInterface $manufacturer,
        AttributeInterface $model,
        AttributeInterface $ean,
        ValueInterface $modelValue,
        ValueInterface $eanValue
    ) {
        $manufacturer->getCode()->willReturn('manufacturer');
        $model->getCode()->willReturn('model');
        $ean->getCode()->willReturn('ean');

        $modelValue->hasData()->willReturn(false);
        $eanValue->hasData()->willReturn(true);
        $eanValue->__toString()->willReturn('123456789123');

        $product->getValue('manufacturer')->willReturn(null);
        $product->getValue('model')->willReturn($modelValue);
        $product->getValue('ean')->willReturn($eanValue);
        $product->getId()->willReturn(42);

        $this->getMappedValues(new IdentifiersMapping([
            'upc'   => $ean->getWrappedObject(),
            'brand' => $manufacturer->getWrappedObject(),
            'mpn'   => $model->getWrappedObject(),
        ]))->shouldReturn([
            'upc' => '123456789123',
        ]);
    }
}
