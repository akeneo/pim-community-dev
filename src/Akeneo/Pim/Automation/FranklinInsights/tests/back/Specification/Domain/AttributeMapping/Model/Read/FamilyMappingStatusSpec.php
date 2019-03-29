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

use Akeneo\Pim\Automation\FranklinInsights\Domain\AttributeMapping\Model\Read\FamilyMappingStatus;
use Akeneo\Pim\Automation\FranklinInsights\Domain\Common\Model\Read\Family;
use Akeneo\Pim\Automation\FranklinInsights\Domain\Common\ValueObject\FamilyCode;
use PhpSpec\ObjectBehavior;

/**
 * @author Julian Prud'homme <julian.prudhomme@akeneo.com>
 */
class FamilyMappingStatusSpec extends ObjectBehavior
{
    public function let(): void
    {
        $family = new Family(new FamilyCode('router'), []);

        $this->beConstructedWith($family, FamilyMappingStatus::MAPPING_EMPTY);
    }

    public function it_is_a_family_mapping_status_read_model(): void
    {
        $this->shouldHaveType(FamilyMappingStatus::class);
    }

    public function it_gets_the_family(): void
    {
        $family = new Family(new FamilyCode('router'), ['en_US' => 'Router']);
        $this->beConstructedWith($family, FamilyMappingStatus::MAPPING_EMPTY);

        $this->getFamily()->shouldReturn($family);
    }

    public function it_gets_the_mapping_status(): void
    {
        $this->getMappingStatus()->shouldReturn(2);
    }
}
