<?php

namespace Specification\Akeneo\Pim\Enrichment\Component\Product\Value;

use Akeneo\Pim\Enrichment\Component\Product\Value\MediaValueInterface;
use Akeneo\Pim\Enrichment\Component\Product\Value\MetricValueInterface;
use Akeneo\Tool\Component\FileStorage\Model\FileInfoInterface;
use PhpSpec\ObjectBehavior;

class MediaValueSpec extends ObjectBehavior
{
    function it_returns_data(FileInfoInterface $fileInfo)
    {
        $this->beConstructedThrough('scopableLocalizableValue', ['my_media', $fileInfo, 'ecommerce', 'en_US']);

        $this->getData()->shouldBeAnInstanceOf(FileInfoInterface::class);
        $this->getData()->shouldReturn($fileInfo);
    }

    function it_compares_itself_to_a_same_media_value(
        FileInfoInterface $fileInfo,
        MediaValueInterface $sameMediaValue,
        FileInfoInterface $sameFileInfo
    ) {
        $this->beConstructedThrough('scopableLocalizableValue', ['my_media', $fileInfo, 'ecommerce', 'en_US']);

        $sameMediaValue->getScopeCode()->willReturn('ecommerce');
        $sameMediaValue->getLocaleCode()->willReturn('en_US');
        $sameMediaValue->getData()->willReturn($sameFileInfo);

        $sameFileInfo->getOriginalFilename()->willReturn('myfile');
        $fileInfo->getOriginalFilename()->willReturn('myfile');

        $sameFileInfo->getMimeType()->willReturn('image/jpeg');
        $fileInfo->getMimeType()->willReturn('image/jpeg');

        $sameFileInfo->getSize()->willReturn(123);
        $fileInfo->getSize()->willReturn(123);

        $sameFileInfo->getExtension()->willReturn('jpg');
        $fileInfo->getExtension()->willReturn('jpg');

        $sameFileInfo->getHash()->willReturn('123456stvcqjd');
        $fileInfo->getHash()->willReturn('123456stvcqjd');

        $sameFileInfo->getKey()->willReturn('key');
        $fileInfo->getKey()->willReturn('key');

        $sameFileInfo->getStorage()->willReturn('placard');
        $fileInfo->getStorage()->willReturn('placard');

        $this->isEqual($sameMediaValue)->shouldReturn(true);
    }

    function it_compares_itself_with_null_file_info_to_a_media_value_with_null_file_info(
        MediaValueInterface $sameMediaValue
    ) {
        $this->beConstructedThrough('scopableLocalizableValue', ['my_media', null, 'ecommerce', 'en_US']);

        $sameMediaValue->getScopeCode()->willReturn('ecommerce');
        $sameMediaValue->getLocaleCode()->willReturn('en_US');
        $sameMediaValue->getData()->willReturn(null);

        $this->isEqual($sameMediaValue)->shouldReturn(true);
    }

    function it_compares_itself_with_null_file_info_to_a_different_media_value_with_null_file_info(
        MediaValueInterface $sameMediaValue
    ) {
        $this->beConstructedThrough('scopableLocalizableValue', ['my_media', null, 'ecommerce', 'en_US']);

        $sameMediaValue->getScopeCode()->willReturn('mobile');
        $sameMediaValue->getLocaleCode()->willReturn('en_US');
        $sameMediaValue->getData()->willReturn(null);

        $this->isEqual($sameMediaValue)->shouldReturn(false);
    }

    function it_compares_itself_to_a_media_value_with_null_media_info(
        FileInfoInterface $fileInfo,
        MediaValueInterface $sameMediaValue
    ) {
        $this->beConstructedThrough('scopableLocalizableValue', ['my_media', $fileInfo, 'ecommerce', 'en_US']);

        $sameMediaValue->getScopeCode()->willReturn('ecommerce');
        $sameMediaValue->getLocaleCode()->willReturn('en_US');
        $sameMediaValue->getData()->willReturn(null);

        $this->isEqual($sameMediaValue)->shouldReturn(false);
    }

    function it_compares_itself_to_a_metric_value(
        FileInfoInterface $fileInfo,
        MetricValueInterface $metricValue
    ) {
        $this->beConstructedThrough('scopableLocalizableValue', ['my_media', $fileInfo, 'ecommerce', 'en_US']);

        $this->isEqual($metricValue)->shouldReturn(false);
    }

    function it_compares_itself_to_a_different_media_value(
        FileInfoInterface $fileInfo,
        MediaValueInterface $differentMediaValue,
        FileInfoInterface $sameFileInfo
    ) {
        $this->beConstructedThrough('scopableLocalizableValue', ['my_media', $fileInfo, 'ecommerce', 'en_US']);

        $differentMediaValue->getScopeCode()->willReturn('mobile');
        $differentMediaValue->getLocaleCode()->willReturn('fr_FR');
        $differentMediaValue->getData()->willReturn($sameFileInfo);

        $this->isEqual($differentMediaValue)->shouldReturn(false);
    }

    function it_compares_itself_to_a_different_file_info(
        FileInfoInterface $fileInfo,
        MediaValueInterface $sameMediaValue,
        FileInfoInterface $differentFileInfo
    ) {
        $this->beConstructedThrough('scopableLocalizableValue', ['my_media', $fileInfo, 'ecommerce', 'en_US']);

        $sameMediaValue->getScopeCode()->willReturn('ecommerce');
        $sameMediaValue->getLocaleCode()->willReturn('en_US');
        $sameMediaValue->getData()->willReturn($differentFileInfo);

        $differentFileInfo->getOriginalFilename()->willReturn('myfile');
        $fileInfo->getOriginalFilename()->willReturn('myfile');

        $differentFileInfo->getMimeType()->willReturn('image/png');
        $fileInfo->getMimeType()->willReturn('image/jpeg');

        $differentFileInfo->getSize()->willReturn(123);
        $fileInfo->getSize()->willReturn(123);

        $differentFileInfo->getExtension()->willReturn('png');
        $fileInfo->getExtension()->willReturn('jpg');

        $differentFileInfo->getHash()->willReturn('123456stvcqjd');
        $fileInfo->getHash()->willReturn('123456stvcqjd');

        $differentFileInfo->getKey()->willReturn('key');
        $fileInfo->getKey()->willReturn('key');

        $differentFileInfo->getStorage()->willReturn('placard');
        $fileInfo->getStorage()->willReturn('placard');

        $this->isEqual($sameMediaValue)->shouldReturn(false);
    }
}
