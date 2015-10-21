<?php

namespace spec\Pim\Bundle\DataGridBundle\Datasource\ResultRecord\MongoDbOdm;

use Doctrine\MongoDB\ArrayIterator;
use Doctrine\MongoDB\Collection;
use Doctrine\ODM\MongoDB\DocumentManager;
use Doctrine\ODM\MongoDB\Query\Builder;
use Doctrine\ODM\MongoDB\Query\Query;
use PhpSpec\ObjectBehavior;
use Pim\Bundle\CatalogBundle\Entity\AssociationType;
use Pim\Bundle\CatalogBundle\Model\Association;
use Pim\Component\Catalog\Model\ProductInterface;
use Prophecy\Argument;

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
        DocumentManager $documentManager,
        \Doctrine\ODM\MongoDB\Mapping\ClassMetadata $metadata,
        Collection $collection,
        ArrayIterator $arrayIterator
    ) {
        $options = [
            'locale_code'              => 'en_US',
            'scope_code'               => 'print',
            'current_group_id'         => null,
            'attributes_configuration' => [],
            'association_type_id'      => 1,
            'current_product'          => $product,
        ];

        $builder->getQuery()->willReturn($query);
        $builder->hydrate(false)->willReturn($builder);

        $associatedProduct1->getId()->willReturn('550ae6b98ead0ed7778b46bb');
        $associatedProduct2->getId()->willReturn('550ae6b98abd0ec8778b46bb');

        $product->getAssociations()->willReturn([$association]);

        $association->getAssociationType()->willReturn($associationType);
        $associationType->getId()->willReturn(1);

        $association->getProducts()->willReturn([
            $associatedProduct1,
            $associatedProduct2,
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
                    '$ne' => \MongoId::__set_state(['$id' => '550ae6b98ead0ee8778b46bb']),
                ],
            ],
            'newObj' => [],
        ];

        $query->getQuery()->willReturn($queryDefinition);

        $query->getDocumentManager()->willReturn($documentManager);
        $documentManager->getDocumentCollection(Argument::any())->willReturn($collection);
        $documentManager->getClassMetadata(Argument::any())->willReturn($metadata);
        $metadata->getFieldNames()->willReturn([
            'id',
            'created',
            'updated',
            'locale',
            'scope',
            'values',
            'indexedValues',
            'indexedValuesOutdated',
            'family',
            'familyId',
            'categories',
            'categoryIds',
            'enabled',
            'groups',
            'groupIds',
            'associations',
            'completenesses',
            'normalizedData',
        ]);

        $pipeline = [
            [
                '$match' => [
                    '_id' => [
                        '$ne' => \MongoId::__set_state(['$id' => '550ae6b98ead0ee8778b46bb']),
                    ],
                ],
            ],
            [
                '$project' => [
                    'id'                    => 1,
                    'created'               => 1,
                    'updated'               => 1,
                    'locale'                => 1,
                    'scope'                 => 1,
                    'values'                => 1,
                    'indexedValues'         => 1,
                    'indexedValuesOutdated' => 1,
                    'family'                => 1,
                    'familyId'              => 1,
                    'categories'            => 1,
                    'categoryIds'           => 1,
                    'enabled'               => 1,
                    'groups'                => 1,
                    'groupIds'              => 1,
                    'associations'          => 1,
                    'completenesses'        => 1,
                    'normalizedData'        => 1,
                    'is_associated'         => [
                        '$cond' => [
                            [
                                '$or' => [
                                    [
                                        '$eq' => [
                                            '$_id',
                                            \MongoId::__set_state(['$id' => '550ae6b98ead0ed7778b46bb']),
                                        ],
                                    ],
                                    [
                                        '$eq' => [
                                            '$_id',
                                            \MongoId::__set_state(['$id' => '550ae6b98abd0ec8778b46bb']),
                                        ],
                                    ],
                                ],
                            ],
                            1,
                            0,
                        ],
                    ],
                ],
            ],
            [
                '$sort' => [
                    'is_associated' => -1,
                ]
            ],
            ['$skip' => 0],
            ['$limit' => 10],
        ];

        $collection->aggregate($pipeline)
            ->willReturn($arrayIterator);

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

        $arrayIterator->toArray()->willReturn([$fixture]);

        $rows = $this->hydrate($builder, $options);
        $rows->shouldHaveCount(1);

        $firstResult = $rows[0];
        $firstResult->shouldBeAnInstanceOf('\Oro\Bundle\DataGridBundle\Datasource\ResultRecord');
    }
}
