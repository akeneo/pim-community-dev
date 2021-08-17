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

namespace Specification\Akeneo\Platform\TailoredExport\Application\Common\SourceValue;

use Akeneo\Platform\TailoredExport\Application\Common\SourceValue\QuantifiedAssociation;
use PhpSpec\ObjectBehavior;

class QuantifiedAssociationSpec extends ObjectBehavior
{
    public function let()
    {
        $this->beConstructedWith('an_id', 100);
    }

    public function it_is_initializable(): void
    {
        $this->shouldBeAnInstanceOf(QuantifiedAssociation::class);
    }

    public function it_returns_the_identifier()
    {
        $this->getIdentifier()->shouldReturn('an_id');
    }

    public function it_returns_the_quantity()
    {
        $this->getQuantity()->shouldReturn(100);
    }
}
