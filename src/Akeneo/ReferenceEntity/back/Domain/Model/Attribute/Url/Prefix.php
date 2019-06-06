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

namespace Akeneo\ReferenceEntity\Domain\Model\Attribute\Url;

use Webmozart\Assert\Assert;

/**
 * @author    Christophe Chausseray <christophe.chausseray@akeneo.com>
 * @copyright 2019 Akeneo SAS (http://www.akeneo.com)
 */
class Prefix
{
    /** @var string */
    private $prefix;

    private function __construct(string $prefix)
    {
        $this->prefix = $prefix;
    }

    public static function fromString(?string $prefix): self
    {
        Assert::nullOrStringNotEmpty($prefix, 'The prefix cannot be an empty string');

        return new self($prefix);
    }

    public function normalize(): string
    {
        return $this->prefix;
    }
}
