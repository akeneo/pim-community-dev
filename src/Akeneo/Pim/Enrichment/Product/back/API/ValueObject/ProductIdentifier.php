<?php

declare(strict_types=1);

namespace Akeneo\Pim\Enrichment\Product\API\ValueObject;

use Webmozart\Assert\Assert;

/**
 * @copyright 2022 Akeneo SAS (https://www.akeneo.com)
 * @license   https://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
final class ProductIdentifier
{
    private function __construct(private string $identifier)
    {
        Assert::stringNotEmpty($identifier, 'The product identifier requires a non empty string');
    }

    public static function fromIdentifier(string $identifier): self
    {
        return new self($identifier);
    }

    public function identifier(): string
    {
        return $this->identifier;
    }
}
