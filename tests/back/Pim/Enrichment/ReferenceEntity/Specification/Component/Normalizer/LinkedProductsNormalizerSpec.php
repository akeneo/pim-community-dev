<?php

declare(strict_types=1);

namespace Specification\Akeneo\Pim\Enrichment\ReferenceEntity\Component\Normalizer;

use Akeneo\Pim\Enrichment\Component\Product\Grid\ReadModel\Row;
use Akeneo\Pim\Enrichment\Component\Product\Grid\ReadModel\Rows;
use Akeneo\Pim\Enrichment\Component\Product\Model\WriteValueCollection;
use Akeneo\Pim\Enrichment\Component\Product\Normalizer\InternalApi\ImageNormalizer;
use Akeneo\Pim\Enrichment\ReferenceEntity\Component\Normalizer\LinkedProductsNormalizer;
use PhpSpec\ObjectBehavior;


class LinkedProductsNormalizerSpec extends ObjectBehavior
{
    function let(ImageNormalizer $imageNormalizer)
    {
        $this->beConstructedWith($imageNormalizer);
    }

    function it_is_initializable()
    {
        $this->shouldBeAnInstanceOf(LinkedProductsNormalizer::class);
    }

    function it_normalizes_a_product_row(ImageNormalizer $imageNormalizer)
    {
        $technicalId = 10;
        $productIdentifier = 'identifier';
        $label = 'Product label';
        $localeCode = 'en_US';
        $channelCode = 'ecommerce';
        $image = null;
        $completeness = 100;
        $row = $this->productRow($technicalId, $productIdentifier, $label, $image, $completeness);

        $imageNormalizer->normalize($image, $localeCode, $channelCode)->willReturn(['image' => 'info']);

        $this->normalize(new Rows([$row], 15, 8, 7), $channelCode, $localeCode)->shouldReturn(
            [
                [
                    'id'                             => $technicalId,
                    'identifier'                     => $productIdentifier,
                    'label'                          => $label,
                    'document_type'                  => 'product',
                    'image'                          => ['image' => 'info'],
                    'completeness'                   => $completeness,
                    'variant_product_completenesses' => null
                ]
            ]
        );
    }

    function it_normalizes_a_product_model_row(ImageNormalizer $imageNormalizer)
    {
        $technicalId = 14;
        $productIdentifier = 'identifier';
        $label = 'Product label';
        $localeCode = 'en_US';
        $channelCode = 'ecommerce';
        $image = null;
        $childrenCompleteness = ['total' => 2, 'complete' => 1];
        $row = $this->productModelRow($technicalId, $productIdentifier, $label, $image, $childrenCompleteness);

        $imageNormalizer->normalize($image, $localeCode, $channelCode)->willReturn(['image' => 'info']);

        $this->normalize(new Rows([$row], 10, 6, 4), $channelCode, $localeCode)->shouldReturn(
            [
                [
                    'id'                             => $technicalId,
                    'identifier'                     => $productIdentifier,
                    'label'                          => $label,
                    'document_type'                  => 'product_model',
                    'image'                          => ['image' => 'info'],
                    'completeness'                   => null,
                    'variant_product_completenesses' => ['completeChildren' => 1, 'totalChildren' => 2]
                ]
            ]
        );
    }

    private function productRow(
        int $technicalId,
        string $productIdentifier,
        string $label,
        \StdClass $image,
        $completeness): Row
    {
        return Row::fromProduct(
            $productIdentifier,
            null,
            [],
            true,
            new \DateTime(),
            new \DateTime(),
            $label,
            $image,
            $completeness,
            $technicalId,
            '',
            new WriteValueCollection([])
        );
    }

    private function productModelRow(
        int $technicalId,
        string $productModelCode,
        string $label,
        \StdClass $image,
        array $childrenCompleteness): Row
    {
        return Row::fromProductModel(
            $productModelCode,
            'accessories',
            new \DateTime(),
            new \DateTime(),
            $label,
            $image,
            $technicalId,
            $childrenCompleteness,
            '',
            new WriteValueCollection([])
        );
    }
}
