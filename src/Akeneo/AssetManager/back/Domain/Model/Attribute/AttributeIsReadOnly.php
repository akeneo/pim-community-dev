<?php

declare(strict_types=1);

/*
 * This file is part of the Akeneo PIM Enterprise Edition.
 *
 * (c) 2020 Akeneo SAS (http://www.akeneo.com)
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Akeneo\AssetManager\Domain\Model\Attribute;

/**
 * @author    Nicolas Marniesse <nicolas.marniesse@akeneo.com>
 * @copyright 2020 Akeneo SAS (http://www.akeneo.com)
 */
class AttributeIsReadOnly
{
    private bool $value;

    private function __construct(bool $value)
    {
        $this->value = $value;
    }

    public static function fromBoolean(bool $isReadOnly): self
    {
        return new self($isReadOnly);
    }

    public function normalize(): bool
    {
        return $this->value;
    }
}
