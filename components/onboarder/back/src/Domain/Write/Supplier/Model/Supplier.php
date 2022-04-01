<?php

declare(strict_types=1);

namespace Akeneo\OnboarderSerenity\Domain\Write\Supplier\Model;

use Akeneo\OnboarderSerenity\Domain\Write\Supplier\ValueObject;

final class Supplier
{
    private ValueObject\Identifier $identifier;
    private ValueObject\Code $code;
    private ValueObject\Label $label;
    private ValueObject\Contributors $contributors;

    private function __construct(
        string $identifier,
        string $code,
        string $label,
        array $contributorEmails = [],
    ) {
        $this->identifier = ValueObject\Identifier::fromString($identifier);
        $this->code = ValueObject\Code::fromString($code);
        $this->label = ValueObject\Label::fromString($label);
        $this->contributors = ValueObject\Contributors::fromEmails($contributorEmails);
    }

    public static function create(string $identifier, string $code, string $label, array $contributorEmails): self
    {
        return new self(
            $identifier,
            $code,
            $label,
            $contributorEmails,
        );
    }

    public function update(string $label, array $contributorEmails): self
    {
        return new self(
            (string) $this->identifier,
            (string) $this->code,
            $label,
            $contributorEmails,
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

    public function contributors(): ValueObject\Contributors
    {
        return $this->contributors;
    }
}
