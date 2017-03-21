<?php

namespace spec\Pim\Bundle\CatalogBundle\Doctrine\MongoDBODM\Filter;

use Akeneo\Component\StorageUtils\Exception\InvalidPropertyTypeException;
use Doctrine\ODM\MongoDB\Query\Builder;
use PhpSpec\ObjectBehavior;
use Pim\Component\Catalog\Model\AttributeInterface;
use Pim\Component\Catalog\Validator\AttributeValidatorHelper;
use Prophecy\Argument;

/**
 * @require Doctrine\ODM\MongoDB\Query\Builder
 */
class MediaFilterSpec extends ObjectBehavior
{
    function let(Builder $qb, AttributeInterface $image, AttributeValidatorHelper $attrValidatorHelper)
    {
        $this->beConstructedWith(
            $attrValidatorHelper,
            ['pim_catalog_image', 'pim_catalog_file'],
            ['STARTS WITH', 'ENDS WITH', 'CONTAINS', 'DOES NOT CONTAIN', '=', 'EMPTY', 'IS EMPTY', '!=']
        );
        $this->setQueryBuilder($qb);

        $image->getCode()->willReturn('picture');
        $image->isLocalizable()->willReturn(false);
        $image->isScopable()->willReturn(false);
    }

    function it_is_a_media_filter()
    {
        $this->shouldHaveType('Pim\Bundle\CatalogBundle\Doctrine\MongoDBODM\Filter\MediaFilter');
    }

    function it_is_an_attribute_filter()
    {
        $this->shouldImplement('Pim\Component\Catalog\Query\Filter\AttributeFilterInterface');
    }

    function it_supports_operators()
    {
        $this->getOperators()->shouldReturn(
            ['STARTS WITH', 'ENDS WITH', 'CONTAINS', 'DOES NOT CONTAIN', '=', 'EMPTY', 'IS EMPTY', '!=']
        );

        $this->supportsOperator('=')->shouldReturn(true);
        $this->supportsOperator('FAKE')->shouldReturn(false);
    }

    function it_supports_file_and_image_attributes(
        AttributeInterface $fileAttribute,
        AttributeInterface $imageAttribute,
        AttributeInterface $textAttribute
    ) {
        $fileAttribute->getType()->willReturn('pim_catalog_file');
        $imageAttribute->getType()->willReturn('pim_catalog_image');
        $textAttribute->getType()->willReturn('pim_catalog_text');

        $this->supportsAttribute($fileAttribute)->shouldReturn(true);
        $this->supportsAttribute($imageAttribute)->shouldReturn(true);
        $this->supportsAttribute($textAttribute)->shouldReturn(false);
    }

    function it_adds_a_starts_with_filter_on_an_attribute_in_the_query($qb, $image, $attrValidatorHelper)
    {
        $attrValidatorHelper->validateLocale($image, Argument::any())->shouldBeCalled();
        $attrValidatorHelper->validateScope($image, Argument::any())->shouldBeCalled();

        $qb->field('normalizedData.picture.originalFilename')->shouldBeCalled()->willReturn($qb);
        $qb->equals(new \MongoRegex('/^foo/i'))->shouldBeCalled();

        $this->addAttributeFilter($image, 'STARTS WITH', 'foo');
    }

    function it_adds_a_ends_with_filter_on_an_attribute_in_the_query($qb, $image, $attrValidatorHelper)
    {
        $attrValidatorHelper->validateLocale($image, Argument::any())->shouldBeCalled();
        $attrValidatorHelper->validateScope($image, Argument::any())->shouldBeCalled();

        $qb->field('normalizedData.picture.originalFilename')->shouldBeCalled()->willReturn($qb);
        $qb->equals(new \MongoRegex('/foo$/i'))->shouldBeCalled();

        $this->addAttributeFilter($image, 'ENDS WITH', 'foo');
    }

    function it_adds_a_contains_filter_on_an_attribute_in_the_query($qb, $image, $attrValidatorHelper)
    {
        $attrValidatorHelper->validateLocale($image, Argument::any())->shouldBeCalled();
        $attrValidatorHelper->validateScope($image, Argument::any())->shouldBeCalled();

        $qb->field('normalizedData.picture.originalFilename')->shouldBeCalled()->willReturn($qb);
        $qb->equals(new \MongoRegex('/foo/i'))->shouldBeCalled();

        $this->addAttributeFilter($image, 'CONTAINS', 'foo');
    }

    function it_adds_a_does_not_contain_filter_on_an_attribute_in_the_query($qb, $image, $attrValidatorHelper)
    {
        $attrValidatorHelper->validateLocale($image, Argument::any())->shouldBeCalled();
        $attrValidatorHelper->validateScope($image, Argument::any())->shouldBeCalled();

        $qb->field('normalizedData.picture.originalFilename')->shouldBeCalled()->willReturn($qb);
        $qb->equals(new \MongoRegex('/^((?!foo).)*$/i'))->shouldBeCalled();

        $this->addAttributeFilter($image, 'DOES NOT CONTAIN', 'foo');
    }

    function it_adds_an_equal_filter_on_an_attribute_in_the_query($qb, $image, $attrValidatorHelper)
    {
        $attrValidatorHelper->validateLocale($image, Argument::any())->shouldBeCalled();
        $attrValidatorHelper->validateScope($image, Argument::any())->shouldBeCalled();

        $qb->field('normalizedData.picture.originalFilename')->shouldBeCalled()->willReturn($qb);
        $qb->equals(new \MongoRegex('/^foo$/i'))->shouldBeCalled();

        $this->addAttributeFilter($image, '=', 'foo');
    }

    function it_adds_a_not_equal_filter_on_an_attribute_in_the_query($qb, $image, $attrValidatorHelper)
    {
        $attrValidatorHelper->validateLocale($image, Argument::any())->shouldBeCalled();
        $attrValidatorHelper->validateScope($image, Argument::any())->shouldBeCalled();

        $qb->field('normalizedData.picture.originalFilename')->shouldBeCalled()->willReturn($qb);
        $qb->exists(true)->shouldBeCalled();
        $qb->notEqual('foo')->shouldBeCalled();

        $this->addAttributeFilter($image, '!=', 'foo');
    }

    function it_adds_an_empty_filter_on_an_attribute_in_the_query($qb, $attrValidatorHelper, $image)
    {
        $attrValidatorHelper->validateLocale($image, Argument::any())->shouldBeCalled();
        $attrValidatorHelper->validateScope($image, Argument::any())->shouldBeCalled();

        $qb->field('normalizedData.picture.originalFilename')->shouldBeCalled()->willReturn($qb);
        $qb->exists(false)->shouldBeCalled();

        $this->addAttributeFilter($image, 'EMPTY', null);
    }

    function it_adds_a_not_empty_filter_on_an_attribute_in_the_query($qb, $attrValidatorHelper, $image)
    {
        $attrValidatorHelper->validateLocale($image, Argument::any())->shouldBeCalled();
        $attrValidatorHelper->validateScope($image, Argument::any())->shouldBeCalled();

        $qb->field('normalizedData.picture.originalFilename')->shouldBeCalled()->willReturn($qb);
        $qb->exists(true)->shouldBeCalled();

        $this->addAttributeFilter($image, 'NOT EMPTY', null);
    }

    function it_throws_an_exception_if_value_is_not_valid($image)
    {
        $image->getCode()->willReturn('media_code');
        $value = ['amount' => 132, 'unit' => 'foo'];
        $this->shouldThrow(
            InvalidPropertyTypeException::stringExpected(
                'media_code',
                'Pim\Bundle\CatalogBundle\Doctrine\MongoDBODM\Filter\MediaFilter',
                $value
            )
        )->during('addAttributeFilter', [$image, '=', $value]);
    }
}
