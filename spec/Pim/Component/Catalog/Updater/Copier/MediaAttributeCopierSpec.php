<?php

namespace spec\Pim\Component\Catalog\Updater\Copier;

use Akeneo\Component\FileStorage\RawFile\RawFileFetcherInterface;
use Akeneo\Component\FileStorage\RawFile\RawFileStorerInterface;
use League\Flysystem\MountManager;
use PhpSpec\ObjectBehavior;
use Pim\Bundle\CatalogBundle\Builder\ProductBuilderInterface;
use Pim\Bundle\CatalogBundle\Factory\MediaFactory;
use Pim\Bundle\CatalogBundle\Model\AttributeInterface;
use Pim\Bundle\CatalogBundle\Model\ProductInterface;
use Pim\Bundle\CatalogBundle\Model\ProductMediaInterface;
use Pim\Bundle\CatalogBundle\Model\ProductValueInterface;
use Pim\Bundle\CatalogBundle\Validator\AttributeValidatorHelper;
use Prophecy\Argument;

class MediaAttributeCopierSpec extends ObjectBehavior
{
    function let(
        ProductBuilderInterface $builder,
        AttributeValidatorHelper $attrValidatorHelper,
        RawFileFetcherInterface $rawFileFetcher,
        RawFileStorerInterface $rawFileStorer,
        MediaFactory $mediaFactory,
        MountManager $mountManager
    ) {
        $this->beConstructedWith(
            $builder,
            $attrValidatorHelper,
            $rawFileFetcher,
            $rawFileStorer,
            $mediaFactory,
            $mountManager,
            ['media'],
            ['media']
        );
    }

    function it_is_initializable()
    {
        $this->shouldHaveType('Pim\Component\Catalog\Updater\Copier\MediaAttributeCopier');
    }

    function it_is_a_copier()
    {
        $this->shouldImplement('Pim\Component\Catalog\Updater\Copier\CopierInterface');
    }

    function it_supports_media_attributes(
        AttributeInterface $fromMediaAttribute,
        AttributeInterface $toMediaAttribute,
        AttributeInterface $toTextareaAttribute,
        AttributeInterface $fromNumberAttribute,
        AttributeInterface $toNumberAttribute
    ) {
        $fromMediaAttribute->getAttributeType()->willReturn('media');
        $toMediaAttribute->getAttributeType()->willReturn('media');
        $this->supportsAttributes($fromMediaAttribute, $toMediaAttribute)->shouldReturn(true);

        $fromNumberAttribute->getAttributeType()->willReturn('pim_catalog_number');
        $toNumberAttribute->getAttributeType()->willReturn('pim_catalog_number');
        $this->supportsAttributes($fromNumberAttribute, $toNumberAttribute)->shouldReturn(false);

        $this->supportsAttributes($fromMediaAttribute, $toNumberAttribute)->shouldReturn(false);
        $this->supportsAttributes($fromNumberAttribute, $toTextareaAttribute)->shouldReturn(false);
    }

    function it_copies_when_a_product_value_has_the_values_and_the_media(
        $attrValidatorHelper,
        ProductMediaInterface $fromMedia,
        ProductMediaInterface $toMedia,
        AttributeInterface $fromAttribute,
        AttributeInterface $toAttribute,
        ProductInterface $product,
        ProductValueInterface $fromProductValue,
        ProductValueInterface $toProductValue,
        RawFileStorerInterface $rawFileStorer,
        RawFileFetcherInterface $rawFileFetcher
    ) {
        $fromLocale = 'fr_FR';
        $toLocale = 'fr_FR';
        $toScope = 'mobile';
        $fromScope = 'mobile';

        $fromAttribute->getCode()->willReturn('fromAttributeCode');
        $toAttribute->getCode()->willReturn('toAttributeCode');

        $attrValidatorHelper->validateLocale(Argument::cetera())->shouldBeCalled();
        $attrValidatorHelper->validateScope(Argument::cetera())->shouldBeCalled();

        $rawFileStorer->store()->shouldBeCalled();
        $rawFileFetcher->fetch()->shouldBeCalled();

        $fromProductValue->getMedia()->willReturn($fromMedia);
        $toProductValue->getMedia()->willReturn($toMedia);

        $product->getValue('fromAttributeCode', $fromLocale, $fromScope)->willReturn($fromProductValue);
        $product->getValue('toAttributeCode', $toLocale, $toScope)->willReturn($toProductValue);

        $this->copyAttributeData(
            $product,
            $product,
            $fromAttribute,
            $toAttribute,
            ['from_locale' => $fromLocale, 'to_locale' => $toLocale, 'from_scope' => $fromScope, 'to_scope' => $toScope]
        );
    }

    function it_copies_when_a_product_value_has_a_media_but_not_the_target_value(
        $builder,
        $attrValidatorHelper,
        ProductMediaInterface $fromMedia,
        ProductMediaInterface $toMedia,
        AttributeInterface $fromAttribute,
        AttributeInterface $toAttribute,
        ProductInterface $product,
        ProductValueInterface $fromProductValue,
        ProductValueInterface $toProductValue
    ) {
        $fromLocale = 'fr_FR';
        $toLocale = 'fr_FR';
        $toScope = 'mobile';
        $fromScope = 'mobile';

        $fromAttribute->getCode()->willReturn('fromAttributeCode');
        $toAttribute->getCode()->willReturn('toAttributeCode');

        $attrValidatorHelper->validateLocale(Argument::cetera())->shouldBeCalled();
        $attrValidatorHelper->validateScope(Argument::cetera())->shouldBeCalled();

        $fromProductValue->getMedia()->willReturn($fromMedia);

        $product->getValue('fromAttributeCode', $fromLocale, $fromScope)->willReturn($fromProductValue);
        $product->getValue('toAttributeCode', $toLocale, $toScope)->willReturn(null);

        $builder->addProductValue($product, $toAttribute, $toLocale, $toScope)->willReturn($toProductValue);
        $toProductValue->getMedia()->willReturn($toMedia);

        $this->copyAttributeData(
            $product,
            $product,
            $fromAttribute,
            $toAttribute,
            ['from_locale' => $fromLocale, 'to_locale' => $toLocale, 'from_scope' => $fromScope, 'to_scope' => $toScope]
        );
    }

    function it_copies_an_empty_media(
        $attrValidatorHelper,
        ProductMediaInterface $toMedia,
        AttributeInterface $fromAttribute,
        AttributeInterface $toAttribute,
        ProductInterface $product,
        ProductValueInterface $fromProductValue,
        ProductValueInterface $toProductValue
    ) {
        $fromLocale = 'fr_FR';
        $toLocale = 'fr_FR';
        $toScope = 'mobile';
        $fromScope = 'mobile';

        $fromAttribute->getCode()->willReturn('fromAttributeCode');
        $toAttribute->getCode()->willReturn('toAttributeCode');

        $attrValidatorHelper->validateLocale(Argument::cetera())->shouldBeCalled();
        $attrValidatorHelper->validateScope(Argument::cetera())->shouldBeCalled();

        $fromProductValue->getMedia()->willReturn(null);
        $toProductValue->getMedia()->willReturn($toMedia);

        $toMedia->setOriginalFilename(null)->shouldBeCalled();
        $toMedia->setFilename(null)->shouldBeCalled();
        $toMedia->setMimeType(null)->shouldBeCalled();

        $product->getValue('fromAttributeCode', $fromLocale, $fromScope)->willReturn($fromProductValue);
        $product->getValue('toAttributeCode', $toLocale, $toScope)->willReturn($toProductValue);

        $this->copyAttributeData(
            $product,
            $product,
            $fromAttribute,
            $toAttribute,
            ['from_locale' => $fromLocale, 'to_locale' => $toLocale, 'from_scope' => $fromScope, 'to_scope' => $toScope]
        );
    }
}
