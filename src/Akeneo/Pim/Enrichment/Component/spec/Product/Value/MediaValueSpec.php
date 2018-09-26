<?php

namespace spec\Akeneo\Pim\Enrichment\Component\Product\Value;

use Akeneo\Pim\Enrichment\Component\Product\Value\MediaValueInterface;
use Akeneo\Pim\Enrichment\Component\Product\Value\MetricValueInterface;
use Akeneo\Pim\Structure\Component\Model\AttributeInterface;
use Akeneo\Tool\Component\FileStorage\Model\FileInfoInterface;
use PhpSpec\ObjectBehavior;

class MediaValueSpec extends ObjectBehavior
{
    function it_returns_data(AttributeInterface $attribute, FileInfoInterface $fileInfo)
    {
        $attribute->isScopable()->willReturn(true);
        $attribute->isLocalizable()->willReturn(true);
        $this->beConstructedWith($attribute, 'ecommerce', 'en_US', $fileInfo);

        $this->getData()->shouldBeAnInstanceOf(FileInfoInterface::class);
        $this->getData()->shouldReturn($fileInfo);
    }

    function it_compares_itself_to_a_same_media_value(
        AttributeInterface $attribute,
        FileInfoInterface $fileInfo,
        MediaValueInterface $sameMediaValue,
        FileInfoInterface $sameFileInfo
    ) {
        $attribute->isScopable()->willReturn(true);
        $attribute->isLocalizable()->willReturn(true);
        $this->beConstructedWith($attribute, 'ecommerce', 'en_US', $fileInfo);

        $sameMediaValue->getScope()->willReturn('ecommerce');
        $sameMediaValue->getLocale()->willReturn('en_US');
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
        AttributeInterface $attribute,
        MediaValueInterface $sameMediaValue
    ) {
        $attribute->isScopable()->willReturn(true);
        $attribute->isLocalizable()->willReturn(true);
        $this->beConstructedWith($attribute, 'ecommerce', 'en_US', null);

        $sameMediaValue->getScope()->willReturn('ecommerce');
        $sameMediaValue->getLocale()->willReturn('en_US');
        $sameMediaValue->getData()->willReturn(null);

        $this->isEqual($sameMediaValue)->shouldReturn(true);
    }

    function it_compares_itself_with_null_file_info_to_a_different_media_value_with_null_file_info(
        AttributeInterface $attribute,
        MediaValueInterface $sameMediaValue
    ) {
        $attribute->isScopable()->willReturn(true);
        $attribute->isLocalizable()->willReturn(true);
        $this->beConstructedWith($attribute, 'ecommerce', 'en_US', null);

        $sameMediaValue->getScope()->willReturn('mobile');
        $sameMediaValue->getLocale()->willReturn('en_US');
        $sameMediaValue->getData()->willReturn(null);

        $this->isEqual($sameMediaValue)->shouldReturn(false);
    }

    function it_compares_itself_to_a_media_value_with_null_media_info(
        AttributeInterface $attribute,
        FileInfoInterface $fileInfo,
        MediaValueInterface $sameMediaValue
    ) {
        $attribute->isScopable()->willReturn(true);
        $attribute->isLocalizable()->willReturn(true);
        $this->beConstructedWith($attribute, 'ecommerce', 'en_US', $fileInfo);

        $sameMediaValue->getScope()->willReturn('ecommerce');
        $sameMediaValue->getLocale()->willReturn('en_US');
        $sameMediaValue->getData()->willReturn(null);

        $this->isEqual($sameMediaValue)->shouldReturn(false);
    }

    function it_compares_itself_to_a_metric_value(
        AttributeInterface $attribute,
        FileInfoInterface $fileInfo,
        MetricValueInterface $metricValue
    ) {
        $attribute->isScopable()->willReturn(true);
        $attribute->isLocalizable()->willReturn(true);
        $this->beConstructedWith($attribute, 'ecommerce', 'en_US', $fileInfo);

        $this->isEqual($metricValue)->shouldReturn(false);
    }

    function it_compares_itself_to_a_different_media_value(
        AttributeInterface $attribute,
        FileInfoInterface $fileInfo,
        MediaValueInterface $differentMediaValue,
        FileInfoInterface $sameFileInfo
    ) {
        $attribute->isScopable()->willReturn(true);
        $attribute->isLocalizable()->willReturn(true);
        $this->beConstructedWith($attribute, 'ecommerce', 'en_US', $fileInfo);

        $differentMediaValue->getScope()->willReturn('mobile');
        $differentMediaValue->getLocale()->willReturn('fr_FR');
        $differentMediaValue->getData()->willReturn($sameFileInfo);

        $this->isEqual($differentMediaValue)->shouldReturn(false);
    }

    function it_compares_itself_to_a_different_file_info(
        AttributeInterface $attribute,
        FileInfoInterface $fileInfo,
        MediaValueInterface $sameMediaValue,
        FileInfoInterface $differentFileInfo
    ) {
        $attribute->isScopable()->willReturn(true);
        $attribute->isLocalizable()->willReturn(true);
        $this->beConstructedWith($attribute, 'ecommerce', 'en_US', $fileInfo);

        $sameMediaValue->getScope()->willReturn('ecommerce');
        $sameMediaValue->getLocale()->willReturn('en_US');
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
