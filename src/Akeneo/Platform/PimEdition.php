<?php

declare(strict_types=1);

namespace Akeneo\Platform;

use Webmozart\Assert\Assert;

/**
 * @copyright 2021 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
final class PimEdition
{
    private const SAAS_EDITIONS = ['Serenity', 'GE'];

    private string $edition;

    private function __construct(string $edition)
    {
        $this->edition = $edition;
    }

    public static function fromString(string $edition): self
    {
        Assert::stringNotEmpty($edition);

        return new self($edition);
    }

    public function asString(): string
    {
        return $this->edition;
    }

    public function isSaasVersion(): bool
    {
        return \in_array($this->edition, self::SAAS_EDITIONS, true);
    }
}
