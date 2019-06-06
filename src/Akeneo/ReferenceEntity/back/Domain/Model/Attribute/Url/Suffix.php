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
class Suffix
{
    /** @var string */
    private $suffix;

    private function __construct(string $suffix)
    {
        $this->suffix = $suffix;
    }

    public static function fromString(?string $suffix): self
    {
        Assert::nullOrStringNotEmpty($suffix, 'The suffix cannot be an empty string');

        return new self($suffix);
    }

    public function normalize(): string
    {
        return $this->suffix;
    }
}
