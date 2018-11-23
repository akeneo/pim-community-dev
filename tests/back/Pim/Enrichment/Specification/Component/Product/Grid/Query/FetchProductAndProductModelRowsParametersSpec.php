<?php

declare(strict_types=1);

namespace Specification\Akeneo\Pim\Enrichment\Component\Product\Grid\Query;

use Akeneo\Pim\Enrichment\Component\Product\Model\ValueCollection;
use Akeneo\Pim\Enrichment\Component\Product\Query\ProductQueryBuilderInterface;
use Akeneo\Pim\Enrichment\Component\Product\Value\MediaValue;
use Akeneo\Pim\Enrichment\Component\Product\Value\ScalarValue;
use Akeneo\Pim\Structure\Component\Model\Attribute;
use Akeneo\Tool\Component\FileStorage\Model\FileInfo;
use PhpSpec\ObjectBehavior;

class FetchProductAndProductModelRowsParametersSpec extends ObjectBehavior
{
    function let(ProductQueryBuilderInterface $builder)
    {
        $this->beConstructedWith($builder, ['attribute_1'], 'channel_code', 'locale_code');
    }

    function it_has_the_attribute_codes()
    {
        $this->attributeCodes()->shouldReturn(['attribute_1']);
    }

    function it_has_the_channel_code()
    {
        $this->channelCode()->shouldReturn('channel_code');
    }

    function it_has_the_locale_code()
    {
        $this->localeCode()->shouldReturn('locale_code');
    }
}
