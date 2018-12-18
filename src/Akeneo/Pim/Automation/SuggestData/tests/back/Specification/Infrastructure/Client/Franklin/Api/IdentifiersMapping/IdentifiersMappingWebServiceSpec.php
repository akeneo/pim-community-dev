<?php

declare(strict_types=1);

/*
 * This file is part of the Akeneo PIM Enterprise Edition.
 *
 * (c) 2018 Akeneo SAS (http://www.akeneo.com)
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Specification\Akeneo\Pim\Automation\SuggestData\Infrastructure\Client\Franklin\Api\IdentifiersMapping;

use Akeneo\Pim\Automation\SuggestData\Infrastructure\Client\Franklin\Api\AuthenticatedApiInterface;
use Akeneo\Pim\Automation\SuggestData\Infrastructure\Client\Franklin\Api\IdentifiersMapping;
use Akeneo\Pim\Automation\SuggestData\Infrastructure\Client\Franklin\GuzzleClient;
use Akeneo\Pim\Automation\SuggestData\Infrastructure\Client\Franklin\UriGenerator;
use PhpSpec\ObjectBehavior;

class IdentifiersMappingWebServiceSpec extends ObjectBehavior
{
    public function let(
        UriGenerator $uriGenerator,
        GuzzleClient $httpClient
    ): void {
        $this->beConstructedWith($uriGenerator, $httpClient);
    }

    public function it_is_subscription_collection(): void
    {
        $this->shouldHaveType(IdentifiersMapping\IdentifiersMappingWebService::class);
    }

    public function it_is_an_authenticated_webservice(): void
    {
        $this->shouldImplement(AuthenticatedApiInterface::class);
    }

    public function it_updates_mapping(
        UriGenerator $uriGenerator,
        GuzzleClient $httpClient
    ): void {
        $normalizedMapping = ['foo' => 'bar'];
        $generatedRoute = '/api/mapping/identifiers';

        $uriGenerator->generate('/api/mapping/identifiers')
            ->shouldBeCalled()
            ->willReturn($generatedRoute);
        $httpClient->request('PUT', $generatedRoute, [
            'form_params' => $normalizedMapping,
        ])->shouldBeCalled();

        $this->update($normalizedMapping);
    }
}
