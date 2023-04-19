<?php

declare(strict_types=1);

namespace Akeneo\Category\Domain\Query\DeactivatedTemplateAttributes;

/**
 * @copyright 2023 Akeneo SAS (https://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
final class DeactivatedTemplateAttributeIdentifier
{
    public function __construct(
        public readonly string $uuid,
        public readonly string $code,
    ) {
    }
}
