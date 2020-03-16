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
final class ProductSource
{
    /** @var string */
    private $field;

    /** @var string|null */
    private $scope;

    /** @var string|null */
    private $locale;

    /** @var array */
    private $options;

    private function __construct(string $field, ?string $scope, ?string $locale, array $options)
    {
        $this->field = strtolower($field);
        $this->scope = $scope;
        $this->locale = $locale;
        $this->options = $options;
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

    public function getOptions(): array
    {
        return $this->options;
    }

    public static function fromNormalized(array $normalized): self
    {
        Assert::keyExists($normalized, 'field', 'Concatenate source configuration requires a "field" key.');

        $options = $normalized;
        unset($options['field']);
        unset($options['scope']);
        unset($options['locale']);

        return new self($normalized['field'], $normalized['scope'] ?? null, $normalized['locale'] ?? null, $options);
    }
}
