<?php

declare(strict_types=1);

namespace Akeneo\Pim\Automation\IdentifierGenerator\Domain\Model\Property;

use Akeneo\Pim\Automation\IdentifierGenerator\Domain\Model\Condition\ConditionInterface;
use Akeneo\Pim\Automation\IdentifierGenerator\Domain\Model\Condition\SimpleSelect;
use Webmozart\Assert\Assert;

/**
 * Simple Select Property that can be added to an Identifier Generator's structure
 * @copyright 2023 Akeneo SAS (https://www.akeneo.com)
 * @license   https://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 *
 * @phpstan-import-type ProcessNormalized from Process
 * @phpstan-type SimpleSelectPropertyNormalized array{
 *  type: 'simple_select',
 *  attributeCode: string,
 *  process: ProcessNormalized,
 *  scope?: string|null,
 *  locale?: string|null
 * }
 */
final class SimpleSelectProperty implements PropertyInterface
{
    private const TYPE = 'simple_select';

    private function __construct(
        private readonly string $attributeCode,
        private readonly Process $process,
        private readonly ?string $scope = null,
        private readonly ?string $locale = null
    ) {
    }

    public static function type(): string
    {
        return self::TYPE;
    }

    public function process(): Process
    {
        return $this->process;
    }

    /**
     * @return SimpleSelectPropertyNormalized
     */
    public function normalize(): array
    {
        $simpleSelectProperty = [
            'type' => self::TYPE,
            'attributeCode' => $this->attributeCode,
            'process' => $this->process->normalize(),
        ];

        if (null !== $this->scope) {
            $simpleSelectProperty['scope'] = $this->scope;
        }
        if (null !== $this->locale) {
            $simpleSelectProperty['locale'] = $this->locale;
        }

        return $simpleSelectProperty;
    }

    /**
     * @param array<string, mixed> $normalizedProperty
     */
    public static function fromNormalized(array $normalizedProperty): PropertyInterface
    {
        Assert::keyExists($normalizedProperty, 'type');
        Assert::same($normalizedProperty['type'], self::type());

        Assert::keyExists($normalizedProperty, 'attributeCode');
        Assert::string($normalizedProperty['attributeCode']);

        Assert::keyExists($normalizedProperty, 'process');
        Assert::isArray($normalizedProperty['process']);

        Assert::nullOrString($normalizedProperty['scope'] ?? null);
        Assert::nullOrString($normalizedProperty['locale'] ?? null);

        return new self(
            $normalizedProperty['attributeCode'],
            Process::fromNormalized($normalizedProperty['process']),
            $normalizedProperty['scope'] ?? null,
            $normalizedProperty['locale'] ?? null
        );
    }

    public function getImplicitCondition(): ?ConditionInterface
    {
        return SimpleSelect::fromNormalized([
            'type' => SimpleSelect::type(),
            'attributeCode' => $this->attributeCode,
            'operator' => 'NOT EMPTY',
            'scope' => $this->scope,
            'locale' => $this->locale,
        ]);
    }

    public function attributeCode(): string
    {
        return $this->attributeCode;
    }
}
