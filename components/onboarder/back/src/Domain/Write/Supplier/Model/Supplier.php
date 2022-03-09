<?php

declare(strict_types=1);

namespace Akeneo\OnboarderSerenity\Domain\Write\Supplier\Model;

use Akeneo\OnboarderSerenity\Domain\Write\Supplier\ValueObject;

final class Supplier
{
    private ValueObject\Identifier $identifier;
    private ValueObject\Code $code;
    private ValueObject\Label $label;

    private function __construct(
        string $identifier,
        string $code,
        string $label
    ) {
        $this->identifier = ValueObject\Identifier::fromString($identifier);
        $this->code = ValueObject\Code::fromString($code);
        $this->label = ValueObject\Label::fromString($label);
    }

    public static function create(string $identifier, string $code, string $label): Supplier
    {
        return new self(
            $identifier,
            $code,
            $label
        );
    }

    public function identifier(): string
    {
        return (string) $this->identifier;
    }

    public function code(): string
    {
        return (string) $this->code;
    }

    public function label(): string
    {
        return (string) $this->label;
    }

    public function toArray(): array
    {
        return [
            'identifier' => $this->identifier(),
            'code' => $this->code(),
            'label' => $this->label(),
        ];
    }
}
