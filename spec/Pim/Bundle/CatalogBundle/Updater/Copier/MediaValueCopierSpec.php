<?php

namespace spec\Pim\Bundle\CatalogBundle\Updater\Copier;

use PhpSpec\ObjectBehavior;
use Pim\Bundle\CatalogBundle\Builder\ProductBuilderInterface;
use Pim\Bundle\CatalogBundle\Factory\MediaFactory;
use Pim\Bundle\CatalogBundle\Manager\MediaManager;
use Pim\Bundle\CatalogBundle\Model\AttributeInterface;
use Pim\Bundle\CatalogBundle\Model\ProductInterface;
use Pim\Bundle\CatalogBundle\Model\ProductMediaInterface;
use Pim\Bundle\CatalogBundle\Model\ProductValueInterface;
use Pim\Bundle\CatalogBundle\Validator\AttributeValidatorHelper;
use Prophecy\Argument;

class MediaValueCopierSpec extends ObjectBehavior
{
    function let(
        ProductBuilderInterface $builder,
        AttributeValidatorHelper $attrValidatorHelper,
        MediaManager $mediaManager,
        MediaFactory $mediaFactory
    ) {
        $this->beConstructedWith(
            $builder,
            $attrValidatorHelper,
            $mediaManager,
            $mediaFactory,
            ['media'],
            ['media']
        );
    }

    function it_is_initializable()
    {
        $this->shouldHaveType('Pim\Bundle\CatalogBundle\Updater\Copier\MediaValueCopier');
    }

    function it_is_a_copier()
    {
        $this->shouldImplement('Pim\Bundle\CatalogBundle\Updater\Copier\CopierInterface');
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
        $this->supports($fromMediaAttribute, $toMediaAttribute)->shouldReturn(true);

        $fromNumberAttribute->getAttributeType()->willReturn('pim_catalog_number');
        $toNumberAttribute->getAttributeType()->willReturn('pim_catalog_number');
        $this->supports($fromNumberAttribute, $toNumberAttribute)->shouldReturn(false);

        $this->supports($fromMediaAttribute, $toNumberAttribute)->shouldReturn(false);
        $this->supports($fromNumberAttribute, $toTextareaAttribute)->shouldReturn(false);
    }

    function it_copies_when_a_product_value_has_the_values_and_the_media(
        $mediaManager,
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

        $fromMedia->getOriginalFilename()->shouldBeCalled()->willReturn('picture.jpg');

        $fromProductValue->getMedia()->willReturn($fromMedia);
        $toProductValue->getMedia()->willReturn($toMedia);

        $product->getValue('fromAttributeCode', $fromLocale, $fromScope)->willReturn($fromProductValue);
        $product->getValue('toAttributeCode', $toLocale, $toScope)->willReturn($toProductValue);

        $mediaManager->generateFilenamePrefix($product, $fromProductValue)->willReturn('prefix-to-file');
        $mediaManager->duplicate($fromMedia, $toMedia, 'prefix-to-file')->shouldBeCalled();

        $this->copyValue([$product], $fromAttribute, $toAttribute, $fromLocale, $toLocale, $fromScope, $toScope);
    }

    function it_copies_when_a_product_value_has_a_media_but_not_the_target_value(
        $builder,
        $mediaManager,
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

        $fromMedia->getOriginalFilename()->shouldBeCalled()->willReturn('picture.jpg');

        $fromProductValue->getMedia()->willReturn($fromMedia);

        $product->getValue('fromAttributeCode', $fromLocale, $fromScope)->willReturn($fromProductValue);
        $product->getValue('toAttributeCode', $toLocale, $toScope)->willReturn(null);

        $builder->addProductValue($product, $toAttribute, $toLocale, $toScope)->willReturn($toProductValue);
        $toProductValue->getMedia()->willReturn($toMedia);

        $mediaManager->generateFilenamePrefix($product, $fromProductValue)->willReturn('prefix-to-file');
        $mediaManager->duplicate($fromMedia, $toMedia, 'prefix-to-file')->shouldBeCalled();

        $this->copyValue([$product], $fromAttribute, $toAttribute, $fromLocale, $toLocale, $fromScope, $toScope);
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

        $this->copyValue([$product], $fromAttribute, $toAttribute, $fromLocale, $toLocale, $fromScope, $toScope);
    }
}
