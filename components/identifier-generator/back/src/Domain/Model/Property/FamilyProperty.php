<?php

declare(strict_types=1);

namespace Akeneo\Pim\Automation\IdentifierGenerator\Domain\Model\Property;

use Webmozart\Assert\Assert;

/**
 * @copyright 2023 Akeneo SAS (https://www.akeneo.com)
 * @license   https://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 *
 * @phpstan-import-type ProcessNormalized from Process
 * @phpstan-type FamilyPropertyNormalized array{type: string, process: ProcessNormalized}
 */
final class FamilyProperty implements PropertyInterface
{
    private function __construct(
        private readonly Process $process,
    ) {
    }

    public static function type(): string
    {
        return 'family';
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
            'type' => $this->type(),
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
}
