<?php

namespace spec\Pim\Bundle\CatalogBundle\Doctrine\MongoDBODM\Repository;

use Akeneo\Component\Classification\Repository\CategoryRepositoryInterface;
use Doctrine\MongoDB\Collection;
use Doctrine\MongoDB\Cursor;
use Doctrine\MongoDB\CursorInterface;
use Doctrine\MongoDB\Query\Query;
use Doctrine\ODM\MongoDB\DocumentManager;
use Doctrine\ODM\MongoDB\Mapping\ClassMetadata;
use Doctrine\ODM\MongoDB\Query\Builder;
use Doctrine\ODM\MongoDB\UnitOfWork;
use PhpSpec\ObjectBehavior;
use Pim\Component\Catalog\Repository\AttributeRepositoryInterface;
use Pim\Component\Catalog\Repository\FamilyRepositoryInterface;
use Pim\Component\Catalog\Repository\GroupRepositoryInterface;
use Prophecy\Argument;

/**
 * @require Doctrine\MongoDB\Collection
 * @require Doctrine\MongoDB\Cursor
 * @require Doctrine\ODM\MongoDB\DocumentManager
 * @require Doctrine\ODM\MongoDB\Mapping\ClassMetadata
 * @require Doctrine\ODM\MongoDB\UnitOfWork
 */
class ProductRepositorySpec extends ObjectBehavior
{
    function let(DocumentManager $dm, UnitOfWork $uow, ClassMetadata $class)
    {
        $class->name = 'foobar';
        $this->beConstructedWith($dm, $uow, $class);
    }

    function it_is_a_product_repository()
    {
        $this->shouldImplement('Pim\Component\Catalog\Repository\ProductRepositoryInterface');
    }

    function it_is_an_object_repository()
    {
        $this->shouldImplement('Doctrine\Common\Persistence\ObjectRepository');
    }

    function it_aggregate_attributes_to_export($dm, Collection $collection, Cursor $cursor)
    {
        $dm->getDocumentCollection('foobar')->willReturn($collection);

        $cursor->toArray()->willReturn([
            ['_id' => 'fooz'],
            ['_id' => 'baz'],
        ]);

        $collection->aggregate([
            ['$match' => ['_id' => ['$in' => [new \MongoId('55db20922a114eb9078b5130')]]]],
            ['$unwind' => '$values'],
            ['$group' => ['_id' => '$values.attribute']]
        ])->willReturn($cursor);

        $this->getAvailableAttributeIdsToExport(['55db20922a114eb9078b5130'])->shouldReturn(['fooz', 'baz']);
    }

    function it_count_all_products(DocumentManager $dm, Builder $builder, Query $query, CursorInterface $cursor)
    {
        $dm->createQueryBuilder('foobar')->willReturn($builder);
        $builder->hydrate(false)->willReturn($builder);
        $builder->getQuery()->willReturn($query);
        $query->execute()->willReturn($cursor);
        $cursor->count()->shouldBeCalled();

        $this->countAll();
    }

    function it_has_attribute_repository(AttributeRepositoryInterface $attributeRepository)
    {
        $this->setAttributeRepository($attributeRepository)->shouldReturn($this);
    }

    function it_has_category_repository(CategoryRepositoryInterface $categoryRepository)
    {
        $this->setCategoryRepository($categoryRepository)->shouldReturn($this);
    }

    function it_has_family_repository(FamilyRepositoryInterface $familyRepository)
    {
        $this->setFamilyRepository($familyRepository)->shouldReturn($this);
    }

    function it_has_group_repository(GroupRepositoryInterface $groupRepository)
    {
        $this->setGroupRepository($groupRepository)->shouldReturn($this);
    }

    function it_checks_if_the_product_has_an_attribute_in_its_variant_group(
        GroupRepositoryInterface $groupRepository,
        DocumentManager $dm,
        Builder $builder,
        Query $query
    ) {
        $this->setGroupRepository($groupRepository);

        $dm->createQueryBuilder('foobar')->willReturn($builder);
        $builder->field('_id')->willReturn($builder);
        $builder->equals(10)->willReturn($builder);
        $builder->hydrate(false)->willReturn($builder);
        $builder->getQuery()->willReturn($query);

        $query->getSingleResult()->willReturn([
            'groupIds' => [10, 11]
        ]);

        $groupRepository->hasAttribute([10, 11], 'attribute_code')->willReturn(true);

        $this->hasAttributeInVariantGroup(10, 'attribute_code')->shouldReturn(true);
    }

    function it_checks_if_the_product_has_an_attribute_in_its_variant_group_but_it_doesnt_have_one(
        GroupRepositoryInterface $groupRepository,
        DocumentManager $dm,
        Builder $builder,
        Query $query
    ) {
        $this->setGroupRepository($groupRepository);

        $dm->createQueryBuilder('foobar')->willReturn($builder);
        $builder->field('_id')->willReturn($builder);
        $builder->equals(10)->willReturn($builder);
        $builder->hydrate(false)->willReturn($builder);
        $builder->getQuery()->willReturn($query);

        $query->getSingleResult()->willReturn([
            'groupIds' => null
        ]);

        $groupRepository->hasAttribute([10, 11], 'attribute_code')->willReturn(false);

        $this->hasAttributeInVariantGroup(10, 'attribute_code')->shouldReturn(false);
    }

    function it_checks_if_the_product_has_an_attribute_in_its_family(
        FamilyRepositoryInterface $familyRepository,
        DocumentManager $dm,
        Builder $builder,
        Query $query
    ) {
        $this->setFamilyRepository($familyRepository);

        $dm->createQueryBuilder('foobar')->willReturn($builder);
        $builder->field('_id')->willReturn($builder);
        $builder->equals(10)->willReturn($builder);
        $builder->hydrate(false)->willReturn($builder);
        $builder->getQuery()->willReturn($query);

        $query->getSingleResult()->willReturn([
            'family' => 10
        ]);

        $familyRepository->hasAttribute(10, 'attribute_code')->willReturn(true);

        $this->hasAttributeInFamily(10, 'attribute_code')->shouldReturn(true);
    }

    function it_checks_if_the_product_has_an_attribute_in_its_family_but_it_does_not_have_one(
        FamilyRepositoryInterface $familyRepository,
        DocumentManager $dm,
        Builder $builder,
        Query $query
    ) {
        $this->setFamilyRepository($familyRepository);

        $dm->createQueryBuilder('foobar')->willReturn($builder);
        $builder->field('_id')->willReturn($builder);
        $builder->equals(10)->willReturn($builder);
        $builder->hydrate(false)->willReturn($builder);
        $builder->getQuery()->willReturn($query);

        $query->getSingleResult()->willReturn([]);

        $familyRepository->hasAttribute(Argument::cetera())->shouldNotBeCalled();

        $this->hasAttributeInFamily(10, 'attribute_code')->shouldReturn(false);
    }
}
