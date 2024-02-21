<?php

declare(strict_types=1);

namespace Akeneo\Platform\Job\ServiceApi\JobInstance;

/**
 * @copyright 2022 Akeneo SAS (https://www.akeneo.com)
 * @license https://opensource.org/licenses/osl-3.0.php Open Software License (OSL 3.0)
 */
final class JobInstance
{
    public function __construct(
        private string $code,
        private ?string $label,
        private array $parameters = [],
    ) {
    }

    public function getCode(): string
    {
        return $this->code;
    }

    public function getLabel(): ?string
    {
        return $this->label;
    }

    public function getParameters(): array
    {
        return $this->parameters;
    }
}
