<?php

declare(strict_types=1);

namespace Akeneo\Pim\Enrichment\Product\Domain\Model;

use Webmozart\Assert\Assert;

/**
 * @copyright 2022 Akeneo SAS (https://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
final class ProductIdentifier
{
    private function __construct(private string $identifier)
    {
        Assert::NotEmpty($this->identifier);
    }

    public static function fromString(string $identifier): ProductIdentifier
    {
        return new self($identifier);
    }

    public function asString(): string
    {
        return $this->identifier;
    }
}
