<?php

declare(strict_types=1);

namespace Akeneo\Pim\Enrichment\Component\Product\Model\QuantifiedAssociation;

use Ramsey\Uuid\Uuid;
use Ramsey\Uuid\UuidInterface;
use Webmozart\Assert\Assert;

/**
 * @author    Samir Boulil <samir.boulil@akeneo.com>
 * @copyright 2020 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class QuantifiedLink
{
    private const QUANTITY_KEY = 'quantity';
    private const IDENTIFIER_KEY = 'identifier';
    private const UUID_KEY = 'uuid';

    /**
     * @deprecated TODO Set this construct private
     */
    public function __construct(
        private ?string $identifier = null,
        private ?int $quantity = null,
        private ?UuidInterface $uuid = null,
    ) {
    }

    public static function fromIdentifier(string $identifier, int $quantity)
    {
        Assert::stringNotEmpty($identifier);

        return new self($identifier, $quantity);
    }

    public static function fromUuid(string $uuid, int $quantity)
    {
        Assert::true(
            Uuid::isValid($uuid),
            sprintf('The associated product "%s" is not a valid uuid', $uuid)
        );

        return new self(null, $quantity, Uuid::fromString($uuid));
    }

    public function identifier(): ?string
    {
        return $this->identifier;
    }

    public function uuid(): ?UuidInterface
    {
        return $this->uuid;
    }

    public function normalize(): array
    {
        if (null === $this->identifier) {
            return [
                self::UUID_KEY => $this->uuid->toString(),
                self::QUANTITY_KEY => $this->quantity
            ];
        }
        return [
            self::IDENTIFIER_KEY => $this->identifier,
            self::QUANTITY_KEY => $this->quantity
        ];
    }
}
