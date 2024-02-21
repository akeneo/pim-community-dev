<?php

declare(strict_types=1);

namespace Akeneo\Category\Api\Command\UserIntents;

/**
 * @copyright 2022 Akeneo SAS (https://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
interface ValueImageUserIntent extends UserIntent
{
    public function attributeUuid(): string;

    public function attributeCode(): string;

    public function channelCode(): ?string;

    public function localeCode(): ?string;

    /**
     * @return array{
     *     size: int,
     *     extension: string,
     *     file_path: string,
     *     mime_type: string,
     *     original_filename: string,
     * } | null
     */
    public function value(): array|null;
}
