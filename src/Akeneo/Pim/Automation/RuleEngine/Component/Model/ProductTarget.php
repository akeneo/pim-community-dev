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

namespace Akeneo\Pim\Automation\RuleEngine\Component\Model;

use Webmozart\Assert\Assert;

/**
 * @author    Nicolas Marniesse <nicolas.marniesse@akeneo.com>
 * @copyright 2020 Akeneo SAS (http://www.akeneo.com)
 */
final class ProductTarget
{
    /** @var string */
    private $field;

    /** @var string|null */
    private $locale;

    /** @var string|null */
    private $scope;

    /** @var string|null */
    private $currency;

    /** @var string|null */
    private $unit;

    private function __construct(string $field, ?string $scope, ?string $locale, ?string $currency, ?string $unit)
    {
        $this->field = $field;
        $this->scope = $scope;
        $this->locale = $locale;
        $this->currency = $currency;
        $this->unit = $unit;
    }

    public function getField(): string
    {
        return $this->field;
    }

    public function getLocale(): ?string
    {
        return $this->locale;
    }

    public function getScope(): ?string
    {
        return $this->scope;
    }

    public function getCurrency(): ?string
    {
        return $this->currency;
    }

    public function getUnit(): ?string
    {
        return $this->unit;
    }

    public static function fromNormalized(array $normalized): self
    {
        Assert::keyExists($normalized, 'field', 'Target configuration requires a "field" key.');

        return new self(
            $normalized['field'],
            $normalized['scope'] ?? null,
            $normalized['locale'] ?? null,
            $normalized['currency'] ?? null,
            $normalized['unit'] ?? null
        );
    }
}
