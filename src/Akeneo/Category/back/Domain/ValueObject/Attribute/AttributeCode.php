<?php

declare(strict_types=1);

namespace Akeneo\Category\Domain\ValueObject\Attribute;

use Webmozart\Assert\Assert;

/**
 * @copyright 2022 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php Open Software License (OSL 3.0)
 */
class AttributeCode
{
    public function __construct(private readonly string $code)
    {
        Assert::stringNotEmpty($code);
        Assert::regex($code, '/^[a-z0-9_]+$/', 'akeneo.category.validation.attribute.code.wrong_format');
        Assert::maxLength($code, 100);
    }

    public function __toString(): string
    {
        return $this->code;
    }
}
