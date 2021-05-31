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

namespace Akeneo\AssetManager\Domain\Model\AssetFamily\Transformation;

use Webmozart\Assert\Assert;

/**
 * @author    Nicolas Marniesse <nicolas.marniesse@akeneo.com>
 * @copyright 2019 Akeneo SAS (http://www.akeneo.com)
 */
class TransformationLabel
{
    private string $label;

    private function __construct(string $label)
    {
        Assert::stringNotEmpty($label);
        $this->label = $label;
    }

    public static function fromString(string $label): TransformationLabel
    {
        return new self($label);
    }

    public function normalize(): string
    {
        return $this->label;
    }

    public function toString(): string
    {
        return $this->label;
    }

    public function equals(TransformationLabel $otherLabel): bool
    {
        return $this->label === $otherLabel->toString();
    }
}
