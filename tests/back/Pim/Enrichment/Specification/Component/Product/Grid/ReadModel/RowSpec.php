<?php

declare(strict_types=1);

namespace Specification\Akeneo\Pim\Enrichment\Component\Product\Grid\ReadModel;

use Akeneo\Pim\Enrichment\Component\Product\Model\ValueCollection;
use Akeneo\Pim\Enrichment\Component\Product\Value\MediaValue;
use Akeneo\Pim\Enrichment\Component\Product\Value\ScalarValue;
use Akeneo\Pim\Structure\Component\Model\Attribute;
use Akeneo\Tool\Component\FileStorage\Model\FileInfo;
use PhpSpec\ObjectBehavior;

class RowSpec extends ObjectBehavior
{
    function it_creates_a_row_from_a_product()
    {
        $scalarAttribute = new Attribute();
        $scalarAttribute->setCode('scalar_attribute');

        $mediaAttribute = new Attribute();
        $mediaAttribute->setCode('media_attribute');

        $this->beConstructedThrough(
            'fromProduct',
            [
                'identifier',
                'family label',
                ['group_1', 'group_2'],
                true,
                new \DateTime('2018-05-23 15:55:50', new \DateTimeZone('UTC')),
                new \DateTime('2018-05-23 15:55:50', new \DateTimeZone('UTC')),
                'data',
                new MediaValue($mediaAttribute, null, null, new FileInfo()),
                90,
                1,
                'parent_code',
                new ValueCollection([new ScalarValue($scalarAttribute, null, null, 'data')])
            ]
        );

        $this->identifier()->shouldReturn('identifier');
        $this->family()->shouldReturn('family label');
        $this->groups()->shouldReturn(['group_1', 'group_2']);
        $this->enabled()->shouldReturn(true);
        $this->created()->shouldBeLike(new \DateTime('2018-05-23 15:55:50', new \DateTimeZone('UTC')));
        $this->updated()->shouldBeLike(new \DateTime('2018-05-23 15:55:50', new \DateTimeZone('UTC')));
        $this->label()->shouldReturn('data');
        $this->image()->shouldBeLike(new MediaValue($mediaAttribute, null, null, new FileInfo()));
        $this->completeness()->shouldReturn(90);
        $this->documentType()->shouldReturn('product');
        $this->technicalId()->shouldReturn(1);
        $this->searchId()->shouldReturn('product_1');
        $this->checked()->shouldReturn(true);
        $this->childrenCompleteness()->shouldReturn([]);
        $this->parent()->shouldReturn('parent_code');
        $this->values()->shouldBeLike(new ValueCollection([new ScalarValue($scalarAttribute, null, null, 'data')]));
    }

    function it_creates_a_row_from_a_product_model()
    {
        $scalarAttribute = new Attribute();
        $scalarAttribute->setCode('scalar_attribute');

        $mediaAttribute = new Attribute();
        $mediaAttribute->setCode('media_attribute');

        $this->beConstructedThrough(
            'fromProductModel',
            [
                'identifier',
                'family label',
                new \DateTime('2018-05-23 15:55:50', new \DateTimeZone('UTC')),
                new \DateTime('2018-05-23 15:55:50', new \DateTimeZone('UTC')),
                'data',
                new MediaValue($mediaAttribute, null, null, new FileInfo()),
                1,
                [],
                'parent_code',
                new ValueCollection([new ScalarValue($scalarAttribute, null, null, 'data')])
            ]
        );

        $this->identifier()->shouldReturn('identifier');
        $this->family()->shouldReturn('family label');
        $this->groups()->shouldReturn([]);
        $this->enabled()->shouldReturn(false);
        $this->created()->shouldBeLike(new \DateTime('2018-05-23 15:55:50', new \DateTimeZone('UTC')));
        $this->updated()->shouldBeLike(new \DateTime('2018-05-23 15:55:50', new \DateTimeZone('UTC')));
        $this->label()->shouldReturn('data');
        $this->image()->shouldBeLike(new MediaValue($mediaAttribute, null, null, new FileInfo()));
        $this->completeness()->shouldReturn(null);
        $this->documentType()->shouldReturn('product_model');
        $this->technicalId()->shouldReturn(1);
        $this->searchId()->shouldReturn('product_model_1');
        $this->checked()->shouldReturn(true);
        $this->childrenCompleteness()->shouldReturn([]);
        $this->parent()->shouldReturn('parent_code');
        $this->values()->shouldBeLike(new ValueCollection([new ScalarValue($scalarAttribute, null, null, 'data')]));
    }
}
