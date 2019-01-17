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

namespace Specification\Akeneo\Pim\Automation\FranklinInsights\Infrastructure\Persistence\Repository\Memory;

use Akeneo\Pim\Automation\FranklinInsights\Domain\IdentifierMapping\Model\IdentifiersMapping;
use Akeneo\Pim\Automation\FranklinInsights\Domain\IdentifierMapping\Repository\IdentifiersMappingRepositoryInterface;
use Akeneo\Pim\Automation\FranklinInsights\Infrastructure\Persistence\Repository\Memory\InMemoryIdentifiersMappingRepository;
use PhpSpec\ObjectBehavior;

/**
 * @author Damien Carcel <damien.carcel@akeneo.com>
 */
class InMemoryIdentifiersMappingRepositorySpec extends ObjectBehavior
{
    public function it_is_an_identifiers_mapping_repository(): void
    {
        $this->beConstructedWith(new IdentifiersMapping([]));
        $this->shouldBeAnInstanceOf(IdentifiersMappingRepositoryInterface::class);
    }

    public function it_is_an_in_memory_identifiers_mapping_repository(): void
    {
        $this->beConstructedWith(new IdentifiersMapping([]));
        $this->shouldBeAnInstanceOf(InMemoryIdentifiersMappingRepository::class);
    }

    public function it_finds_an_identifiers_mapping(): void
    {
        $identifiersMapping = new IdentifiersMapping([]);
        $this->beConstructedWith($identifiersMapping);

        $this->find()->shouldBeLike($identifiersMapping);
    }

    public function it_saves_an_identifiers_mapping(): void
    {
        $mappingUsedForContruction = new IdentifiersMapping([]);
        $this->beConstructedWith($mappingUsedForContruction);

        $newMapping = new IdentifiersMapping([]);
        $this->save($newMapping);
        $this->find()->shouldBeLike($newMapping);
    }
}
