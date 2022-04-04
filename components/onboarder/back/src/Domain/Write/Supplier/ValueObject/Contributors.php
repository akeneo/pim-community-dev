<?php

declare(strict_types=1);

namespace Akeneo\OnboarderSerenity\Domain\Write\Supplier\ValueObject;

final class Contributors implements \Countable
{
    /** @var array<Contributor> */
    private array $contributors;

    private function __construct(array $contributors)
    {
        $this->contributors = $contributors;
    }

    public static function fromEmails(array $contributorEmails): self
    {
        $contributors = [];
        foreach ($contributorEmails as $email) {
            $contributors[] = Contributor::fromEmail($email);
        }

        return new self($contributors);
    }

    public function toArray(): array
    {
        return array_map(fn (Contributor $contributor) => ['email' => $contributor->email()], $this->contributors);
    }

    public function count(): int
    {
        return count($this->contributors);
    }
}
