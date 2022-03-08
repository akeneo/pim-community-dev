<?php

declare(strict_types=1);

namespace Akeneo\OnboarderSerenity\Domain\Write\Supplier;

final class Supplier
{
    private Identifier $identifier;
    private Code $code;
    private Label $label;

    private function __construct(
        string $identifier,
        string $code,
        string $label
    ) {
        $this->identifier = Identifier::fromString($identifier);
        $this->code = Code::fromString($code);
        $this->label = Label::fromString($label);
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
