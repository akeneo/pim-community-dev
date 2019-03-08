<?php

namespace spec\Oro\Bundle\PimDataGridBundle\Datasource;

use Akeneo\Pim\Enrichment\Component\Product\Model\AssociationInterface;
use Akeneo\Pim\Enrichment\Component\Product\Model\ProductInterface;
use Akeneo\Pim\Enrichment\Component\Product\Model\ProductModelInterface;
use Akeneo\Pim\Structure\Component\Model\AssociationTypeInterface;
use Akeneo\Tool\Component\StorageUtils\Cursor\CursorInterface;
use Akeneo\Tool\Component\StorageUtils\Exception\InvalidObjectException;
use Doctrine\Common\Collections\Collection;
use Doctrine\Common\Persistence\ObjectManager;
use Oro\Bundle\DataGridBundle\Datagrid\Datagrid;
use Oro\Bundle\DataGridBundle\Datasource\ResultRecord;
use PhpSpec\ObjectBehavior;
use Oro\Bundle\PimDataGridBundle\Datasource\AssociatedProductModelDatasource;
use Oro\Bundle\PimDataGridBundle\Datasource\DatasourceInterface;
use Oro\Bundle\PimDataGridBundle\Datasource\ParameterizableInterface;
use Oro\Bundle\PimDataGridBundle\EventSubscriber\FilterEntityWithValuesSubscriber;
use Akeneo\Pim\Enrichment\Component\Product\Query\Filter\Operators;
use Akeneo\Pim\Enrichment\Component\Product\Query\ProductQueryBuilderFactoryInterface;
use Akeneo\Pim\Enrichment\Component\Product\Query\ProductQueryBuilderInterface;
use Akeneo\Pim\Enrichment\Component\Product\Query\Sorter\Directions;
use Prophecy\Argument;
use Symfony\Component\Serializer\Normalizer\NormalizerInterface;

class AssociatedProductModelDatasourceSpec extends ObjectBehavior
{
    function let(
        ObjectManager $objectManager,
        ProductQueryBuilderFactoryInterface $pqbFactory,
        NormalizerInterface $productNormalizer,
        FilterEntityWithValuesSubscriber $subscriber
    ) {
        $this->beConstructedWith($objectManager, $pqbFactory, $productNormalizer, $subscriber);

        $this->setSortOrder(Directions::DESCENDING);
        $this->setParameters(['dataLocale' => 'a_locale']);
    }

    function it_is_initializable()
    {
        $this->shouldHaveType(AssociatedProductModelDatasource::class);
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
                ProductModelInterface::class
            )
        )->during('getResults');
    }

    function it_gets_product_models_sorted_by_association_status(
        $pqbFactory,
        $productNormalizer,
        Datagrid $datagrid,
        ProductQueryBuilderInterface $pqb,
        ProductQueryBuilderInterface $pqbAsso,
        ProductQueryBuilderInterface $pqbAssoProductModel,
        ProductModelInterface $currentProduct,
        ProductModelInterface $parent,
        ProductInterface $associatedProduct1,
        ProductInterface $associatedProduct2,
        ProductModelInterface $associatedProductModel,
        Collection $associationCollection,
        Collection $parentAssociationCollection,
        AssociationInterface $association,
        AssociationInterface $parentAssociation,
        AssociationTypeInterface $associationType,
        \ArrayIterator $associationIterator,
        \ArrayIterator $parentAssociationIterator,
        CursorInterface $productCursor,
        CursorInterface $associatedProductCursor,
        CursorInterface $associatedProductModelCursor,
        Collection $collectionProductModel,
        Collection $parentCollectionProductModel,
        \Iterator $collectionProductModelIterator,
        \Iterator $parentCollectionProductModelIterator
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
        $associatedProductModel->getCode()->willReturn('associated_product_model_1');
        $associatedProductModel->getId()->willReturn('2');
        $currentProduct->getAllAssociations()->willReturn($associationCollection);
        $currentProduct->getParent()->willReturn($parent);

        $parent->getAllAssociations()->willReturn($parentAssociationCollection);

        $parentAssociationCollection->getIterator()->willReturn($parentAssociationIterator);
        $parentAssociationIterator->rewind()->shouldBeCalled();
        $parentAssociationIterator->valid()->willReturn(true, false);
        $parentAssociationIterator->current()->willReturn($parentAssociation);

        $associationCollection->getIterator()->willReturn($associationIterator);
        $associationIterator->rewind()->shouldBeCalled();
        $associationIterator->valid()->willReturn(true, false);
        $associationIterator->current()->willReturn($association);

        $association->getProducts()->willReturn([$associatedProduct1, $associatedProduct2]);
        $parentAssociation->getProducts()->willReturn([$associatedProduct2]);
        $association->getProductModels()->willReturn($collectionProductModel);
        $parentAssociation->getProductModels()->willReturn($parentCollectionProductModel);

        $collectionProductModel->getIterator()->willReturn($collectionProductModelIterator);
        $collectionProductModelIterator->rewind()->shouldBeCalled();
        $collectionProductModelIterator->valid()->willReturn(true, false);
        $collectionProductModelIterator->current()->willReturn($associatedProductModel);
        $collectionProductModelIterator->next()->shouldBeCalled();

        $parentCollectionProductModel->getIterator()->willReturn($parentCollectionProductModelIterator);
        $parentCollectionProductModelIterator->rewind()->shouldBeCalled();
        $parentCollectionProductModelIterator->valid()->willReturn(false);

        $association->getAssociationType()->willReturn($associationType);
        $parentAssociation->getAssociationType()->willReturn($associationType);
        $associationType->getId()->willReturn(1);

        $pqb->execute()->willReturn($productCursor);
        $productCursor->count()->willReturn(2);

        $pqb->getRawFilters()->shouldBeCalledTimes(2)->willReturn(null);

        $pqbFactory->create([
            'repository_parameters' => [],
            'repository_method'     => 'createQueryBuilder',
            'limit'                 => 42,
            'from'                  => 0,
            'default_locale'        => 'a_locale',
            'default_scope'         => 'a_channel',
            'filters'               => null,
        ])->willReturn($pqbAsso);

        $pqbAsso
            ->addFilter(
                'identifier',
                Operators::IN_LIST,
                ['associated_product_1', 'associated_product_2']
            )->shouldBeCalled();
        $pqbAsso
            ->addFilter(
                'entity_type',
                Operators::EQUALS,
                ProductInterface::class
            )->shouldBeCalled();
        $pqbAsso->execute()->willReturn($associatedProductCursor);

        $associatedProductCursor->rewind()->shouldBeCalled();
        $associatedProductCursor->valid()->willReturn(true, true, false);
        $associatedProductCursor->current()->willReturn($associatedProduct1, $associatedProduct2);
        $associatedProductCursor->next()->shouldBeCalled();
        $associatedProductCursor->count()->willReturn(2);

        $pqbFactory->create([
            'repository_parameters' => [],
            'repository_method'     => 'createQueryBuilder',
            'limit'                 => 40,
            'from'                  => 0,
            'default_locale'        => 'a_locale',
            'default_scope'         => 'a_channel',
            'filters'               => null,
        ])->willReturn($pqbAssoProductModel);

        $pqbAssoProductModel
            ->addFilter(
                'identifier',
                Operators::IN_LIST,
                ['associated_product_model_1']
            )->shouldBeCalled();
        $pqbAssoProductModel
            ->addFilter(
                'entity_type',
                Operators::EQUALS,
                ProductModelInterface::class
            )->shouldBeCalled();
        $pqbAssoProductModel->execute()->willReturn($associatedProductModelCursor);

        $associatedProductModelCursor->rewind()->shouldBeCalled();
        $associatedProductModelCursor->valid()->willReturn(true, false);
        $associatedProductModelCursor->current()->willReturn($associatedProductModel);
        $associatedProductModelCursor->next()->shouldBeCalled();
        $associatedProductModelCursor->count()->willReturn(1);

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
            'from_inheritance' => false,
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
            'from_inheritance' => true,
        ]);

        $productNormalizer->normalize($associatedProductModel, 'datagrid', [
            'locales'       => ['a_locale'],
            'channels'      => ['a_channel'],
            'data_locale'   => 'a_locale',
            'is_associated' => true,
        ])->willReturn([
            'identifier'    => 'associated_product_model_1',
            'family'        => null,
            'enabled'       => true,
            'values'        => [],
            'created'       => '2000-01-01',
            'updated'       => '2000-01-01',
            'is_checked'    => true,
            'is_associated' => true,
            'label'         => 'associated_product_model_1',
            'completeness'  => null,
        ]);

        $results = $this->getResults();
        $results->shouldBeArray();
        $results->shouldHaveCount(2);
        $results->shouldHaveKey('data');
        $results->shouldHaveKeyWithValue('totalRecords', 3);
        $results['data']->shouldBeArray();
        $results['data']->shouldHaveCount(3);
        $results['data']->shouldBeAnArrayOfInstanceOf(ResultRecord::class);
        $results['data'][0]->getValue('id')->shouldReturn('product-2');
        $results['data'][1]->getValue('id')->shouldReturn('product-3');
        $results['data'][2]->getValue('id')->shouldReturn('product-model-2');
    }

    public function getMatchers(): array
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
