<?php

namespace spec\PimEnterprise\Component\ProductAsset\Upload;

use PhpSpec\ObjectBehavior;
use Pim\Bundle\CatalogBundle\Model\LocaleInterface;
use Prophecy\Argument;

class ParsedFilenameSpec extends ObjectBehavior
{
    function let(
        LocaleInterface $locale
    ) {
        $this->beConstructedWith([$locale], 'foobar.jpg');
    }

    function it_can_be_initialized()
    {
        $this->shouldHaveType('PimEnterprise\Component\ProductAsset\Upload\ParsedFilename');
        $this->shouldImplement('PimEnterprise\Component\ProductAsset\Upload\ParsedFilenameInterface');
    }

    function it_do_not_parse_invalid_format(LocaleInterface $locale)
    {
        $this->beConstructedWith([$locale], 'f+oo-bar.jpg');

        $this->getAssetCode()->shouldReturn(null);
        $this->getLocaleCode()->shouldReturn(null);
        $this->getCleanFilename()->shouldReturn(null);
    }

    function it_parses_a_simple_non_localizable_filename()
    {
        $this->getAssetCode()->shouldReturn('foobar');
        $this->getLocaleCode()->shouldReturn(null);
        $this->getExtension()->shouldReturn('jpg');
        $this->getCleanFilename()->shouldReturn('foobar.jpg');
    }

    function it_parses_a_complex_non_localizable_filename(LocaleInterface $locale)
    {
        $this->beConstructedWith([$locale], 'foo-bar.jpg');

        $this->getAssetCode()->shouldReturn('foo_bar');
        $this->getLocaleCode()->shouldReturn(null);
        $this->getExtension()->shouldReturn('jpg');
        $this->getCleanFilename()->shouldReturn('foo_bar.jpg');
    }

    function it_parses_a_localizable_filename(LocaleInterface $locale)
    {
        $locale->getCode()->willReturn('en_US');

        $this->beConstructedWith([$locale], 'foo-bar-en_US.jpg');

        $this->getAssetCode()->shouldReturn('foo_bar');
        $this->getLocaleCode()->shouldReturn('en_US');
        $this->getExtension()->shouldReturn('jpg');
        $this->getCleanFilename()->shouldReturn('foo_bar-en_US.jpg');
    }

    function it_do_not_parse_unknown_locale(LocaleInterface $locale)
    {
        $locale->getCode()->willReturn('fr_FR');

        $this->beConstructedWith([$locale], 'foo-bar-en_US.jpg');

        $this->getAssetCode()->shouldReturn('foo_bar_en_US');
        $this->getLocaleCode()->shouldReturn(null);
    }
}
