<?php

namespace spec\Pim\Bundle\DataGridBundle\Datasource\ResultRecord\MongoDbOdm;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\MongoDB\ArrayIterator;
use Doctrine\MongoDB\Collection;
use Doctrine\ODM\MongoDB\DocumentManager;
use Doctrine\ODM\MongoDB\Mapping\ClassMetadata;
use Doctrine\ODM\MongoDB\Query\Builder;
use Doctrine\ODM\MongoDB\Query\Query;
use PhpSpec\ObjectBehavior;
use Pim\Bundle\CatalogBundle\Entity\AssociationType;
use Pim\Bundle\CatalogBundle\Model\Association;
use Pim\Bundle\CatalogBundle\Model\ProductInterface;
use Prophecy\Argument;
use Prophecy\Promise\ReturnPromise;

/**
 * @require Doctrine\ODM\MongoDB\Query\Builder
 */
class AssociatedProductHydratorSpec extends ObjectBehavior
{
    public function let(ProductInterface $productClass)
    {
        $this->beConstructedWith(get_class($productClass));
    }

    function it_is_initializable()
    {
        $this->shouldHaveType('Pim\Bundle\DataGridBundle\Datasource\ResultRecord\MongoDbOdm\AssociatedProductHydrator');
    }

    function it_is_a_hydrator()
    {
        $this->shouldImplement('Pim\Bundle\DataGridBundle\Datasource\ResultRecord\HydratorInterface');
    }

    function it_hydrates_a_result_record(
        Builder $builder,
        Query $query,
        ProductInterface $product,
        Association $association,
        AssociationType $associationType,
        ProductInterface $associatedProduct1,
        ProductInterface $associatedProduct2,
        ArrayCollection $productsCollection,
        ArrayCollection $productIdsCollection,
        ArrayIterator $productsIterator,
        ArrayCollection $associationsCollection,
        ArrayIterator $associationsIterator,
        ArrayIterator $arrayIterator
    ) {
        $product->getId()->willReturn('110ae6b98ead0ee8778b46bb');

        $options = [
            'locale_code'              => 'en_US',
            'scope_code'               => 'print',
            'current_group_id'         => null,
            'attributes_configuration' => [],
            'association_type_id'      => 1,
            'current_product'          => $product,
        ];

        $builder->find()->willReturn($builder);
        $builder->count()->willReturn($builder);
        $builder->getQuery()->willReturn($query);
        $builder->hydrate(false)->willReturn($builder);
        $builder->setQueryArray(Argument::any())->willReturn($builder);
        $builder->limit(Argument::any())->willReturn($builder);
        $builder->skip(Argument::any())->willReturn($builder);

        $product->getAssociations()->willReturn($associationsCollection);
        $associationsCollection->getIterator()->willReturn($associationsIterator);
        $associationsIterator->rewind()->shouldBeCalled();
        $associationsCount = 1;
        $associationsIterator->valid()->will(
            function () use (&$associationsCount) {
                return $associationsCount-- > 0;
            }
        );
        $associationsIterator->next()->shouldBeCalled();
        $associationsIterator->current()->will(new ReturnPromise([$association]));
        $associationsCollection->filter(Argument::any())->willReturn($associationsIterator);
        $associationsIterator->first()->willReturn($association);

        $association->getAssociationType()->willReturn($associationType);
        $associationType->getId()->willReturn(1);

        $associatedProduct1->getId()->willReturn('220ae6b98ead0ed7778b46bb');
        $associatedProduct2->getId()->willReturn('330ae6b98abd0ec8778b46bb');
        $association->getProducts()->willReturn($productsCollection);
        $productsCollection->getIterator()->willReturn($productsIterator);
        $productsIterator->rewind()->shouldBeCalled();
        $productsCount = 2;
        $productsIterator->valid()->will(
            function () use (&$productsCount) {
                return $productsCount-- > 0;
            }
        );
        $productsIterator->next()->shouldBeCalled();
        $productsIterator->current()->will(new ReturnPromise([$associatedProduct1, $associatedProduct2]));
        $productsCollection->map(Argument::any())->willReturn($productIdsCollection);
        $productIdsCollection->toArray()->willReturn([
            '220ae6b98ead0ed7778b46bb',
            '330ae6b98abd0ec8778b46bb'
        ]);

        $queryDefinition = [
            'type'   => 1,
            'sort'   => [
                'normalizedData.is_associated' => -1,
                '_id'                          => 1,
            ],
            'limit'  => 10,
            'skip'   => 0,
            'query'  => [
                '_id' => [
                    '$ne' => \MongoId::__set_state(['$id' => '110ae6b98ead0ee8778b46bb']),
                ],
            ],
            'newObj' => [],
        ];

        $query->getQuery()->willReturn($queryDefinition);

        $fixture = [
            '_id'            => \MongoId::__set_state(['$id' => '550ae6b98ead0ee8778b46bb']),
            'normalizedData' => [],
            'sku'            => [
                'attribute' => ['code' => 'sku', 'attributeType' => 'text', 'backendType' => 'text'],
                'locale'    => null,
                'scope'     => null,
                'value'     => 'mysku',
            ],
            'name'           => [
                'attribute' => ['code' => 'name', 'attributeType' => 'text', 'backendType' => 'text'],
                'locale'    => 'fr_FR',
                'scope'     => null
            ],
            'desc'           => [
                'attribute' => ['code' => 'desc', 'attributeType' => 'text', 'backendType' => 'text'],
                'locale'    => 'fr_FR',
                'scope'     => 'print'
            ],
            'is_associated'  => 1,
        ];

        $query->execute()->willReturn($arrayIterator);
        $arrayIterator->toArray()->willReturn([$fixture]);

        $rows = $this->hydrate($builder, $options);
        $rows->shouldHaveCount(1);

        $firstResult = $rows[0];
        $firstResult->shouldBeAnInstanceOf('\Oro\Bundle\DataGridBundle\Datasource\ResultRecord');
    }
}
