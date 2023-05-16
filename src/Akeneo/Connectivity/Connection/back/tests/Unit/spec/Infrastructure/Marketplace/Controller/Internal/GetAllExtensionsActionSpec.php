<?php

declare(strict_types=1);

namespace spec\Akeneo\Connectivity\Connection\Infrastructure\Marketplace\Controller\Internal;

use Akeneo\Connectivity\Connection\Application\Marketplace\MarketplaceAnalyticsGenerator;
use Akeneo\Connectivity\Connection\Domain\Marketplace\DTO\GetAllExtensionsResult;
use Akeneo\Connectivity\Connection\Domain\Marketplace\GetAllExtensionsQueryInterface;
use Akeneo\UserManagement\Bundle\Context\UserContext;
use PhpSpec\ObjectBehavior;
use PHPUnit\Framework\Assert;
use Psr\Log\LoggerInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

class GetAllExtensionsActionSpec extends ObjectBehavior
{
    public function let(
        GetAllExtensionsQueryInterface $getAllExtensionsQuery,
        MarketplaceAnalyticsGenerator $marketplaceAnalyticsGenerator,
        UserContext $userContext,
        LoggerInterface $logger,
    ): void {
        $this->beConstructedWith(
            $getAllExtensionsQuery,
            $marketplaceAnalyticsGenerator,
            $userContext,
            $logger,
        );
    }

    public function it_returns_an_empty_list_when_the_marketplace_api_throws_a_bad_request_error(
        Request $request,
        GetAllExtensionsQueryInterface $getAllExtensionsQuery,
    ): void {
        $request->isXmlHttpRequest()->willReturn(true);

        $getAllExtensionsQuery->execute()->willThrow(new \Exception('error message', Response::HTTP_BAD_REQUEST));

        $result = $this->__invoke($request);

        Assert::assertEquals(Response::HTTP_OK, $result->getStatusCode()->getWrappedObject());
        Assert::assertEquals(
            \json_encode(GetAllExtensionsResult::create(0, [])->normalize(), JSON_THROW_ON_ERROR),
            $result->getContent()->getWrappedObject()
        );
    }
}
