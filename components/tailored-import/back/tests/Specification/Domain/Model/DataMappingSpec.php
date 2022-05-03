<?php

declare(strict_types=1);

/*
 * This file is part of the Akeneo PIM Enterprise Edition.
 *
 * (c) 2022 Akeneo SAS (https://www.akeneo.com)
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Specification\Akeneo\Platform\TailoredImport\Domain\Model;

use Akeneo\Platform\TailoredImport\Domain\Model\Operation\OperationCollection;
use Akeneo\Platform\TailoredImport\Domain\Model\Target\AttributeTarget;
use PhpSpec\ObjectBehavior;

class DataMappingSpec extends ObjectBehavior
{
    public function it_can_be_initialized_from_normalized()
    {
        $this->beConstructedThrough('create', [
            'b244c45c-d5ec-4993-8cff-7ccd04e82fef',
            AttributeTarget::create(
                'code',
                'pim_catalog_text',
                'web',
                'fr_FR',
                'set',
                'skip',
                null,
            ),
            [],
            OperationCollection::create([]),
            [],
        ]);

        $this->getUuid()->shouldReturn('b244c45c-d5ec-4993-8cff-7ccd04e82fef');
    }

    public function it_throws_an_exception_when_uuid_is_invalid()
    {
        $this->beConstructedThrough('create', [
            'invalid-uuid',
            AttributeTarget::create(
                'code',
                'pim_catalog_text',
                'web',
                'fr_FR',
                'set',
                'skip',
                null,
            ),
            [],
            OperationCollection::create([]),
            [],
        ]);

        $this->shouldThrow(\InvalidArgumentException::class)->duringInstantiation();
    }
}
