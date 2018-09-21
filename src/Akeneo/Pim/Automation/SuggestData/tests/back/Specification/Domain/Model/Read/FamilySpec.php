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
use PhpSpec\ObjectBehavior;

/**
 * @author Julian Prud'homme <julian.prudhomme@akeneo.com>
 */
class FamilySpec extends ObjectBehavior
{
    public function let()
    {
        $this->beConstructedWith('router', [
            'en_US' => 'router',
            'fr_FR' => 'routeur',
        ]);
    }

    public function it_is_a_family_read_model()
    {
        $this->shouldHaveType(Family::class);
    }

    public function it_gets_the_family_code()
    {
        $this->getCode()->shouldReturn('router');
    }

    public function it_gets_the_labels()
    {
        $this->getLabels()->shouldReturn([
            'en_US' => 'router',
            'fr_FR' => 'routeur',
        ]);
    }
}
