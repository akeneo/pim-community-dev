<?php

declare(strict_types=1);

namespace Akeneo\Category\Domain\ValueObject\Attribute\Value;

use Webmozart\Assert\Assert;

/**
 * @copyright 2022 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php Open Software License (OSL 3.0)
 */
class ChannelValue
{
    public function __construct(private readonly string $channel)
    {
        Assert::stringNotEmpty($channel);
    }

    public function getValue(): string
    {
        return $this->channel;
    }

    public function __toString(): string
    {
        return $this->channel;
    }
}
