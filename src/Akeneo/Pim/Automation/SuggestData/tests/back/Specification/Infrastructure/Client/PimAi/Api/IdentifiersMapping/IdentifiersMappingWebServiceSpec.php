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

namespace Specification\Akeneo\Pim\Automation\SuggestData\Infrastructure\Client\PimAi\Api\IdentifiersMapping;

use Akeneo\Pim\Automation\SuggestData\Domain\Model\IdentifiersMapping;
use Akeneo\Pim\Automation\SuggestData\Infrastructure\DataProvider\Normalizer\IdentifiersMappingNormalizer;
use Akeneo\Pim\Automation\SuggestData\Infrastructure\Client\PimAi\Api\IdentifiersMapping\IdentifiersMappingWebService;
use Akeneo\Pim\Automation\SuggestData\Infrastructure\Client\PimAi\Client;
use Akeneo\Pim\Automation\SuggestData\Infrastructure\Client\PimAi\UriGenerator;
use PhpSpec\ObjectBehavior;

class IdentifiersMappingWebServiceSpec extends ObjectBehavior
{
    public function let(
        UriGenerator $uriGenerator,
        Client $httpClient
    ) {
        $this->beConstructedWith($uriGenerator, $httpClient);
    }

    public function it_is_subscription_collection()
    {
        $this->shouldHaveType(IdentifiersMappingWebService::class);
    }

    public function it_should_update_mapping(
        UriGenerator $uriGenerator,
        Client $httpClient,
        IdentifiersMapping $mapping
    ) {
        $normalizedMapping = ['foo' => 'bar'];
        $generatedRoute = '/api/mapping/identifiers';

        $uriGenerator->generate('/mapping/identifiers')
            ->shouldBeCalled()
            ->willReturn($generatedRoute);
        $httpClient->request('PUT', $generatedRoute, [
            'form_params' => $normalizedMapping
        ])->shouldBeCalled();

        $this->update($normalizedMapping);
    }
}
