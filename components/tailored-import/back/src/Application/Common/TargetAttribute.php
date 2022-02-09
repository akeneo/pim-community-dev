<?php

declare(strict_types=1);

namespace Akeneo\Platform\TailoredImport\Application\Common;

/**
 * @copyright 2022 Akeneo SAS (https://www.akeneo.com)
 * @license   https://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class TargetAttribute implements TargetInterface
{
    const TYPE = 'attribute';

    private function __construct(
        private string $code,
        private ?string $channel,
        private ?string $locale,
        private string $action,
        private string $ifEmpty)
    {
    }

    public static function createFromNormalized(array $normalizedAttributeTarget)
    {
        return new self(
            $normalizedAttributeTarget['code'],
            $normalizedAttributeTarget['channel'],
            $normalizedAttributeTarget['locale'],
            $normalizedAttributeTarget['action'],
            $normalizedAttributeTarget['if_empty']
        );
    }

    public function code(): string
    {
        return $this->code;
    }


    public function channel(): ?string
    {
        return $this->channel;
    }


    public function locale(): ?string
    {
        return $this->locale;
    }


    public function action(): string
    {
        return $this->action;
    }


    public function ifEmpty(): string
    {
        return $this->ifEmpty;
    }
}