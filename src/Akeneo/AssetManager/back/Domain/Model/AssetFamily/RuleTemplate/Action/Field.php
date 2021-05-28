<?php

declare(strict_types=1);

/*
 * This file is part of the Akeneo PIM Enterprise Edition.
 *
 * (c) 2019 Akeneo SAS (http://www.akeneo.com)
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Akeneo\AssetManager\Domain\Model\AssetFamily\RuleTemplate\Action;

use Webmozart\Assert\Assert;

/**
 * @author    Christophe Chausseray <christophe.chausseray@akeneo.com>
 * @copyright 2019 Akeneo SAS (http://www.akeneo.com)
 */
class Field
{
    private string $value;

    private function __construct(string $value)
    {
        Assert::stringNotEmpty($value, 'Field value of action should not be empty');

        $this->value = $value;
    }

    public static function createFromNormalized(string $value): self
    {
        return new self($value);
    }

    public function stringValue(): string
    {
        return $this->value;
    }
}
