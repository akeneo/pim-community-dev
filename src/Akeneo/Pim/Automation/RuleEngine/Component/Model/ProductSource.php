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
    /** @var string|null */
    private $field;

    /** @var string|null */
    private $scope;

    /** @var string|null */
    private $locale;

    /** @var string|null */
    private $text;

    /** @var bool|null */
    private $newLine;

    /** @var array */
    private $options;

    private function __construct(
        ?string $field,
        ?string $scope,
        ?string $locale,
        ?string $text,
        ?bool $newLine,
        array $options
    ) {
        $this->field = $field;
        $this->scope = $scope;
        $this->locale = $locale;
        $this->text = $text;
        $this->newLine = $newLine;
        $this->options = $options;
    }

    public function getField(): ?string
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

    public function getText(): ?string
    {
        return $this->text;
    }

    public function isNewLine(): ?bool
    {
        return $this->newLine;
    }

    public function getOptions(): array
    {
        return $this->options;
    }

    public static function fromNormalized(array $normalized): self
    {
        if (isset($normalized['text'])) {
            return static::fromNormalizedText($normalized);
        }
        if (array_key_exists('new_line', $normalized)) {
            return static::fromNormalizedNewLine($normalized);
        }

        if (isset($normalized['field'])) {
            return static::fromNormalizedField($normalized);
        }

        throw new \InvalidArgumentException(
            'Concatenate source configuration requires either a "field", "text" or "new_line" key.'
        );
    }

    private static function fromNormalizedField(array $normalized): self
    {
        Assert::keyExists($normalized, 'field', 'Concatenate field source configuration requires a "field" key.');

        $options = $normalized;
        unset($options['field']);
        unset($options['scope']);
        unset($options['locale']);
        unset($options['text']);
        unset($options['newLine']);

        return new self(
            $normalized['field'] ?? null,
            $normalized['scope'] ?? null,
            $normalized['locale'] ?? null,
            null,
            null,
            $options
        );
    }

    private static function fromNormalizedText(array $normalized): self
    {
        Assert::keyExists($normalized, 'text', 'Concatenate text source configuration requires a "text" key.');

        return new self(
            null,
            null,
            null,
            $normalized['text'],
            null,
            []
        );
    }

    private static function fromNormalizedNewLine(array $normalized): self
    {
        Assert::keyExists(
            $normalized,
            'new_line',
            'Concatenate new line source configuration requires a "new_line" key.'
        );

        return new self(
            null,
            null,
            null,
            null,
            true,
            []
        );
    }
}
