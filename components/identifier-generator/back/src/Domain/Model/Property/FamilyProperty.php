<?php

declare(strict_types=1);

namespace Akeneo\Pim\Automation\IdentifierGenerator\Domain\Model\Property;

use Akeneo\Pim\Automation\IdentifierGenerator\Domain\Model\Condition\ConditionInterface;
use Akeneo\Pim\Automation\IdentifierGenerator\Domain\Model\Condition\Family;
use Webmozart\Assert\Assert;

/**
 * @copyright 2023 Akeneo SAS (https://www.akeneo.com)
 * @license   https://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 *
 * @phpstan-import-type ProcessNormalized from Process
 * @phpstan-type FamilyPropertyNormalized array{type: 'family', process: ProcessNormalized}
 */
final class FamilyProperty implements PropertyInterface
{
    public const TYPE = 'family';

    private function __construct(
        private readonly Process $process,
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
     * @return FamilyPropertyNormalized
     */
    public function normalize(): array
    {
        return [
            'type' => self::TYPE,
            'process' => $this->process->normalize(),
        ];
    }

    public static function fromNormalized(array $normalizedProperty): PropertyInterface
    {
        Assert::keyExists($normalizedProperty, 'type');
        Assert::same($normalizedProperty['type'], self::type());
        Assert::keyExists($normalizedProperty, 'process');
        Assert::isArray($normalizedProperty['process']);

        return new self(Process::fromNormalized($normalizedProperty['process']));
    }

    public function getImplicitCondition(): ?ConditionInterface
    {
        return Family::fromNormalized([
            'type' => Family::type(),
            'operator' => 'NOT EMPTY',
        ]);
    }
}
