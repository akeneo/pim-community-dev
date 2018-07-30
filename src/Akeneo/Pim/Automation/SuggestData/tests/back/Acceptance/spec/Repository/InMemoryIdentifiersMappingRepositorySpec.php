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

namespace spec\Akeneo\Test\Pim\Automation\SuggestData\Acceptance\Repository;

use Akeneo\Pim\Automation\SuggestData\Domain\Model\IdentifiersMapping;
use Akeneo\Pim\Automation\SuggestData\Component\Repository\IdentifiersMappingRepositoryInterface;
use Akeneo\Test\Pim\Automation\SuggestData\Acceptance\Repository\InMemoryIdentifiersMappingRepository;
use PhpSpec\ObjectBehavior;

/**
 * @author Damien Carcel <damien.carcel@akeneo.com>
 */
class InMemoryIdentifiersMappingRepositorySpec extends ObjectBehavior
{
    function it_is_an_identifiers_mapping_repository()
    {
        $this->beConstructedWith(new IdentifiersMapping([]));
        $this->shouldBeAnInstanceOf(IdentifiersMappingRepositoryInterface::class);
    }

    function it_is_an_in_memory_identifiers_mapping_repository()
    {
        $this->beConstructedWith(new IdentifiersMapping([]));
        $this->shouldBeAnInstanceOf(InMemoryIdentifiersMappingRepository::class);
    }

    function it_finds_an_identifiers_mapping()
    {
        $identifiersMapping = new IdentifiersMapping([]);
        $this->beConstructedWith($identifiersMapping);

        $this->find()->shouldReturn($identifiersMapping);
    }

    function it_saves_an_identifiers_mapping()
    {
        $mappingUsedForContruction = new IdentifiersMapping([]);
        $this->beConstructedWith($mappingUsedForContruction);

        $newMapping = new IdentifiersMapping([]);
        $this->save($newMapping);
        $this->find()->shouldReturn($newMapping);
    }
}
