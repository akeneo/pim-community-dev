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

namespace Specification\Akeneo\Pim\Automation\SuggestData\Application\Mapping\Query;

use Akeneo\Pim\Automation\SuggestData\Application\Mapping\Query\GetIdentifiersMappingQuery;
use PhpSpec\ObjectBehavior;

class GetIdentifiersMappingQuerySpec extends ObjectBehavior
{
    public function it_is_a_get_identifiers_mapping_query(): void
    {
        $this->shouldHaveType(GetIdentifiersMappingQuery::class);
    }
}
