<?php

declare(strict_types=1);

/*
 * This file is part of the Akeneo PIM Enterprise Edition.
 *
 * (c) 2022 Akeneo SAS (https://www.akeneo.com)
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Specification\Akeneo\Platform\TailoredImport\Application\ExecuteDataMapping\UserIntentAggregator;

use Akeneo\Pim\Enrichment\Product\Api\Command\UserIntent\UserIntent;
use PhpSpec\ObjectBehavior;

class UserIntentAggregatorSpec extends ObjectBehavior
{
    public function it_does_nothing_for_the_moment(
        UserIntent $firstUserIntent,
        UserIntent $secondUserIntent,
    ) {
        $this->aggregateByTarget([$firstUserIntent, $secondUserIntent])->shouldReturn([$firstUserIntent, $secondUserIntent]);
    }
}
