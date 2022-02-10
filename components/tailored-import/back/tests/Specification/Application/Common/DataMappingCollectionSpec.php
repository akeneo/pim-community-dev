<?php

declare(strict_types=1);

namespace Specification\Akeneo\Platform\TailoredImport\Application\Common;

use PhpSpec\ObjectBehavior;

/**
 * @copyright 2022 Akeneo SAS (https://www.akeneo.com)
 * @license   https://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class DataMappingCollectionSpec extends ObjectBehavior
{
    public function it_can_be_initialized_from_normalized()
    {
        $this->beConstructedThrough('createFromNormalized',[[[
            'uuid' => "b244c45c-d5ec-4993-8cff-7ccd04e82fef",
            'target' => [
                'type' => 'attribute',
                'code' => 'code',
                'channel' => 'web',
                'locale' => 'fr_FR',
                'action' => 'set',
                'if_empty' => 'test'
            ],
            'sources' => [],
            'operations' => [],
            'sample_data' => [],
        ]]]);

        $this->iterator()->shouldHaveCount(1);
    }
}