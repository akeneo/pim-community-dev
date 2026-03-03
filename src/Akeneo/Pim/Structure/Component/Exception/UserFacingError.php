<?php

declare(strict_types=1);

/**
 * @copyright 2023 Akeneo SAS (https://www.akeneo.com)
 * @license https://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */

namespace Akeneo\Pim\Structure\Component\Exception;

class UserFacingError extends \Exception
{
    /**
     * @param array<string,mixed> $translationParameters
     */
    public function __construct(
        private string $translationKey,
        private array $translationParameters = []
    ) {
        parent::__construct();
    }

    public function translationKey(): string
    {
        return $this->translationKey;
    }

    /**
     * @return array<string,mixed>
     */
    public function translationParameters(): array
    {
        return $this->translationParameters;
    }
}
