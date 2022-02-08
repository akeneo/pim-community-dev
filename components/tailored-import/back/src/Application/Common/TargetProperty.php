<?php

declare(strict_types=1);

namespace Akeneo\Platform\TailoredImport\Application\Common;

/**
 * @copyright 2022 Akeneo SAS (https://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class TargetProperty implements TargetInterface
{
    const TYPE = 'property';

    private function __construct(
        private string $code,
        private string $action, 
        private string $ifEmpty)
    {
    }

    public static function createFromNormalized(array $normalizedPropertyTarget)
    {
        return new self(
            $normalizedPropertyTarget['code'],
            $normalizedPropertyTarget['action'],
            $normalizedPropertyTarget['if_empty']
        );
    }

    public function getCode(): string
    {
        return $this->code;
    }

    public function getAction(): string
    {
        return $this->action;
    }


    public function getIfEmpty(): string
    {
        return $this->ifEmpty;
    }
}