<?php

declare(strict_types=1);

/*
 * This file is part of the Akeneo PIM Enterprise Edition.
 *
 * (c) 2021 Akeneo SAS (https://www.akeneo.com)
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Specification\Akeneo\Platform\TailoredExport\Infrastructure\Connector\Processor\PropertySelector;

use Akeneo\Pim\Enrichment\Component\Product\Model\EntityWithFamilyVariantInterface;
use Akeneo\Pim\Enrichment\Component\Product\Model\ProductModelInterface;
use Akeneo\Pim\Enrichment\Component\Product\Query\GetProductModelLabelsInterface;
use PhpSpec\ObjectBehavior;

class ParentSelectorSpec extends ObjectBehavior
{
    public function let(GetProductModelLabelsInterface $getProductModelLabels)
    {
        $this->beConstructedWith($getProductModelLabels);
    }

    public function it_returns_property_name_supported()
    {
        $this->supports(['type' => 'code'], 'parent')->shouldReturn(true);
        $this->supports(['type' => 'code'], 'family')->shouldReturn(false);
        $this->supports(['type' => 'label'], 'parent')->shouldReturn(true);
        $this->supports(['type' => 'unknown'], 'parent')->shouldReturn(false);
    }

    public function it_selects_the_code(EntityWithFamilyVariantInterface $entity, ProductModelInterface $productModel)
    {
        $productModel->getCode()->willReturn('product_model');
        $entity->getParent()->willReturn($productModel);

        $this->applySelection(['type' => 'code'], $entity)->shouldReturn('product_model');
    }

    public function it_selects_the_label(
        GetProductModelLabelsInterface $getProductModelLabels,
        EntityWithFamilyVariantInterface $entity,
        ProductModelInterface $productModel
    ) {
        $productModel->getCode()->willReturn('product_model');
        $entity->getParent()->willReturn($productModel);

        $getProductModelLabels->byCodesAndLocaleAndScope(['product_model'], 'fr_FR', 'ecommerce')
            ->willReturn(['product_model' => 'My product model label']);

        $this->applySelection([
            'type' => 'label',
            'locale' => 'fr_FR',
            'channel' => 'ecommerce',
        ], $entity)->shouldReturn('My product model label');
    }

    public function it_fallbacks_on_the_code_when_not_translated(
        GetProductModelLabelsInterface $getProductModelLabels,
        EntityWithFamilyVariantInterface $entity,
        ProductModelInterface $productModel
    ) {
        $productModel->getCode()->willReturn('scanners');
        $entity->getParent()->willReturn($productModel);

        $getProductModelLabels->byCodesAndLocaleAndScope(['scanners'], 'fr_FR', 'ecommerce')
            ->willReturn([]);

        $this->applySelection([
            'type' => 'label',
            'locale' => 'fr_FR',
            'channel' => 'ecommerce',
        ], $entity)->shouldReturn('[scanners]');
    }
}
