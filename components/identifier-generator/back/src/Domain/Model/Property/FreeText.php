<?php

declare(strict_types=1);

namespace Akeneo\Pim\Automation\IdentifierGenerator\Domain\Model\Property;

use Akeneo\Pim\Automation\IdentifierGenerator\Domain\Model\Condition\ConditionInterface;
use Webmozart\Assert\Assert;

/**
 * Property to add a free text to the structure
 * @copyright 2022 Akeneo SAS (https://www.akeneo.com)
 * @license   https://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 *
 * @phpstan-type FreeTextNormalized array{type: 'free_text', string: string}
 */
final class FreeText implements PropertyInterface
{
    public const LENGTH_LIMIT = 100;

    private const TYPE = 'free_text';

    private function __construct(
        private string $value,
    ) {
        Assert::stringNotEmpty($value);
        Assert::maxLength($value, self::LENGTH_LIMIT);
    }

    public static function type(): string
    {
        return self::TYPE;
    }

    /**
     * @inheritDoc
     */
    public static function fromNormalized(array $normalizedProperty): PropertyInterface
    {
        Assert::keyExists($normalizedProperty, 'type');
        Assert::eq($normalizedProperty['type'], self::type());
        Assert::keyExists($normalizedProperty, 'string');
        Assert::string($normalizedProperty['string']);

        return self::fromString($normalizedProperty['string']);
    }

    /**
     * @return FreeTextNormalized
     */
    public function normalize(): array
    {
        return [
            'type' => self::TYPE,
            'string' => $this->value,
        ];
    }

    public static function fromString(string $value): self
    {
        return new self($value);
    }

    public function asString(): string
    {
        return $this->value;
    }

    public function getImplicitCondition(): ?ConditionInterface
    {
        return null;
    }
}
