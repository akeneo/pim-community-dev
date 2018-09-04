<?php

/*
 * This file is part of the Akeneo PIM Enterprise Edition.
 *
 * (c) 2018 Akeneo SAS (http://www.akeneo.com)
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

declare(strict_types=1);

namespace Akeneo\EnrichedEntity\Domain\Query\Attribute;

use Webmozart\Assert\Assert;

/**
 * Each Record value is identified by a single key, composed of
 *  - Attribute identifier
 *  - Channel
 *  - Locale
 *
 * @author    Adrien Pétremann <adrien.petremann@akeneo.com>
 * @copyright 2018 Akeneo SAS (https://www.akeneo.com)
 */
class ValueKey
{
    /** @var string */
    private $key;

    private function __construct(string $key)
    {
        Assert::notEmpty($key, 'Key should not be empty');

        $this->key = $key;
    }

    public static function fromNormalized(string $normalizedKey): self
    {
        return new self($normalizedKey);
    }

    public function normalize(): string
    {
        return $this->key;
    }
}
