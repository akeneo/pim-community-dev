<?php

namespace spec\Pim\Bundle\CatalogBundle\Doctrine\MongoDBODM\Filter;

use Doctrine\ODM\MongoDB\Query\Builder;
use PhpSpec\ObjectBehavior;
use Pim\Bundle\CatalogBundle\Exception\InvalidArgumentException;
use Pim\Bundle\CatalogBundle\Model\AttributeInterface;
use Pim\Bundle\CatalogBundle\Validator\AttributeValidatorHelper;
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
            ['STARTS WITH', 'ENDS WITH', 'CONTAINS', 'DOES NOT CONTAIN', '=', 'EMPTY']
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
        $this->shouldImplement('Pim\Bundle\CatalogBundle\Query\Filter\AttributeFilterInterface');
    }

    function it_supports_operators()
    {
        $this->getOperators()->shouldReturn(
            ['STARTS WITH', 'ENDS WITH', 'CONTAINS', 'DOES NOT CONTAIN', '=', 'EMPTY']
        );

        $this->supportsOperator('=')->shouldReturn(true);
        $this->supportsOperator('FAKE')->shouldReturn(false);
    }

    function it_supports_file_and_image_attributes(
        AttributeInterface $fileAttribute,
        AttributeInterface $imageAttribute,
        AttributeInterface $textAttribute
    ) {
        $fileAttribute->getAttributeType()->willReturn('pim_catalog_file');
        $imageAttribute->getAttributeType()->willReturn('pim_catalog_image');
        $textAttribute->getAttributeType()->willReturn('pim_catalog_text');

        $this->supportsAttribute($fileAttribute)->shouldReturn(true);
        $this->supportsAttribute($imageAttribute)->shouldReturn(true);
        $this->supportsAttribute($textAttribute)->shouldReturn(false);
    }

    function it_adds_a_starts_with_filter_on_an_attribute_in_the_query($qb, $image, $attrValidatorHelper)
    {
        $attrValidatorHelper->validateLocale($image, Argument::any())->shouldBeCalled();
        $attrValidatorHelper->validateScope($image, Argument::any())->shouldBeCalled();

        $qb->field('normalizedData.picture.originalFilename')
            ->shouldBeCalled()
            ->willReturn($qb);

        $qb->equals(new \MongoRegex('/^foo/i'))
            ->shouldBeCalled();

        $this->addAttributeFilter($image, 'STARTS WITH', 'foo');
    }

    function it_adds_a_ends_with_filter_on_an_attribute_in_the_query($qb, $image, $attrValidatorHelper)
    {
        $attrValidatorHelper->validateLocale($image, Argument::any())->shouldBeCalled();
        $attrValidatorHelper->validateScope($image, Argument::any())->shouldBeCalled();

        $qb->field('normalizedData.picture.originalFilename')
            ->shouldBeCalled()
            ->willReturn($qb);

        $qb->equals(new \MongoRegex('/foo$/i'))
            ->shouldBeCalled();

        $this->addAttributeFilter($image, 'ENDS WITH', 'foo');
    }

    function it_adds_a_contains_filter_on_an_attribute_in_the_query($qb, $image, $attrValidatorHelper)
    {
        $attrValidatorHelper->validateLocale($image, Argument::any())->shouldBeCalled();
        $attrValidatorHelper->validateScope($image, Argument::any())->shouldBeCalled();

        $qb->field('normalizedData.picture.originalFilename')
            ->shouldBeCalled()
            ->willReturn($qb);

        $qb->equals(new \MongoRegex('/foo/i'))
            ->shouldBeCalled();

        $this->addAttributeFilter($image, 'CONTAINS', 'foo');
    }

    function it_adds_a_does_not_contain_filter_on_an_attribute_in_the_query($qb, $image, $attrValidatorHelper)
    {
        $attrValidatorHelper->validateLocale($image, Argument::any())->shouldBeCalled();
        $attrValidatorHelper->validateScope($image, Argument::any())->shouldBeCalled();

        $qb->field('normalizedData.picture.originalFilename')
            ->shouldBeCalled()
            ->willReturn($qb);

        $qb->equals(new \MongoRegex('/^((?!foo).)*$/i'))
            ->shouldBeCalled();

        $this->addAttributeFilter($image, 'DOES NOT CONTAIN', 'foo');
    }

    function it_adds_an_equal_filter_on_an_attribute_in_the_query($qb, $image, $attrValidatorHelper)
    {
        $attrValidatorHelper->validateLocale($image, Argument::any())->shouldBeCalled();
        $attrValidatorHelper->validateScope($image, Argument::any())->shouldBeCalled();

        $qb->field('normalizedData.picture.originalFilename')
            ->shouldBeCalled()
            ->willReturn($qb);

        $qb->equals('foo')
            ->shouldBeCalled();

        $this->addAttributeFilter($image, '=', 'foo');
    }

    function it_adds_a_empty_filter_on_an_attribute_in_the_query($qb, $attrValidatorHelper, $image)
    {
        $attrValidatorHelper->validateLocale($image, Argument::any())->shouldBeCalled();
        $attrValidatorHelper->validateScope($image, Argument::any())->shouldBeCalled();

        $qb->field('normalizedData.picture.originalFilename')
            ->shouldBeCalled()
            ->willReturn($qb);

        $qb->exists(false)
            ->shouldBeCalled();

        $this->addAttributeFilter($image, 'EMPTY', null);
    }

    function it_throws_an_exception_if_value_is_not_valid($image)
    {
        $image->getCode()->willReturn('media_code');
        $value = ['data' => 132, 'unit' => 'foo'];
        $this->shouldThrow(
            InvalidArgumentException::stringExpected('media_code', 'filter', 'media', gettype($value))
        )->during('addAttributeFilter', [$image, '=', $value]);
    }
}
