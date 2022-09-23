<?php

declare(strict_types=1);

/*
 * This file is part of the Akeneo PIM Enterprise Edition.
 *
 * (c) 2021 Akeneo SAS (https://www.akeneo.com)
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Specification\Akeneo\Platform\Syndication\Application\Common\Selection\Family;

use Akeneo\Platform\Syndication\Application\Common\Selection\Family\FamilyCodeSelection;
use PhpSpec\ObjectBehavior;

class FamilyCodeSelectionSpec extends ObjectBehavior
{
    public function it_is_initializable(): void
    {
        $this->shouldBeAnInstanceOf(FamilyCodeSelection::class);
    }
}
