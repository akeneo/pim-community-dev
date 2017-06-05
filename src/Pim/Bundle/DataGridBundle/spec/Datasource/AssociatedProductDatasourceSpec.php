<?php

namespace spec\Pim\Bundle\DataGridBundle\Datasource;

use Akeneo\Component\StorageUtils\Cursor\CursorInterface;
use Akeneo\Component\StorageUtils\Exception\InvalidObjectException;
use Doctrine\Common\Collections\Collection;
use Doctrine\Common\Persistence\ObjectManager;
use Oro\Bundle\DataGridBundle\Datagrid\Datagrid;
use Oro\Bundle\DataGridBundle\Datasource\ResultRecord;
use PhpSpec\ObjectBehavior;
use Pim\Bundle\DataGridBundle\Datasource\AssociatedProductDatasource;
use Pim\Bundle\DataGridBundle\Datasource\DatasourceInterface;
use Pim\Bundle\DataGridBundle\Datasource\ParameterizableInterface;
use Pim\Component\Catalog\Model\AssociationInterface;
use Pim\Component\Catalog\Model\AssociationTypeInterface;
use Pim\Component\Catalog\Model\ProductInterface;
use Pim\Component\Catalog\Query\Filter\Operators;
use Pim\Component\Catalog\Query\ProductQueryBuilderFactoryInterface;
use Pim\Component\Catalog\Query\ProductQueryBuilderInterface;
use Pim\Component\Catalog\Query\Sorter\Directions;
use Prophecy\Argument;
use Symfony\Component\Serializer\Normalizer\NormalizerInterface;

class AssociatedProductDatasourceSpec extends ObjectBehavior
{
    public function let(
        ObjectManager $objectManager,
        ProductQueryBuilderFactoryInterface $pqbFactory,
        NormalizerInterface $productNormalizer
    ) {
        $this->beConstructedWith($objectManager, $pqbFactory, $productNormalizer);

        $this->setSortOrder(Directions::DESCENDING);
        $this->setParameters(['dataLocale' => 'a_locale']);
    }

    function it_is_initializable()
    {
        $this->shouldHaveType(AssociatedProductDatasource::class);
    }

    function it_is_a_datasource()
    {
        $this->shouldImplement(DatasourceInterface::class);
        $this->shouldImplement(ParameterizableInterface::class);
    }

    function it_throws_an_exception_when_there_is_no_current_product(
        $pqbFactory,
        Datagrid $datagrid,
        ProductQueryBuilderInterface $pqb
    ) {
        $pqbFactory->create(Argument::any())->willReturn($pqb);
        $pqb->getQueryBuilder()->shouldBeCalled();

        $this->process($datagrid, [
            'locale_code'     => 'a_locale',
            'scope_code'      => 'a_channel',
            '_per_page'       => 42,
            'current_product' => 'not a product instance',
        ]);

        $this->shouldThrow(
            InvalidObjectException::objectExpected(
                'not a product instance',
                ProductInterface::class
            )
        )->during('getResults');
    }

    function it_gets_products_sorted_by_association_status(
        $pqbFactory,
        $productNormalizer,
        Datagrid $datagrid,
        ProductQueryBuilderInterface $pqb,
        ProductQueryBuilderInterface $pqbAsso,
        ProductQueryBuilderInterface $pqbNonAsso,
        ProductInterface $currentProduct,
        ProductInterface $associatedProduct1,
        ProductInterface $associatedProduct2,
        ProductInterface $nonAssociatedProduct1,
        ProductInterface $nonAssociatedProduct2,
        Collection $associationCollection,
        AssociationInterface $association,
        AssociationTypeInterface $associationType,
        \ArrayIterator $associationIterator,
        CursorInterface $productCursor,
        CursorInterface $associatedProductCursor,
        CursorInterface $nonAssociatedProductCursor
    ) {
        $pqbFactory->create([
            'repository_parameters' => [],
            'repository_method'     => 'createQueryBuilder',
            'limit'                 => 42,
            'from'                  => 0,
            'default_locale'        => 'a_locale',
            'default_scope'         => 'a_channel',
        ])->willReturn($pqb);
        $pqb->getQueryBuilder()->shouldBeCalledTimes(1);

        $this->process($datagrid, [
            'locale_code'         => 'a_locale',
            'scope_code'          => 'a_channel',
            '_per_page'           => 42,
            'current_product'     => $currentProduct,
            'association_type_id' => '1'
        ]);

        $associatedProduct1->getIdentifier()->willReturn('associated_product_1');
        $associatedProduct1->getId()->willReturn('2');
        $associatedProduct2->getIdentifier()->willReturn('associated_product_2');
        $associatedProduct2->getId()->willReturn('3');
        $nonAssociatedProduct1->getIdentifier()->willReturn('non_associated_product_1');
        $nonAssociatedProduct1->getId()->willReturn('4');
        $nonAssociatedProduct2->getIdentifier()->willReturn('non_associated_product_2');
        $nonAssociatedProduct2->getId()->willReturn('5');

        $currentProduct->getAssociations()->willReturn($associationCollection);
        $currentProduct->getIdentifier()->willReturn('current_product');

        $associationCollection->getIterator()->willReturn($associationIterator);
        $associationIterator->rewind()->shouldBeCalled();
        $associationIterator->valid()->willReturn(true, false);
        $associationIterator->current()->willReturn($association);
        $associationIterator->next()->shouldBeCalled();

        $association->getProducts()->willReturn([$associatedProduct1, $associatedProduct2]);
        $association->getAssociationType()->willReturn($associationType);
        $associationType->getId()->willReturn(1);

        $pqb->addFilter('identifier', Operators::NOT_EQUAL, 'current_product')->shouldBeCalled();
        $pqb->execute()->willReturn($productCursor);
        $productCursor->count()->willReturn(4);

        $pqb->getRawFilters()->shouldBeCalledTimes(2)->willReturn([[
            'field'    => 'identifier',
            'operator' => Operators::NOT_EQUAL,
            'value'    => 'current_product',
        ]]);

        $pqbFactory->create([
            'repository_parameters' => [],
            'repository_method'     => 'createQueryBuilder',
            'limit'                 => 42,
            'from'                  => 0,
            'default_locale'        => 'a_locale',
            'default_scope'         => 'a_channel',
            'filters'               => [[
                'field'    => 'identifier',
                'operator' => Operators::NOT_EQUAL,
                'value'    => 'current_product',
            ]],
        ])->willReturn($pqbAsso);
        $pqbAsso
            ->addFilter(
                'identifier',
                Operators::IN_LIST,
                ['associated_product_1', 'associated_product_2']
            )->shouldBeCalled();
        $pqbAsso->execute()->willReturn($associatedProductCursor);

        $associatedProductCursor->rewind()->shouldBeCalled();
        $associatedProductCursor->valid()->willReturn(true, true, false);
        $associatedProductCursor->current()->willReturn($associatedProduct1, $associatedProduct2);
        $associatedProductCursor->next()->shouldBeCalled();

        $pqbFactory->create([
            'repository_parameters' => [],
            'repository_method'     => 'createQueryBuilder',
            'limit'                 => 40,
            'from'                  => 0,
            'default_locale'        => 'a_locale',
            'default_scope'         => 'a_channel',
            'filters'               => [[
                'field'    => 'identifier',
                'operator' => Operators::NOT_EQUAL,
                'value'    => 'current_product',
            ]],
        ])->willReturn($pqbNonAsso);
        $pqbNonAsso
            ->addFilter(
                'identifier',
                Operators::NOT_IN_LIST,
                ['associated_product_1', 'associated_product_2']
            )->shouldBeCalled();
        $pqbNonAsso->execute()->willReturn($nonAssociatedProductCursor);

        $nonAssociatedProductCursor->rewind()->shouldBeCalled();
        $nonAssociatedProductCursor->valid()->willReturn(true, true, false);
        $nonAssociatedProductCursor->current()->willReturn($nonAssociatedProduct1, $nonAssociatedProduct2);
        $nonAssociatedProductCursor->next()->shouldBeCalled();

        $productNormalizer->normalize($currentProduct, Argument::cetera())->shouldNotBeCalled();

        $productNormalizer->normalize($associatedProduct1, 'datagrid', [
            'locales'       => ['a_locale'],
            'channels'      => ['a_channel'],
            'data_locale'   => 'a_locale',
            'is_associated' => true,
        ])->willReturn([
            'identifier'    => 'associated_product_1',
            'family'        => null,
            'enabled'       => true,
            'values'        => [],
            'created'       => '2000-01-01',
            'updated'       => '2000-01-01',
            'is_checked'    => true,
            'is_associated' => true,
            'label'         => 'associated_product_1',
            'completeness'  => null,
        ]);

        $productNormalizer->normalize($associatedProduct2, 'datagrid', [
            'locales'       => ['a_locale'],
            'channels'      => ['a_channel'],
            'data_locale'   => 'a_locale',
            'is_associated' => true,
        ])->willReturn([
            'identifier'    => 'associated_product_2',
            'family'        => null,
            'enabled'       => true,
            'values'        => [],
            'created'       => '2000-01-01',
            'updated'       => '2000-01-01',
            'is_checked'    => true,
            'is_associated' => true,
            'label'         => 'associated_product_2',
            'completeness'  => null,
        ]);

        $productNormalizer->normalize($nonAssociatedProduct1, 'datagrid', [
            'locales'       => ['a_locale'],
            'channels'      => ['a_channel'],
            'data_locale'   => 'a_locale',
            'is_associated' => false,
        ])->willReturn([
            'identifier'    => 'non_associated_product_1',
            'family'        => null,
            'enabled'       => true,
            'values'        => [],
            'created'       => '2000-01-01',
            'updated'       => '2000-01-01',
            'is_checked'    => false,
            'is_associated' => false,
            'label'         => 'non_associated_product_1',
            'completeness'  => null,
        ]);

        $productNormalizer->normalize($nonAssociatedProduct2, 'datagrid', [
            'locales'       => ['a_locale'],
            'channels'      => ['a_channel'],
            'data_locale'   => 'a_locale',
            'is_associated' => false,
        ])->willReturn([
            'identifier'    => 'non_associated_product_2',
            'family'        => null,
            'enabled'       => true,
            'values'        => [],
            'created'       => '2000-01-01',
            'updated'       => '2000-01-01',
            'is_checked'    => false,
            'is_associated' => false,
            'label'         => 'non_associated_product_2',
            'completeness'  => null,
        ]);

        $results = $this->getResults();
        $results->shouldBeArray();
        $results->shouldHaveCount(2);
        $results->shouldHaveKey('data');
        $results->shouldHaveKeyWithValue('totalRecords', 4);
        $results['data']->shouldBeArray();
        $results['data']->shouldHaveCount(4);
        $results['data']->shouldBeAnArrayOfInstanceOf(ResultRecord::class);
    }

    public function getMatchers()
    {
        return [
            'beAnArrayOfInstanceOf' => function (array $subjects, $class) {
                foreach ($subjects as $subject) {
                    if (!$subject instanceof $class) {
                        return false;
                    }
                }

                return true;
            },
        ];
    }
}
