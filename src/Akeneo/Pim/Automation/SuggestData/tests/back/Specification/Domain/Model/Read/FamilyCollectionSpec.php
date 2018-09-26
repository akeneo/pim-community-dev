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

namespace Specification\Akeneo\Pim\Automation\SuggestData\Domain\Model\Read;

use Akeneo\Pim\Automation\SuggestData\Domain\Model\Read\Family;
use Akeneo\Pim\Automation\SuggestData\Domain\Model\Read\FamilyCollection;
use PhpSpec\ObjectBehavior;

/**
 * @author Julian Prud'homme <julian.prudhomme@akeneo.com>
 */
class FamilyCollectionSpec extends ObjectBehavior
{
    public function it_is_a_family_collection()
    {
        $this->shouldHaveType(FamilyCollection::class);
    }

    public function it_is_iterable()
    {
        $this->shouldHaveType(\IteratorAggregate::class);
    }

    public function it_can_add_a_family()
    {
        $this
            ->add(new Family('router', []))
            ->add(new Family('camcorders', []));
        $this->getIterator()->count()->shouldReturn(2);
    }
}
