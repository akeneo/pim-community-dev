<?php

declare(strict_types=1);

namespace Akeneo\Pim\Enrichment\Product\Domain;

use Webmozart\Assert\Assert;

/**
 * @copyright 2022 Akeneo SAS (https://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
final class Violation
{
    private function  __construct(
        private string $message,
        private array $messageParameters = [],
        private ?string $path = null
    ) {
        Assert::stringNotEmpty($this->message);
        Assert::allString($this->messageParameters);
        Assert::allString(\array_keys($this->messageParameters));
    }

    public static function fromMessageAndPath(
        string $message,
        array $messageParameters = [],
        ?string $path = null
    ): Violation {
        return new Violation($message, $messageParameters, $path);
    }

    public function message(): string
    {
        return $this->message;
    }

    /**
     * @return array<string, string>
     */
    public function messageParameters(): array
    {
        return $this->messageParameters;
    }

    /**
     * @return string|null
     */
    public function path(): ?string
    {
        return $this->path;
    }
}
