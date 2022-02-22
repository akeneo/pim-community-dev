<?php

declare(strict_types=1);

namespace Akeneo\OnboarderSerenity\Domain\Supplier;

final class Supplier
{
    private Identifier $identifier;
    private Code $code;
    private Label $label;

    private function __construct(
        Identifier $identifier,
        Code $code,
        Label $label
    ) {
        $this->identifier = $identifier;
        $this->code = $code;
        $this->label = $label;
    }

    public static function create(Identifier $identifier, Code $code, Label $label): Supplier
    {
        return new self(
            $identifier,
            $code,
            $label
        );
    }

    public function getIdentifier(): Identifier
    {
        return $this->identifier;
    }

    public function getCode(): Code
    {
        return $this->code;
    }

    public function equals(self $other): bool
    {
        return $this->identifier->equals($other->identifier)
            && $other->code->equals($this->code)
            && $other->label->equals($this->label)
        ;
    }

    public function getLabel(): Label
    {
        return $this->label;
    }
}
