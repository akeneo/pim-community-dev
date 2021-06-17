<?php

declare(strict_types=1);

namespace Akeneo\AssetManager\Domain\Model\Attribute;

use Webmozart\Assert\Assert;

/**
 * @author    Samir Boulil <samir.boulil@akeneo.com>
 * @copyright 2018 Akeneo SAS (http://www.akeneo.com)
 */
class AttributeValidationRule
{
    public const NONE = 'none';
    public const EMAIL = 'email';
    public const REGULAR_EXPRESSION = 'regular_expression';
    public const URL = 'url';
    public const VALIDATION_RULE_TYPES = [self::EMAIL, self::REGULAR_EXPRESSION, self::URL, self::NONE];

    private ?string $validationRule = null;

    private function __construct(?string $validationRule)
    {
        Assert::true(
            in_array($validationRule, self::VALIDATION_RULE_TYPES),
            sprintf(
                'Expected validation to be any of "%s", "%s" given.',
                implode(', ', self::VALIDATION_RULE_TYPES),
                $validationRule
            )
        );
        $this->validationRule = $validationRule;
    }

    public static function fromString(string $validationRule): self
    {
        return new self($validationRule);
    }

    public static function none(): self
    {
        return new self(self::NONE);
    }

    public function isNone(): bool
    {
        return self::NONE === $this->validationRule;
    }

    public function isRegularExpression(): bool
    {
        return self::REGULAR_EXPRESSION === $this->validationRule;
    }

    public function isEmail(): bool
    {
        return self::EMAIL === $this->validationRule;
    }

    public function isUrl(): bool
    {
        return self::URL === $this->validationRule;
    }

    public function normalize(): ?string
    {
        return $this->validationRule;
    }
}
