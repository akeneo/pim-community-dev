<?php

namespace spec\Pim\Bundle\CatalogBundle\Doctrine\ORM\Filter;

use Akeneo\Component\StorageUtils\Exception\InvalidPropertyTypeException;
use Doctrine\ORM\Query\Expr;
use Doctrine\ORM\QueryBuilder;
use PhpSpec\ObjectBehavior;
use Pim\Bundle\CatalogBundle\Doctrine\ORM\Filter\MediaFilter;
use Pim\Component\Catalog\Model\AttributeInterface;
use Pim\Component\Catalog\Query\Filter\AttributeFilterInterface;
use Pim\Component\Catalog\Validator\AttributeValidatorHelper;
use Prophecy\Argument;

class MediaFilterSpec extends ObjectBehavior
{
    function let(QueryBuilder $qb, AttributeValidatorHelper $attrValidatorHelper, Expr $expr, AttributeInterface $image)
    {
        $this->beConstructedWith(
            $attrValidatorHelper,
            ['pim_catalog_image', 'pim_catalog_file'],
            ['STARTS WITH', 'ENDS WITH', 'CONTAINS', 'DOES NOT CONTAIN', '=', 'EMPTY', 'NOT EMPTY', '!=']
        );
        $this->setQueryBuilder($qb);

        $qb->getRootAliases()->willReturn(['p']);
        $qb->expr()->willReturn($expr);

        $image->getId()->willReturn(1);
        $image->getCode()->willReturn('picture');
        $image->isLocalizable()->willReturn(false);
        $image->isScopable()->willReturn(false);
        $image->getBackendType()->willReturn('media');
    }

    function it_is_a_media_filter()
    {
        $this->shouldHaveType(MediaFilter::class);
    }

    function it_is_an_attribute_filter()
    {
        $this->shouldImplement(AttributeFilterInterface::class);
    }

    function it_supports_operators()
    {
        $this->getOperators()->shouldReturn(
            ['STARTS WITH', 'ENDS WITH', 'CONTAINS', 'DOES NOT CONTAIN', '=', 'EMPTY', 'NOT EMPTY', '!=']
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

    function it_adds_a_starts_with_filter_on_an_attribute_in_the_query($qb, $attrValidatorHelper, $expr, $image)
    {
        $attrValidatorHelper->validateLocale($image, Argument::any())->shouldBeCalled();
        $attrValidatorHelper->validateScope($image, Argument::any())->shouldBeCalled();

        $qb->innerJoin('p.values', Argument::any(), 'WITH', Argument::any())
            ->shouldBeCalled()
            ->willReturn($qb);

        $expr->literal('foo%')->willReturn('foo%');
        $expr->like(Argument::any(), 'foo%')
            ->shouldBeCalled()
            ->willReturn('filterMediapicture.originalFilename LIKE foo%');

        $qb->innerJoin(
                Argument::any(),
                Argument::any(),
                'WITH',
                'filterMediapicture.originalFilename LIKE foo%'
            )
            ->shouldBeCalled();

        $this->addAttributeFilter($image, 'STARTS WITH', 'foo');
    }

    function it_adds_a_ends_with_filter_on_an_attribute_in_the_query($qb, $attrValidatorHelper, $expr, $image)
    {
        $attrValidatorHelper->validateLocale($image, Argument::any())->shouldBeCalled();
        $attrValidatorHelper->validateScope($image, Argument::any())->shouldBeCalled();

        $qb->innerJoin('p.values', Argument::any(), 'WITH', Argument::any())
            ->shouldBeCalled()
            ->willReturn($qb);

        $expr->literal('%foo')->willReturn('%foo');
        $expr->like(Argument::any(), '%foo')
            ->shouldBeCalled()
            ->willReturn('filterMediapicture.originalFilename LIKE %foo');

        $qb->innerJoin(
                Argument::any(),
                Argument::any(),
                'WITH',
                'filterMediapicture.originalFilename LIKE %foo'
            )
            ->shouldBeCalled();

        $this->addAttributeFilter($image, 'ENDS WITH', 'foo');
    }

    function it_adds_a_contains_filter_on_an_attribute_in_the_query($qb, $expr, $image)
    {
        $qb->innerJoin('p.values', Argument::any(), 'WITH', Argument::any())
            ->shouldBeCalled()
            ->willReturn($qb);

        $expr->literal('%foo%')->willReturn('%foo%');
        $expr->like(Argument::any(), '%foo%')
            ->shouldBeCalled()
            ->willReturn('filterMediapicture.originalFilename LIKE %foo%');

        $qb->innerJoin(
                Argument::any(),
                Argument::any(),
                'WITH',
                'filterMediapicture.originalFilename LIKE %foo%'
            )
            ->shouldBeCalled();

        $this->addAttributeFilter($image, 'CONTAINS', 'foo');
    }

    function it_adds_a_does_not_contain_filter_on_an_attribute_in_the_query($qb, $expr, $image)
    {
        $qb->innerJoin('p.values', Argument::any(), 'WITH', Argument::any())
            ->shouldBeCalled()
            ->willReturn($qb);

        $expr->literal('%foo%')->willReturn('%foo%');
        $expr->notLike(Argument::any(), '%foo%')->shouldBeCalled();

        $qb->innerJoin(
                Argument::any(),
                Argument::any(),
                'WITH',
                Argument::any()
            )
            ->shouldBeCalled();

        $this->addAttributeFilter($image, 'DOES NOT CONTAIN', 'foo');
    }

    function it_adds_an_equal_filter_on_an_attribute_in_the_query($qb, $expr, $image)
    {
        $qb->innerJoin('p.values', Argument::any(), 'WITH', Argument::any())
            ->shouldBeCalled()
            ->willReturn($qb);

        $expr->literal('foo')->willReturn('foo');
        $expr->like(Argument::any(), 'foo')
            ->willReturn('filterMediapicture.originalFilename LIKE "foo"');

        $qb->innerJoin(
                Argument::any(),
                Argument::any(),
                'WITH',
                'filterMediapicture.originalFilename LIKE "foo"'
            )
            ->shouldBeCalled();

        $this->addAttributeFilter($image, '=', 'foo');
    }

    function it_adds_a_empty_type_filter_on_an_attribute_in_the_query($qb, $expr, $image)
    {
        $qb->leftJoin('p.values', Argument::any(), 'WITH', Argument::any())
            ->shouldBeCalled()
            ->willReturn($qb);

        $expr->isNull(Argument::any())
            ->willReturn('filterMediapicture.originalFilename IS NULL');

        $qb->leftJoin(Argument::any(), Argument::any())
            ->shouldBeCalled();

        $qb->andWhere('filterMediapicture.originalFilename IS NULL')
            ->shouldBeCalled();

        $this->addAttributeFilter($image, 'EMPTY', null);
    }

    function it_adds_a_not_empty_filter_on_an_attribute_in_the_query($qb, $expr, $image)
    {
        $qb->leftJoin('p.values', Argument::any(), 'WITH', Argument::any())
            ->shouldBeCalled()
            ->willReturn($qb);

        $expr->isNotNull(Argument::any())
            ->willReturn('filterMediapicture.originalFilename IS NOT NULL');

        $qb->leftJoin(Argument::any(), Argument::any())
            ->shouldBeCalled();

        $qb->andWhere('filterMediapicture.originalFilename IS NOT NULL')
            ->shouldBeCalled();

        $this->addAttributeFilter($image, 'NOT EMPTY', null);
    }

    function it_adds_a_not_equal_filter_on_an_attribute_in_the_query($qb, $expr, $image)
    {
        $qb->innerJoin('p.values', Argument::any(), 'WITH', Argument::any())
            ->shouldBeCalled()
            ->willReturn($qb);

        $expr->literal('foo')->willReturn('foo');
        $expr->notLike(Argument::any(), 'foo')
            ->willReturn('filterMediapicture.originalFilename NOT LIKE "foo"');

        $qb->innerJoin(
            Argument::any(),
            Argument::any(),
            'WITH',
            'filterMediapicture.originalFilename NOT LIKE "foo"'
        )->shouldBeCalled();

        $this->addAttributeFilter($image, '!=', 'foo');
    }

    function it_throws_an_exception_if_value_is_not_valid(AttributeInterface $attribute)
    {
        $attribute->getCode()->willReturn('media_code');
        $value = ['amount' => 132, 'unit' => 'foo'];
        $this->shouldThrow(
            InvalidPropertyTypeException::stringExpected(
                'media_code',
                MediaFilter::class,
                $value
            )
        )->during('addAttributeFilter', [$attribute, '=', $value]);
    }
}
