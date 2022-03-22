<?php

declare(strict_types=1);

namespace Specification\Akeneo\Platform\TailoredImport\Domain\Model;

use Akeneo\Platform\TailoredImport\Domain\Model\TargetAttribute;
use PhpSpec\ObjectBehavior;

/**
 * @copyright 2022 Akeneo SAS (https://www.akeneo.com)
 * @license   https://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class DataMappingSpec extends ObjectBehavior
{
    public function it_can_be_initialized_from_normalized()
    {
        $this->beConstructedThrough('create', [
            'b244c45c-d5ec-4993-8cff-7ccd04e82fef',
            TargetAttribute::create(
                'code',
                'pim_catalog_text',
                'web',
                'fr_FR',
                'set',
                'skip'
            ),
            [],
            [],
            [],
        ]);

        $this->getUuid()->shouldReturn('b244c45c-d5ec-4993-8cff-7ccd04e82fef');
    }

    public function it_throws_an_exception_when_uuid_is_invalid()
    {
        $this->beConstructedThrough('create', [
            'invalid-uuid',
            TargetAttribute::create(
                'code',
                'pim_catalog_text',
                'web',
                'fr_FR',
                'set',
                'skip'
            ),
            [],
            [],
            [],
        ]);

        $this->shouldThrow(\InvalidArgumentException::class)->duringInstantiation();
    }
}
