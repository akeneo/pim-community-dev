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

namespace Akeneo\Pim\Automation\DataQualityInsights\Domain\ValueObject;

final class LanguageCode
{
    /** @var string */
    private $code;

    public function __construct(string $code)
    {
        if (preg_match('~^[a-z]{2}(_[A-Z]{2})?$~', $code) === 0) {
            throw new \InvalidArgumentException('A language code must be a two letter code (ex: "en", "fr") or a valid locale (ex: "pt_BR").');
        }

        $this->code = $code;
    }

    public function __toString()
    {
        return $this->code;
    }
}
