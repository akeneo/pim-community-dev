<?php

declare(strict_types=1);

namespace Specification\Akeneo\Pim\Enrichment\Component\Product\Grid\ReadModel;

use Akeneo\Pim\Enrichment\Component\Product\Grid\ReadModel\Row;
use Akeneo\Pim\Enrichment\Component\Product\Model\ValueCollection;
use Akeneo\Pim\Enrichment\Component\Product\Value\MediaValue;
use Akeneo\Pim\Enrichment\Component\Product\Value\ScalarValue;
use Akeneo\Pim\Structure\Component\Model\Attribute;
use Akeneo\Tool\Component\FileStorage\Model\FileInfo;
use PhpSpec\ObjectBehavior;

class RowsSpec extends ObjectBehavior
{
    function let()
    {
        $row = Row::fromProduct(
            'identifier',
            'family label',
            ['group_1', 'group_2'],
            true,
            new \DateTime('2018-05-23 15:55:50', new \DateTimeZone('UTC')),
            new \DateTime('2018-05-23 15:55:50', new \DateTimeZone('UTC')),
            'data',
            MediaValue::value('media_attribute', new FileInfo()),
            90,
            1,
            'parent_code',
            new ValueCollection([ScalarValue::value('scalar_attribute', 'data')])
        );

        $this->beConstructedWith([$row], 100);
    }

    function it_has_the_total_number_of_returned_elements()
    {
        $this->totalCount()->shouldReturn(100);
    }

    function it_has_the_product_and_product_model_rows()
    {
        $row = Row::fromProduct(
            'identifier',
            'family label',
            ['group_1', 'group_2'],
            true,
            new \DateTime('2018-05-23 15:55:50', new \DateTimeZone('UTC')),
            new \DateTime('2018-05-23 15:55:50', new \DateTimeZone('UTC')),
            'data',
            MediaValue::value('media_attribute', new FileInfo()),
            90,
            1,
            'parent_code',
            new ValueCollection([ScalarValue::value('scalar_attribute', 'data')])
        );

        $this->rows()->shouldBeLike([$row]);
    }
}
