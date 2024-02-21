<?php

declare(strict_types=1);

namespace spec\Akeneo\Connectivity\Connection\Infrastructure\Marketplace\Controller\Internal;

use Akeneo\Connectivity\Connection\Application\Marketplace\AppUrlGenerator;
use Akeneo\Connectivity\Connection\Application\Marketplace\MarketplaceAnalyticsGenerator;
use Akeneo\Connectivity\Connection\Domain\Marketplace\DTO\GetAllAppsResult;
use Akeneo\Connectivity\Connection\Domain\Marketplace\GetAllAppsQueryInterface;
use Akeneo\Platform\Bundle\FeatureFlagBundle\FeatureFlag;
use Akeneo\Platform\Bundle\FrameworkBundle\Service\PimUrl;
use Akeneo\UserManagement\Bundle\Context\UserContext;
use PhpSpec\ObjectBehavior;
use PHPUnit\Framework\Assert;
use Psr\Log\LoggerInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

class GetAllAppsActionSpec extends ObjectBehavior
{
    public function let(
        GetAllAppsQueryInterface $getAllAppsQuery,
        MarketplaceAnalyticsGenerator $marketplaceAnalyticsGenerator,
        UserContext $userContext,
        LoggerInterface $logger,
        FeatureFlag $activateFeatureFlag,
    ): void {
        $this->beConstructedWith(
            new AppUrlGenerator(new PimUrl('https://some_pim_url')),
            $getAllAppsQuery,
            $marketplaceAnalyticsGenerator,
            $userContext,
            $logger,
            $activateFeatureFlag,
        );
    }

    public function it_returns_an_empty_list_when_the_marketplace_api_throws_a_bad_request_error(
        FeatureFlag $activateFeatureFlag,
        Request $request,
        GetAllAppsQueryInterface $getAllAppsQuery,
    ): void {
        $activateFeatureFlag->isEnabled()->willReturn(true);
        $request->isXmlHttpRequest()->willReturn(true);

        $getAllAppsQuery->execute()->willThrow(new \Exception('error message', Response::HTTP_BAD_REQUEST));

        $result = $this->__invoke($request);

        Assert::assertEquals(Response::HTTP_OK, $result->getStatusCode()->getWrappedObject());
        Assert::assertEquals(
            \json_encode(GetAllAppsResult::create(0, [])->normalize(), JSON_THROW_ON_ERROR),
            $result->getContent()->getWrappedObject()
        );
    }
}
