<?php

declare(strict_types=1);

namespace Akeneo\Pim\Automation\IdentifierGenerator\Domain\Model\Property;

use Akeneo\Pim\Automation\IdentifierGenerator\Domain\Model\Condition\ConditionInterface;
use Akeneo\Pim\Automation\IdentifierGenerator\Domain\Model\Condition\ReferenceEntity;
use Webmozart\Assert\Assert;

/**
 * Reference Entity Property that can be added to an Identifier Generator's structure
 * @copyright 2023 Akeneo SAS (https://www.akeneo.com)
 * @license   https://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 *
 * @phpstan-import-type ProcessNormalized from Process
 * @phpstan-type ReferenceEntityPropertyNormalized array{
 *  type: 'reference_entity',
 *  attributeCode: string,
 *  process: ProcessNormalized,
 *  scope?: string|null,
 *  locale?: string|null
 * }
 */
final class ReferenceEntityProperty implements PropertyInterface
{
    private const TYPE = 'reference_entity';

    private function __construct(
        private readonly string $attributeCode,
        private readonly Process $process,
        private readonly ?string $scope = null,
        private readonly ?string $locale = null
    ) {
        Assert::stringNotEmpty($this->attributeCode);
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
     * @return ReferenceEntityPropertyNormalized
     */
    public function normalize(): array
    {
        $referenceEntityProperty = [
            'type' => self::TYPE,
            'attributeCode' => $this->attributeCode,
            'process' => $this->process->normalize(),
        ];

        if (null !== $this->scope) {
            $referenceEntityProperty['scope'] = $this->scope;
        }
        if (null !== $this->locale) {
            $referenceEntityProperty['locale'] = $this->locale;
        }

        return $referenceEntityProperty;
    }

    public function getImplicitCondition(): ?ConditionInterface
    {
        return ReferenceEntity::fromNormalized([
            'type' => ReferenceEntity::type(),
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

    /**
     * @param array<string, mixed> $normalizedProperty
     */
    public static function fromNormalized(array $normalizedProperty): PropertyInterface
    {
        Assert::keyExists($normalizedProperty, 'type');
        Assert::same($normalizedProperty['type'], self::type());

        Assert::keyExists($normalizedProperty, 'attributeCode');
        Assert::stringNotEmpty($normalizedProperty['attributeCode']);

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
}
