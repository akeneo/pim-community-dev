<?php

declare(strict_types=1);

namespace Akeneo\Category\Api\Command\UserIntents;

/**
 * @copyright 2022 Akeneo SAS (https://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
interface ValueUserIntent extends UserIntent
{
    public function attributeUuid(): string;

    public function attributeCode(): string;

    public function channelCode(): ?string;

    public function localeCode(): ?string;

    public function value(): string;
}
