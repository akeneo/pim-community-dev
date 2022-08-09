<?php

declare(strict_types=1);

namespace Akeneo\Category\Api\Model\Category;

use Akeneo\Category\Domain\ValueObject\Code as CodeFromDomain;
use Webmozart\Assert\Assert;

/**
 * This model represents a category code as exposed to the outside of the category bounded context
 * It resembles the eponymous internal domain model but can drift in the future
 *
 * @copyright 2022 Akeneo SAS (https://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class Code
{
    public static function fromDomainModel(CodeFromDomain $c): Code
    {
        return new Code((string)$c);
    }

    public function __construct(private string $code)
    {
        Assert::notEmpty($code);
    }

    public function __toString(): string
    {
        return $this->code;
    }
}
