<?php

declare(strict_types=1);

namespace Akeneo\Pim\Automation\IdentifierGenerator\Domain\Model\Property;

use Webmozart\Assert\Assert;

/**
 * Property to add a free text to the structure
 * @copyright 2022 Akeneo SAS (https://www.akeneo.com)
 * @license   https://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
final class FreeText implements PropertyInterface
{
    public const LENGTH_LIMIT = 100;

    private function __construct(
        private string $value,
    ) {
    }

    public static function type(): string
    {
        return 'free_text';
    }

    /**
     * @inheritDoc
     */
    public static function fromNormalized(array $normalizedProperty): PropertyInterface
    {
        Assert::keyExists($normalizedProperty, 'type');
        Assert::eq($normalizedProperty['type'], self::type());
        Assert::keyExists($normalizedProperty, 'string');
        Assert::stringNotEmpty($normalizedProperty['string']);
        Assert::maxLength($normalizedProperty['string'], self::LENGTH_LIMIT);

        return self::fromString($normalizedProperty['string']);
    }

    /**
     * @inheritDoc
     */
    public function normalize(): array
    {
        return [
            'type' => $this->type(),
            'string' => $this->value,
        ];
    }

    public static function fromString(string $value): self
    {
        Assert::stringNotEmpty($value);
        Assert::maxLength($value, self::LENGTH_LIMIT);

        return new self($value);
    }

    public function asString(): string
    {
        return $this->value;
    }
}
