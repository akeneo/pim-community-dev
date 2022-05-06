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

namespace Akeneo\Platform\TailoredImport\Test\Acceptance\FakeServices\Ramsey;

use Akeneo\Platform\TailoredImport\Application\ReadColumns\UuidGeneratorInterface;

class InMemoryUuidGenerator implements UuidGeneratorInterface
{
    public function generate(): string
    {
        return 'b64d1498-b668-4880-81c2-58f7c88375b1';
    }
}
