<?php

declare(strict_types=1);

namespace Specification\Akeneo\Platform\TailoredImport\Domain\Model;

use PhpSpec\ObjectBehavior;

/**
 * @copyright 2022 Akeneo SAS (https://www.akeneo.com)
 * @license   https://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class DataMappingSpec extends ObjectBehavior
{
    public function it_can_be_initialized_from_normalized()
    {
        $this->beConstructedThrough('createFromNormalized', [[
            'uuid' => 'b244c45c-d5ec-4993-8cff-7ccd04e82fef',
            'target' => [
                'type' => 'attribute',
                'code' => 'code',
                'channel' => 'web',
                'locale' => 'fr_FR',
                'action' => 'set',
                'if_empty' => 'skip'
            ],
            'sources' => [],
            'operations' => [],
            'sample_data' => [],
        ]]);

        $this->uuid()->shouldReturn('b244c45c-d5ec-4993-8cff-7ccd04e82fef');
    }

    public function it_throws_an_exception_when_uuid_is_invalid()
    {
        $this->beConstructedThrough('createFromNormalized', [[
            'uuid' => 'invalid-uuid',
            'target' => [
                'type' => 'attribute',
                'code' => 'code',
                'channel' => 'web',
                'locale' => 'fr_FR',
                'action' => 'set',
                'if_empty' => 'skip'
            ],
            'sources' => [],
            'operations' => [],
            'sample_data' => [],
        ]]);

        $this->shouldThrow(\InvalidArgumentException::class)->duringInstantiation();
    }
}
