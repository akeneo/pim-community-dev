<?php

declare(strict_types=1);

namespace Akeneo\EnrichedEntity\back\Domain\Model\Record;

/**
 * @author    Samir Boulil <samir.boulil@akeneo.com>
 * @copyright 2018 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class RecordIdentifier
{
    /** @var string */
    private $identifier;

    private function __construct(string $identifier)
    {
        $this->identifier = $identifier;
    }

    public static function fromString(string $identifier): self
    {
        return new self($identifier);
    }

    public function __toString(): string
    {
        return $this->identifier;
    }

    public function equals(RecordIdentifier $identifier): bool
    {
        return $this->identifier === (string) $identifier;
    }
}
