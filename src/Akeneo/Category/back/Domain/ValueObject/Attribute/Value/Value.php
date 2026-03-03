<?php

declare(strict_types=1);

namespace Akeneo\Category\Domain\ValueObject\Attribute\Value;

use Akeneo\Category\Domain\ValueObject\Attribute\AttributeCode;
use Akeneo\Category\Domain\ValueObject\Attribute\AttributeUuid;

/**
 * @copyright 2022 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php Open Software License (OSL 3.0)
 *
 * @phpstan-import-type ImageData from ImageDataValue
 */
interface Value
{
    /**
     * @phpstan-ignore-next-line
     */
    public function normalize(): array;

    public function getKey(): string;

    public function getKeyWithChannelAndLocale(): string;

    public function getCode(): AttributeCode;

    public function getUuid(): AttributeUuid;

    public function getLocale(): ?LocaleValue;

    public function getChannel(): ?ChannelValue;
}
