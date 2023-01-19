<?php

declare(strict_types=1);

namespace Specification\Akeneo\Pim\Automation\IdentifierGenerator\Domain\Model\Property;

use Akeneo\Pim\Automation\IdentifierGenerator\Domain\Model\Property\FamilyProperty;
use Akeneo\Pim\Automation\IdentifierGenerator\Domain\Model\Property\Process;
use PhpSpec\ObjectBehavior;

/**
 * @copyright 2023 Akeneo SAS (https://www.akeneo.com)
 * @license   https://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class ProcessSpec extends ObjectBehavior
{
    public function let(): void
    {
        $this->beConstructedThrough('fromNormalized', [
            [
                'type' => 'truncate',
                'operator' => 'EQUALS',
                'value' => 3
            ]
        ]);
    }

    public function it_is_a_family_generation_process(): void
    {
        $this->shouldBeAnInstanceOf(Process::class);
    }

    public function it_returns_a_type(): void
    {
        $this->type()->shouldReturn('truncate');
    }

    public function it_normalize_a_process(): void
    {
        $this->normalize()->shouldReturn([
            'type' => 'truncate',
            'operator' => 'EQUALS',
            'value' => 3
        ]);
    }
}
