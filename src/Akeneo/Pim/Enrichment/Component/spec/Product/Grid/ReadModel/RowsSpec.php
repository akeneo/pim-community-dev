<?php

declare(strict_types=1);

namespace spec\Akeneo\Pim\Enrichment\Component\Product\Grid\ReadModel;

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
        $scalarAttribute = new Attribute();
        $scalarAttribute->setCode('scalar_attribute');

        $mediaAttribute = new Attribute();
        $mediaAttribute->setCode('media_attribute');

        $row = Row::fromProduct(
            'identifier',
            'family label',
            ['group_1', 'group_2'],
            true,
            new \DateTime('2018-05-23 15:55:50', new \DateTimeZone('UTC')),
            new \DateTime('2018-05-23 15:55:50', new \DateTimeZone('UTC')),
            new ScalarValue($scalarAttribute, null, null, 'data'),
            new MediaValue($mediaAttribute, null, null, new FileInfo()),
            90,
            1,
            'parent_code',
            new ValueCollection([new ScalarValue($scalarAttribute, null, null, 'data')])
        );

        $this->beConstructedWith([$row], 100);
    }

    function it_has_the_total_number_of_returned_elements()
    {
        $this->totalCount()->shouldReturn(100);
    }

    function it_has_the_product_and_product_model_rows()
    {
        $scalarAttribute = new Attribute();
        $scalarAttribute->setCode('scalar_attribute');

        $mediaAttribute = new Attribute();
        $mediaAttribute->setCode('media_attribute');

        $row = Row::fromProduct(
            'identifier',
            'family label',
            ['group_1', 'group_2'],
            true,
            new \DateTime('2018-05-23 15:55:50', new \DateTimeZone('UTC')),
            new \DateTime('2018-05-23 15:55:50', new \DateTimeZone('UTC')),
            new ScalarValue($scalarAttribute, null, null, 'data'),
            new MediaValue($mediaAttribute, null, null, new FileInfo()),
            90,
            1,
            'parent_code',
            new ValueCollection([new ScalarValue($scalarAttribute, null, null, 'data')])
        );

        $this->rows()->shouldBeLike([$row]);
    }
}
