<?php

declare(strict_types=1);

namespace Specification\Akeneo\Pim\Enrichment\Product\Infrastructure\AntiCorruptionLayer;

use Akeneo\Pim\Enrichment\Product\Domain\Model\Permission\AccessLevel;
use Akeneo\Pim\Enrichment\Product\Domain\Model\ProductIdentifier;
use Akeneo\Pim\Enrichment\Product\Domain\Query\IsUserCategoryGranted;
use Akeneo\Pim\Enrichment\Product\Infrastructure\AntiCorruptionLayer\AclIsUserCategoryGranted;
use Akeneo\Pim\Permission\Component\Query\ProductCategoryAccessQueryInterface;
use Akeneo\Test\Pim\Enrichment\Product\Helper\FeatureHelper;
use PhpSpec\ObjectBehavior;

class AclIsUserCategoryGrantedSpec extends ObjectBehavior
{
    function let($productCategoryAccessQuery)
    {
        FeatureHelper::skipSpecTestWhenPermissionFeatrureIsNotActivated();

        $productCategoryAccessQuery->beADoubleOf(ProductCategoryAccessQueryInterface::class);
    }

    function it_is_initializable($productCategoryAccessQuery)
    {
        $this->beConstructedWith($productCategoryAccessQuery);

        $this->shouldHaveType(AclIsUserCategoryGranted::class);
        $this->shouldImplement(IsUserCategoryGranted::class);
    }

    function it_throws_an_exception_when_permission_feature_is_not_activated()
    {
        $this->beConstructedWith(null);

        $this->shouldThrow(\InvalidArgumentException::class)
            ->during('forProductAndAccessLevel', [1, ProductIdentifier::fromString('foo'), AccessLevel::OWN_PRODUCTS]);
    }

    function it_returns_true_when_permission_is_granted($productCategoryAccessQuery)
    {
        $this->beConstructedWith($productCategoryAccessQuery);

        $productCategoryAccessQuery->getGrantedProductIdentifiers(['foo'], 1, AccessLevel::OWN_PRODUCTS)
            ->willReturn(['foo']);

        $this->forProductAndAccessLevel(1, ProductIdentifier::fromString('foo'), AccessLevel::OWN_PRODUCTS)
            ->shouldReturn(true);
    }

    function it_returns_false_when_permission_is_not_granted($productCategoryAccessQuery)
    {
        $this->beConstructedWith($productCategoryAccessQuery);

        $productCategoryAccessQuery->getGrantedProductIdentifiers(['foo'], 1, AccessLevel::OWN_PRODUCTS)
            ->willReturn([]);

        $this->forProductAndAccessLevel(1, ProductIdentifier::fromString('foo'), AccessLevel::OWN_PRODUCTS)
            ->shouldReturn(false);
    }
}
