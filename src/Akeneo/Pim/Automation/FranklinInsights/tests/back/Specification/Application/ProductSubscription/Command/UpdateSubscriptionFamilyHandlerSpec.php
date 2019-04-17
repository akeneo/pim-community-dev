<?php

declare(strict_types=1);

namespace Specification\Akeneo\Pim\Automation\FranklinInsights\Application\ProductSubscription\Command;

use Akeneo\Pim\Automation\FranklinInsights\Application\DataProvider\SubscriptionProviderInterface;
use Akeneo\Pim\Automation\FranklinInsights\Application\ProductSubscription\Command\UpdateSubscriptionFamilyCommand;
use Akeneo\Pim\Automation\FranklinInsights\Application\ProductSubscription\Command\UpdateSubscriptionFamilyHandler;
use Akeneo\Pim\Automation\FranklinInsights\Domain\Common\Model\Read\Family;
use Akeneo\Pim\Automation\FranklinInsights\Domain\Common\Repository\FamilyRepositoryInterface;
use Akeneo\Pim\Automation\FranklinInsights\Domain\Common\ValueObject\FamilyCode;
use Akeneo\Pim\Automation\FranklinInsights\Domain\Common\ValueObject\ProductId;
use Akeneo\Pim\Automation\FranklinInsights\Domain\Subscription\Exception\ProductNotSubscribedException;
use Akeneo\Pim\Automation\FranklinInsights\Domain\Subscription\Model\ProductSubscription;
use Akeneo\Pim\Automation\FranklinInsights\Domain\Subscription\Repository\ProductSubscriptionRepositoryInterface;
use Akeneo\Pim\Automation\FranklinInsights\Domain\Subscription\ValueObject\SubscriptionId;
use PhpSpec\ObjectBehavior;
use Prophecy\Argument;

class UpdateSubscriptionFamilyHandlerSpec extends ObjectBehavior
{
    public function let(
        ProductSubscriptionRepositoryInterface $productSubscriptionRepository,
        SubscriptionProviderInterface $subscriptionProvider,
        FamilyRepositoryInterface $familyRepository
    ): void {
        $this->beConstructedWith($productSubscriptionRepository, $subscriptionProvider, $familyRepository);
    }

    public function it_is_an_update_subscription_family_handler(): void
    {
        $this->shouldHaveType(UpdateSubscriptionFamilyHandler::class);
    }

    public function it_throws_an_exception_if_the_product_is_not_subscribed(
        $productSubscriptionRepository,
        $subscriptionProvider
    ): void {
        $productId = new ProductId(42);
        $productSubscriptionRepository->findOneByProductId($productId)->willReturn(null);
        $subscriptionProvider->updateFamilyInfos(Argument::cetera())->shouldNotBeCalled();

        $this->shouldThrow(ProductNotSubscribedException::notSubscribed())->during(
            'handle',
            [
                new UpdateSubscriptionFamilyCommand($productId, new FamilyCode('router')),
            ]
        );
    }

    public function it_updates_family_infos_for_a_subscribed_product(
        $productSubscriptionRepository,
        $subscriptionProvider,
        FamilyRepositoryInterface $familyRepository
    ): void {
        $productId = new ProductId(42);
        $productSubscriptionRepository->findOneByProductId($productId)->willReturn(
            new ProductSubscription($productId, new SubscriptionId('123456-abcdef'), [])
        );

        $familyCode = new FamilyCode('router');
        $family = new Family($familyCode, []);
        $familyRepository->findOneByIdentifier($familyCode)->willReturn($family);

        $subscriptionProvider->updateFamilyInfos('123456-abcdef', $family)->shouldBeCalled();

        $this->handle(new UpdateSubscriptionFamilyCommand($productId, $familyCode));
    }
}
