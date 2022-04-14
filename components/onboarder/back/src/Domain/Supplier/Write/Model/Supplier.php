<?php

declare(strict_types=1);

namespace Akeneo\OnboarderSerenity\Domain\Supplier\Write\Model;

use Akeneo\OnboarderSerenity\Domain\Supplier\Write\ValueObject\Code;
use Akeneo\OnboarderSerenity\Domain\Supplier\Write\ValueObject\Contributors;
use Akeneo\OnboarderSerenity\Domain\Supplier\Write\ValueObject\Identifier;
use Akeneo\OnboarderSerenity\Domain\Supplier\Write\ValueObject\Label;

final class Supplier
{
    private Identifier $identifier;
    private Code $code;
    private Label $label;
    private Contributors $contributors;

    private function __construct(
        string $identifier,
        string $code,
        string $label,
        array $contributorEmails = [],
    ) {
        $this->identifier = Identifier::fromString($identifier);
        $this->code = Code::fromString($code);
        $this->label = Label::fromString($label);
        $this->contributors = Contributors::fromEmails($contributorEmails);
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

    public function contributors(): array
    {
        return $this->contributors->toArray();
    }
}
