<?php

declare(strict_types=1);

namespace Specification\Akeneo\Pim\Enrichment\Product\Infrastructure\AntiCorruptionLayer;

use Akeneo\Pim\Enrichment\Product\Domain\Model\Permission\AccessLevel;
use Akeneo\Pim\Enrichment\Product\Domain\Model\ProductIdentifier;
use Akeneo\Pim\Enrichment\Product\Domain\Query\IsUserCategoryGranted;
use Akeneo\Pim\Enrichment\Product\Infrastructure\AntiCorruptionLayer\AclIsUserCategoryGranted;
use Akeneo\Pim\Permission\Component\Query\ProductCategoryAccessQueryInterface;
use Akeneo\Test\Pim\Enrichment\Product\Helper\FeatureHelper;
use Akeneo\UserManagement\Component\Model\UserInterface;
use Akeneo\UserManagement\Component\Repository\UserRepositoryInterface;
use PhpSpec\ObjectBehavior;

class AclIsUserCategoryGrantedSpec extends ObjectBehavior
{
    function let($productCategoryAccessQuery, UserRepositoryInterface $userRepository, UserInterface $user)
    {
        FeatureHelper::skipSpecTestWhenPermissionFeatureIsNotActivated();

        $productCategoryAccessQuery->beADoubleOf(ProductCategoryAccessQueryInterface::class);
        $user->getGroupsIds()->willReturn([100, 101]);
        $userRepository->findOneBy(['id' => 1])->willReturn($user);
        $userRepository->findOneBy(['id' => 9])->willReturn(null);

        $this->beConstructedWith($productCategoryAccessQuery, $userRepository);
    }

    function it_is_initializable($productCategoryAccessQuery)
    {
        $this->shouldHaveType(AclIsUserCategoryGranted::class);
        $this->shouldImplement(IsUserCategoryGranted::class);
    }

    function it_throws_an_exception_when_permission_feature_is_not_activated(UserRepositoryInterface $userRepository)
    {
        $this->beConstructedWith(null, $userRepository);

        $this->shouldThrow(\InvalidArgumentException::class)
            ->during('forProductAndAccessLevel', [1, ProductIdentifier::fromString('foo'), AccessLevel::OWN_PRODUCTS]);
    }

    function it_throws_an_exception_when_user_is_unknown()
    {
        $this->shouldThrow(\InvalidArgumentException::class)
            ->during('forProductAndAccessLevel', [9, ProductIdentifier::fromString('foo'), AccessLevel::OWN_PRODUCTS]);
    }

    function it_returns_true_when_permission_is_granted($productCategoryAccessQuery)
    {
        $productCategoryAccessQuery->getGrantedProductIdentifiers(['foo'], [100, 101], AccessLevel::OWN_PRODUCTS)
            ->shouldBeCalledOnce()->willReturn(['foo']);

        $this->forProductAndAccessLevel(1, ProductIdentifier::fromString('foo'), AccessLevel::OWN_PRODUCTS)
            ->shouldReturn(true);
    }

    function it_returns_false_when_permission_is_not_granted($productCategoryAccessQuery)
    {
        $productCategoryAccessQuery->getGrantedProductIdentifiers(['foo'], [100, 101], AccessLevel::OWN_PRODUCTS)
            ->shouldBeCalledOnce()->willReturn([]);

        $this->forProductAndAccessLevel(1, ProductIdentifier::fromString('foo'), AccessLevel::OWN_PRODUCTS)
            ->shouldReturn(false);
    }
}
