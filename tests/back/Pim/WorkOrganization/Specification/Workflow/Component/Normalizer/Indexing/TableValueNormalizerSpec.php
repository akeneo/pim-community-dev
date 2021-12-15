<?php

namespace Specification\Akeneo\Pim\WorkOrganization\Workflow\Component\Normalizer\Indexing\PublishedProduct;

use Akeneo\Pim\TableAttribute\Domain\Value\Table;
use Akeneo\Pim\TableAttribute\Infrastructure\Value\TableValue;
use Akeneo\Pim\WorkOrganization\Workflow\Component\Normalizer\Indexing\TableValueNormalizer;
use PhpSpec\ObjectBehavior;
use Symfony\Component\Serializer\Normalizer\NormalizerInterface;

class TableValueNormalizerSpec extends ObjectBehavior
{
    function let(NormalizerInterface $baseTableNormalizer)
    {
        $this->beConstructedWith($baseTableNormalizer);
    }

    function it_is_initializable()
    {
        $this->shouldImplement(NormalizerInterface::class);
        $this->shouldHaveType(TableValueNormalizer::class);
    }

    function it_normalizes_table_values_for_published_products(TableValue $tableValue)
    {
        $tableValue->getAttributeCode()->willReturn('packaging');
        $tableValue->getScopeCode()->willReturn(null);
        $tableValue->getLocaleCode()->willReturn('en_US');
        $tableValue->getData()->willReturn(Table::fromNormalized([['parcel' => 'parcel1', 'width' => 95]]));

        $this->normalize($tableValue, 'indexing_product_and_product_model', ['is_workflow' => true])
             ->shouldReturn(
                 [
                     'packaging-table' => [
                         '<all_channels>' => [
                             'en_US' => 'not_empty',
                         ],
                     ],
                 ]
             );
    }

    function it_does_not_normalize_table_values_for_non_published_products(
        NormalizerInterface $baseTableNormalizer,
        TableValue $tableValue
    ) {
        $tableValue->getData()->willReturn(Table::fromNormalized([['parcel' => 'parcel1', 'width' => 95]]));
        $baseTableNormalizer->normalize($tableValue, 'indexing_product_and_product_model', [])
            ->shouldBeCalled()->willReturn([]);
        $this->normalize($tableValue, 'indexing_product_and_product_model')->shouldReturn([]);
    }
}
