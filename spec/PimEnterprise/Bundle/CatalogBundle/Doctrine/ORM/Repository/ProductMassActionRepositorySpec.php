<?php

namespace spec\PimEnterprise\Bundle\CatalogBundle\Doctrine\ORM\Repository;

use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\AbstractQuery;
use Doctrine\ORM\EntityManager;
use Doctrine\ORM\Query\Expr;
use Doctrine\ORM\QueryBuilder;
use Oro\Bundle\UserBundle\Entity\Group;
use PhpSpec\ObjectBehavior;
use Pim\Bundle\UserBundle\Entity\UserInterface;
use Pim\Component\Catalog\Model\ProductInterface;
use PimEnterprise\Bundle\WorkflowBundle\Doctrine\ORM\Repository\PublishedProductRepository;
use Prophecy\Argument;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;

class ProductMassActionRepositorySpec extends ObjectBehavior
{
    function let(
        EntityManager $em,
        PublishedProductRepository $publishedRepository,
        TokenStorageInterface $tokenStorage
    ) {
        $this->beConstructedWith(
            $em,
            Argument::any(),
            $publishedRepository,
            $tokenStorage,
            'category_access.class'
        );
    }

    function it_returns_the_number_of_products_deleted_that_the_current_user_owned(
        $publishedRepository,
        $em,
        $tokenStorage,
        Group $redactorGroup,
        Group $userGroup,
        QueryBuilder $qb,
        Expr $expr,
        Expr\Orx $orX,
        Expr\Andx $andX,
        Expr\Comparison $comparison,
        Expr\Func $inProd,
        Expr\Func $inProdPermission,
        Expr\Func $inUserGroup,
        AbstractQuery $query,
        TokenInterface $token,
        UserInterface $user,
        Collection $groupCollection
    ) {
        $productIds = [1, 2];
        $publishedRepository->getProductIdsMapping($productIds)->willReturn([]);

        $em->createQueryBuilder()->willReturn($qb);
        $qb->expr()->willReturn($expr);

        $qb->select('product.id')->willReturn($qb);
        $qb->distinct(true)->willReturn($qb);
        $qb->from(ProductInterface::class, 'product')->willReturn($qb);
        $qb->leftJoin('product.categories', 'prodCategory')->willReturn($qb);
        $qb->leftJoin(
            'category_access.class',
            'catAccess',
            Expr\Join::WITH,
            'catAccess.category = prodCategory.id'
        )->willReturn($qb);

        $expr->in('product.id', ':ids')->willReturn($inProdPermission);
        $qb->where($inProdPermission)->willReturn($qb);
        $qb->setParameter('ids', $productIds)->willReturn($qb);

        $expr->isNull('prodCategory.id')->willReturn('prodCategory.id IS NULL');
        $expr->eq('catAccess.ownItems', true)->willReturn($comparison);
        $expr->in('catAccess.userGroup', ':userGroupIds')->willReturn($inUserGroup);
        $expr->andX($comparison, $inUserGroup)->willReturn($andX);
        $expr->orX('prodCategory.id IS NULL', $andX)->willReturn($orX);

        $qb->andWhere($orX)->willReturn($qb);
        $qb->setParameter('userGroupIds', [10, 11])->willReturn($qb);

        $tokenStorage->getToken()->willReturn($token);
        $token->getUser()->willReturn($user);
        $user->getGroups()->willReturn($groupCollection);
        $groupCollection->toArray()->willReturn([$redactorGroup, $userGroup]);

        $redactorGroup->getId()->willReturn(10);
        $userGroup->getId()->willReturn(11);

        $qb->getQuery()->willReturn($query);
        $query->getScalarResult()->willReturn([['id' => 1], ['id' => 2]]);

        $qb->delete(Argument::any(), 'p')->willReturn($qb);
        $expr->in('p.id', $productIds)->willReturn($inProd);
        $qb->where($inProd)->willReturn($qb);

        $query->execute()->willReturn(2);

        $this->deleteFromIds($productIds)->shouldReturn(2);
    }

    function it_throws_an_exception_if_there_is_a_product_published($publishedRepository)
    {
        $ids = [1, 2];
        $publishedRepository->getProductIdsMapping($ids)->willReturn([1]);

        $this
            ->shouldThrow(
                new \Exception(
                    'Impossible to mass delete products. You should not have any published products in your selection.'
                )
            )
            ->duringDeleteFromIds($ids);
    }

    function it_throws_an_exception_if_there_is_a_product_the_user_does_not_own(
        $publishedRepository,
        $em,
        $tokenStorage,
        Group $redactorGroup,
        Group $userGroup,
        QueryBuilder $qb,
        Expr $expr,
        Expr\Orx $orX,
        Expr\Andx $andX,
        Expr\Comparison $comparison,
        Expr\Func $inProd,
        Expr\Func $inUserGroup,
        AbstractQuery $query,
        TokenInterface $token,
        UserInterface $user,
        Collection $groupCollection
    ) {
        $productIds = [1, 2];
        $publishedRepository->getProductIdsMapping($productIds)->willReturn([]);

        $em->createQueryBuilder()->willReturn($qb);
        $qb->expr()->willReturn($expr);

        $qb->select('product.id')->willReturn($qb);
        $qb->distinct(true)->willReturn($qb);
        $qb->from(ProductInterface::class, 'product')->willReturn($qb);
        $qb->leftJoin('product.categories', 'prodCategory')->willReturn($qb);
        $qb->leftJoin(
            'category_access.class',
            'catAccess',
            Expr\Join::WITH,
            'catAccess.category = prodCategory.id'
        )->willReturn($qb);

        $expr->in('product.id', ':ids')->willReturn($inProd);
        $qb->where($inProd)->willReturn($qb);
        $qb->setParameter('ids', $productIds)->willReturn($qb);

        $expr->isNull('prodCategory.id')->willReturn('prodCategory.id IS NULL');
        $expr->eq('catAccess.ownItems', true)->willReturn($comparison);
        $expr->in('catAccess.userGroup', ':userGroupIds')->willReturn($inUserGroup);
        $expr->andX($comparison, $inUserGroup)->willReturn($andX);
        $expr->orX('prodCategory.id IS NULL', $andX)->willReturn($orX);

        $qb->andWhere($orX)->willReturn($qb);
        $qb->setParameter('userGroupIds', [10, 11])->willReturn($qb);

        $tokenStorage->getToken()->willReturn($token);
        $token->getUser()->willReturn($user);
        $user->getGroups()->willReturn($groupCollection);
        $groupCollection->toArray()->willReturn([$redactorGroup, $userGroup]);

        $redactorGroup->getId()->willReturn(10);
        $userGroup->getId()->willReturn(11);

        $qb->getQuery()->willReturn($query);
        $query->getScalarResult()->willReturn([]);

        $this
            ->shouldThrow(
                new \Exception(
                    'Impossible to mass delete products. To be deleted, all the products of your selection ' .
                    'should be categorized in at least one category that you own.'
                )
            )
            ->duringDeleteFromIds($productIds);
    }
}
