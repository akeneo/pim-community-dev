<?php

declare(strict_types=1);

namespace Akeneo\OnboarderSerenity\Application\Supplier;

final class CreateSupplier
{
    public string $identifier;
    public string $code;
    public string $label;

    public function __construct(string $identifier, string $code, string $label)
    {
        $this->identifier = $identifier;
        $this->code = $code;
        $this->label = $label;
    }
}
