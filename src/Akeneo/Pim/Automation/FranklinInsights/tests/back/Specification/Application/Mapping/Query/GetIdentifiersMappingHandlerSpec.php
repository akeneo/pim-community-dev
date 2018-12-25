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

namespace Specification\Akeneo\Pim\Automation\FranklinInsights\Application\Mapping\Query;

use Akeneo\Pim\Automation\FranklinInsights\Application\Mapping\Query\GetIdentifiersMappingHandler;
use Akeneo\Pim\Automation\FranklinInsights\Application\Mapping\Query\GetIdentifiersMappingQuery;
use Akeneo\Pim\Automation\FranklinInsights\Domain\IdentifierMapping\Model\IdentifiersMapping;
use Akeneo\Pim\Automation\FranklinInsights\Domain\IdentifierMapping\Repository\IdentifiersMappingRepositoryInterface;
use PhpSpec\ObjectBehavior;

class GetIdentifiersMappingHandlerSpec extends ObjectBehavior
{
    public function let(IdentifiersMappingRepositoryInterface $identifiersMappingRepository): void
    {
        $this->beConstructedWith($identifiersMappingRepository);
    }

    public function it_is_an_identifiers_mapping_handler(): void
    {
        $this->shouldHaveType(GetIdentifiersMappingHandler::class);
    }

    public function it_gets_an_identifiers_mapping(
        $identifiersMappingRepository,
        IdentifiersMapping $identifiersMapping,
        GetIdentifiersMappingQuery $query
    ): void {
        $identifiersMappingRepository->find()->willReturn($identifiersMapping);

        $this->handle($query)->shouldReturn($identifiersMapping);
    }
}
