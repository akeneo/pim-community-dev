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

namespace Specification\Akeneo\Pim\Automation\FranklinInsights\Domain\IdentifierMapping\Model;

use Akeneo\Pim\Automation\FranklinInsights\Domain\FamilyAttribute\Model\Read\Attribute;
use PhpSpec\ObjectBehavior;

/**
 * @author Damien Carcel <damien.carcel@akeneo.com>
 */
class IdentifierMappingSpec extends ObjectBehavior
{
    public function let(Attribute $akeneoAttribute): void
    {
        $this->beConstructedWith('franklin_code', $akeneoAttribute);
    }

    public function it_gets_franklin_attribute_code(): void
    {
        $this->getFranklinCode()->shouldReturn('franklin_code');
    }

    public function it_gets_akeneo_attribute($akeneoAttribute): void
    {
        $this->getAttribute()->shouldReturn($akeneoAttribute);
    }

    public function it_build_an_identifier_mapping_object_without_akeneo_attribute(): void
    {
        $this->beConstructedWith('franklin_code', null);

        $this->getFranklinCode()->shouldReturn('franklin_code');
        $this->getAttribute()->shouldReturn(null);
    }
}
