<?php

declare(strict_types=1);

namespace Akeneo\Pim\Automation\DataQualityInsights\Domain\ValueObject;

/**
 * @copyright 2019 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
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
