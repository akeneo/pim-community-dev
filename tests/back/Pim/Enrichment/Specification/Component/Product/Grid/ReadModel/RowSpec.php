<?php

declare(strict_types=1);

namespace Specification\Akeneo\Pim\Enrichment\Component\Product\Grid\ReadModel;

use Akeneo\Pim\Enrichment\Component\Product\Grid\ReadModel\AdditionalProperties;
use Akeneo\Pim\Enrichment\Component\Product\Grid\ReadModel\AdditionalProperty;
use Akeneo\Pim\Enrichment\Component\Product\Grid\ReadModel\Row;
use Akeneo\Pim\Enrichment\Component\Product\Model\WriteValueCollection;
use Akeneo\Pim\Enrichment\Component\Product\Value\MediaValue;
use Akeneo\Pim\Enrichment\Component\Product\Value\ScalarValue;
use Akeneo\Tool\Component\FileStorage\Model\FileInfo;
use PhpSpec\ObjectBehavior;

class RowSpec extends ObjectBehavior
{
    function it_is_initializable()
    {
        $this->shouldHaveType(Row::class);
    }

    function it_creates_a_row_from_a_product()
    {
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
                MediaValue::value('media_attribute', new FileInfo()),
                90,
                1,
                'parent_code',
                new WriteValueCollection([ScalarValue::value('scalar_attribute', 'data')])
            ]
        );

        $this->identifier()->shouldReturn('identifier');
        $this->familyCode()->shouldReturn('family label');
        $this->groupCodes()->shouldReturn(['group_1', 'group_2']);
        $this->enabled()->shouldReturn(true);
        $this->created()->shouldBeLike(new \DateTime('2018-05-23 15:55:50', new \DateTimeZone('UTC')));
        $this->updated()->shouldBeLike(new \DateTime('2018-05-23 15:55:50', new \DateTimeZone('UTC')));
        $this->label()->shouldReturn('data');
        $this->image()->shouldBeLike(MediaValue::value('media_attribute', new FileInfo()));
        $this->completeness()->shouldReturn(90);
        $this->documentType()->shouldReturn('product');
        $this->technicalId()->shouldReturn(1);
        $this->searchId()->shouldReturn('product_1');
        $this->checked()->shouldReturn(true);
        $this->childrenCompleteness()->shouldReturn([]);
        $this->parentCode()->shouldReturn('parent_code');
        $this->values()->shouldBeLike(new WriteValueCollection([ScalarValue::value('scalar_attribute', 'data')]));
        $this->additionalProperties()->shouldBeLike(new AdditionalProperties([]));
    }

    function it_creates_a_row_from_a_product_model()
    {
        $this->beConstructedThrough(
            'fromProductModel',
            [
                'identifier',
                'family label',
                new \DateTime('2018-05-23 15:55:50', new \DateTimeZone('UTC')),
                new \DateTime('2018-05-23 15:55:50', new \DateTimeZone('UTC')),
                'data',
                MediaValue::value('media_attribute', new FileInfo()),
                1,
                [],
                'parent_code',
                new WriteValueCollection([ScalarValue::value('scalar_attribute', 'data')])
            ]
        );

        $this->identifier()->shouldReturn('identifier');
        $this->familyCode()->shouldReturn('family label');
        $this->groupCodes()->shouldReturn([]);
        $this->enabled()->shouldReturn(false);
        $this->created()->shouldBeLike(new \DateTime('2018-05-23 15:55:50', new \DateTimeZone('UTC')));
        $this->updated()->shouldBeLike(new \DateTime('2018-05-23 15:55:50', new \DateTimeZone('UTC')));
        $this->label()->shouldReturn('data');
        $this->image()->shouldBeLike(MediaValue::value('media_attribute', new FileInfo()));
        $this->completeness()->shouldReturn(null);
        $this->documentType()->shouldReturn('product_model');
        $this->technicalId()->shouldReturn(1);
        $this->searchId()->shouldReturn('product_model_1');
        $this->checked()->shouldReturn(true);
        $this->childrenCompleteness()->shouldReturn([]);
        $this->parentCode()->shouldReturn('parent_code');
        $this->values()->shouldBeLike(new WriteValueCollection([ScalarValue::value('scalar_attribute', 'data')]));
        $this->additionalProperties()->shouldBeLike(new AdditionalProperties([]));
    }

    function it_adds_an_additional_property()
    {
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
                MediaValue::value('media_attribute', new FileInfo()),
                90,
                1,
                'parent_code',
                new WriteValueCollection([ScalarValue::value('scalar_attribute', 'data')])
            ]
        );

        $row = $this->addAdditionalProperty(new AdditionalProperty('name', 'value'));
        $row->additionalProperties()->shouldBeLike(new AdditionalProperties([
            new AdditionalProperty('name', 'value')
        ]));
    }
}
