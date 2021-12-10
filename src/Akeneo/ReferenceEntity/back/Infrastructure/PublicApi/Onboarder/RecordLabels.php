<?php

declare(strict_types=1);

namespace Akeneo\ReferenceEntity\Infrastructure\PublicApi\Onboarder;

/**
 * @author    Christophe Chausseray <christophe.chausseray@akeneo.com>
 * @copyright 2019 Akeneo SAS (http://www.akeneo.com)
 */
final class RecordLabels
{
    /** @param array<string, string> $labels */
    public function __construct(
        private string $identifier,
        private array $labels,
        private string $code,
        private string $referenceEntityIdentifier
    ) {
    }

    public function getIdentifier(): string
    {
        return $this->identifier;
    }

    public function getLabels(): array
    {
        return $this->labels;
    }

    public function getCode(): string
    {
        return $this->code;
    }

    public function getReferenceEntityIdentifier(): string
    {
        return $this->referenceEntityIdentifier;
    }
}
