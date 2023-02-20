<?php

declare(strict_types=1);

namespace Akeneo\Pim\Platform\Messaging\Domain;

/**
 * @copyright 2023 Akeneo SAS (https://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
interface SerializableMessageInterface
{
    public function normalize(): array;

    public static function denormalize(array $normalized): SerializableMessageInterface;
}
