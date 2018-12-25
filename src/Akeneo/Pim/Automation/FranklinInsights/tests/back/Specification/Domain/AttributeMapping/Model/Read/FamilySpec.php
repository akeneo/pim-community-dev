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

namespace Specification\Akeneo\Pim\Automation\FranklinInsights\Domain\AttributeMapping\Model\Read;

use Akeneo\Pim\Automation\FranklinInsights\Domain\AttributeMapping\Model\Read\Family;
use PhpSpec\ObjectBehavior;

/**
 * @author Julian Prud'homme <julian.prudhomme@akeneo.com>
 */
class FamilySpec extends ObjectBehavior
{
    public function let(): void
    {
        $this->beConstructedWith(
            'router',
            [
                'en_US' => 'router',
                'fr_FR' => 'routeur',
            ],
            Family::MAPPING_EMPTY
        );
    }

    public function it_is_a_family_read_model(): void
    {
        $this->shouldHaveType(Family::class);
    }

    public function it_gets_the_family_code(): void
    {
        $this->getCode()->shouldReturn('router');
    }

    public function it_gets_the_labels(): void
    {
        $this->getLabels()->shouldReturn([
            'en_US' => 'router',
            'fr_FR' => 'routeur',
        ]);
    }

    public function it_gets_the_family_status(): void
    {
        $this->getMappingStatus()->shouldReturn(2);
    }
}
