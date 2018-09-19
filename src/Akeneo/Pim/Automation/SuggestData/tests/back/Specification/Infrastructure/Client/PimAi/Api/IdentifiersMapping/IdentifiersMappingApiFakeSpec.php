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

use Akeneo\Pim\Automation\SuggestData\Infrastructure\Client\PimAi\Api\IdentifiersMapping\IdentifiersMappingApiFake;
use Akeneo\Pim\Automation\SuggestData\Infrastructure\Client\PimAi\Api\IdentifiersMapping\IdentifiersMappingApiInterface;
use PhpSpec\ObjectBehavior;

/**
 * @author Damien Carcel <damien.carcel@akeneo.com>
 */
class IdentifiersMappingApiFakeSpec extends ObjectBehavior
{
    function it_is_an_identifiers_mapping_api()
    {
        $this->shouldImplement(IdentifiersMappingApiInterface::class);
    }

    function it_is_a_fake_implementation_of_the_identifiers_mapping_api()
    {
        $this->beAnInstanceOf(IdentifiersMappingApiFake::class);
    }

    function it_returns_the_stored_mapping()
    {
        $this->get()->shouldReturn([]);
    }

    function it_updates_the_identifiers_mapping()
    {
        $normalizedMapping = [
            'foo' => [
                'code' => 'bar',
                'label' => ['en_US' => 'Chaquip'],
            ]
        ];

        $this->update($normalizedMapping);

        $this->get()->shouldReturn($normalizedMapping);
    }
}
