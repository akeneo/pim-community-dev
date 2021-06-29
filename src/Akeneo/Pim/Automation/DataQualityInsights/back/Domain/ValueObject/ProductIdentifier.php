<?php

declare(strict_types=1);

namespace Akeneo\Pim\Automation\DataQualityInsights\Domain\ValueObject;

/**
 * @copyright 2019 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
final class ProductIdentifier
{
    /** @var string */
    private $identifier;

    public function __construct(string $identifier)
    {
        if (empty($identifier)) {
            throw new \InvalidArgumentException('A product identifier cannot be empty');
        }

        $this->identifier = $identifier;
    }

    public function __toString()
    {
        return $this->identifier;
    }
}
