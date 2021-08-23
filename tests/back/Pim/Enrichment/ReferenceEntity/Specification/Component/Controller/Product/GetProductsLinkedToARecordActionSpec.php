<?php

declare(strict_types=1);

namespace Specification\Akeneo\Pim\Enrichment\ReferenceEntity\Component\Controller\Product;

use Akeneo\Pim\Enrichment\Component\Product\Grid\Query\FetchProductAndProductModelRows;
use Akeneo\Pim\Enrichment\Component\Product\Grid\Query\FetchProductAndProductModelRowsParameters;
use Akeneo\Pim\Enrichment\Component\Product\Grid\ReadModel\Row;
use Akeneo\Pim\Enrichment\Component\Product\Grid\ReadModel\Rows;
use Akeneo\Pim\Enrichment\Component\Product\Model\WriteValueCollection;
use Akeneo\Pim\Enrichment\Component\Product\Query\Filter\Operators;
use Akeneo\Pim\Enrichment\Component\Product\Query\ProductQueryBuilderFactoryInterface;
use Akeneo\Pim\Enrichment\Component\Product\Query\ProductQueryBuilderInterface;
use Akeneo\Pim\Enrichment\ReferenceEntity\Component\Controller\Product\GetProductsLinkedToARecordAction;
use Akeneo\Pim\Enrichment\ReferenceEntity\Component\Normalizer\LinkedProductsNormalizer;
use PhpSpec\ObjectBehavior;
use Prophecy\Argument;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Exception\UnprocessableEntityHttpException;
use Symfony\Component\Validator\ConstraintViolation;
use Symfony\Component\Validator\ConstraintViolationList;
use Symfony\Component\Validator\Validator\ValidatorInterface;

class GetProductsLinkedToARecordActionSpec extends ObjectBehavior
{
    function let(
        ProductQueryBuilderFactoryInterface $pqbFactory,
        FetchProductAndProductModelRows $fetchProductAndProductModelRows,
        ValidatorInterface $validator,
        LinkedProductsNormalizer $linkedProductNormalizer
    ) {
        $this->beConstructedWith($pqbFactory, $fetchProductAndProductModelRows, $validator, $linkedProductNormalizer);
    }

    function it_is_initializable()
    {
        $this->shouldBeAnInstanceOf(GetProductsLinkedToARecordAction::class);
    }

    function it_fetches_20_products_linked_to_a_record(
        ProductQueryBuilderFactoryInterface $pqbFactory,
        ProductQueryBuilderInterface $pqb,
        FetchProductAndProductModelRows $fetchProductAndProductModelRows,
        ValidatorInterface $validator,
        LinkedProductsNormalizer $linkedProductNormalizer
    ) {
        $recordCode = 'kartell';
        $attributeCode = 'brand';
        $localeCode = 'en_US';
        $channel = 'ecommerce';

        $pqbFactory->create(['default_locale' => $localeCode, 'default_scope' => $channel, 'limit' => 20])->willReturn($pqb);
        $pqb->addFilter($attributeCode, Operators::IN_LIST, [$recordCode]);
        $pqb->addSorter('updated', 'DESC');

        $validator->validate(Argument::type(FetchProductAndProductModelRowsParameters::class))
            ->willReturn(new ConstraintViolationList());

        $rows = $this->get30Rows();
        $fetchProductAndProductModelRows->__invoke(Argument::type(FetchProductAndProductModelRowsParameters::class))
            ->willReturn($rows);
        $linkedProductNormalizer->normalize($rows, $channel, $localeCode)->willReturn(['product_info']);

        $this->__invoke(new Request(['channel' => $channel, 'locale' => $localeCode]), $recordCode, $attributeCode);
    }

    function it_throws_if_the_query_is_invalid(
        ProductQueryBuilderFactoryInterface $pqbFactory,
        ProductQueryBuilderInterface $pqb,
        FetchProductAndProductModelRows $fetchProductAndProductModelRows,
        ValidatorInterface $validator
    ) {
        $recordCode = 'kartell';
        $attributeCode = 'brand';
        $locale = 'en_US';
        $channel = 'ecommerce';

        $pqbFactory->create(['default_locale' => $locale, 'default_scope' => $channel, 'limit' => 20])
            ->willReturn($pqb);
        $pqb->addFilter($attributeCode, Operators::IN_LIST, [$recordCode]);
        $pqb->addSorter('updated', 'DESC');

        $validator->validate(Argument::type(FetchProductAndProductModelRowsParameters::class))
            ->willReturn($this->violation());

        $fetchProductAndProductModelRows->__invoke()->shouldNotBeCalled();

        $this->shouldThrow(UnprocessableEntityHttpException::class)
            ->during('__invoke', [new Request(['channel' => $channel, 'locale' => $locale]), $recordCode, $attributeCode]);
    }


    private function get30Rows(): Rows
    {
        $rows = [];
        for ($i = 0; $i < 30; $i++) {
            $rows[] = $this->row();
        }

        return new Rows($rows, 30, 20, 10);
    }

    private function row(): Row
    {
        return Row::fromProduct(
            'identifier',
            null,
            [],
            true,
            new \DateTime(),
            new \DateTime(),
            'label',
            null,
            999,
            10,
            'parent',
            new WriteValueCollection([])
        );
    }

    private function violation(): ConstraintViolationList
    {
        return new ConstraintViolationList([new ConstraintViolation('', '', [], '', '', '')]);
    }
}
