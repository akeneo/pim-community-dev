<?php

namespace spec\PimEnterprise\Component\ProductAsset\Upload;

use PhpSpec\ObjectBehavior;
use Pim\Component\Catalog\Model\LocaleInterface;
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

    function it_cleans_filename_with_invalid_characters(LocaleInterface $locale)
    {
        $this->beConstructedWith([$locale], 'f+oo-bar.test.jpg');

        $this->getAssetCode()->shouldReturn('f_oo_bar_test');
        $this->getLocaleCode()->shouldReturn(null);
        $this->getCleanFilename()->shouldReturn('f_oo_bar_test.jpg');
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
