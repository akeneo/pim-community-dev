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

namespace Specification\Akeneo\Pim\Automation\SuggestData\Infrastructure\Client\PimAi\Api\AttributeOptionsMapping;

use Akeneo\Pim\Automation\SuggestData\Infrastructure\Client\PimAi\Api\AttributeOptionsMapping\AttributeOptionsMappingInterface;
use Akeneo\Pim\Automation\SuggestData\Infrastructure\Client\PimAi\Api\AttributeOptionsMapping\AttributeOptionsMappingWebService;
use Akeneo\Pim\Automation\SuggestData\Infrastructure\Client\PimAi\Client;
use Akeneo\Pim\Automation\SuggestData\Infrastructure\Client\PimAi\UriGenerator;
use PhpSpec\ObjectBehavior;

/**
 * @author Romain Monceau <romain@akeneo.com>
 */
class AttributeOptionsMappingWebServiceSpec extends ObjectBehavior
{
    public function let(UriGenerator $uriGenerator, Client $httpClient): void
    {
        $this->beConstructedWith($uriGenerator, $httpClient);
    }

    public function it_is_an_attribute_options_mapping_web_service(): void
    {
        $this->shouldHaveType(AttributeOptionsMappingWebService::class);
    }

    public function it_implements_attribute_options_mapping_interface(): void
    {
        $this->shouldImplement(AttributeOptionsMappingInterface::class);
    }

    public function it_fetches_attribute_options_mapping($uriGenerator, $httpClient): void
    {
        $uriGenerator->generate('/api/mapping/foo/attributes/bar/options');

        $this->fetchByFamilyAndAttribute('foo', 'bar')->shouldReturn();
    }
}
