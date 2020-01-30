<?php
declare(strict_types=1);

namespace Akeneo\Pim\Automation\FranklinInsights\Domain\QualityHighlights\ValueObject;

final class Lock
{
    /** @var string */
    private $uuid;

    public function __construct(string $uuid)
    {
        if ($uuid === '') {
            throw new \InvalidArgumentException('Uuid must not be empty');
        }

        $this->uuid = $uuid;
    }

    public function __toString(): string
    {
        return $this->uuid;
    }
}
