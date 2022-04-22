<?php

declare(strict_types=1);

/*
 * This file is part of the Akeneo PIM Enterprise Edition.
 *
 * (c) 2022 Akeneo SAS (https://www.akeneo.com)
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Akeneo\Platform\TailoredImport\Infrastructure\Hydrator;

use Akeneo\Pim\Structure\Component\Query\PublicApi\AttributeType\Attribute;
use Akeneo\Platform\TailoredImport\Domain\Model\Target\AttributeTarget;
use Akeneo\Platform\TailoredImport\Domain\Model\Target\PropertyTarget;
use Akeneo\Platform\TailoredImport\Domain\Model\Target\TargetInterface;

class TargetHydrator
{
    public function __construct(
        private SourceConfigurationHydrator $sourceConfigurationHydrator,
    ) {
    }

    public function hydrate(array $normalizedTarget, array $indexedAttributes): TargetInterface
    {
        return match ($normalizedTarget['type']) {
            AttributeTarget::TYPE => $this->hydrateAttribute($normalizedTarget, $indexedAttributes),
            PropertyTarget::TYPE => $this->hydrateProperty($normalizedTarget),
            default => throw new \InvalidArgumentException(sprintf('Unsupported "%s" target type', $normalizedTarget['type'])),
        };
    }

    private function hydrateAttribute(array $normalizedTarget, array $indexedAttributes): AttributeTarget
    {
        $attribute = $indexedAttributes[$normalizedTarget['code']] ?? null;
        if (!$attribute instanceof Attribute) {
            throw new \InvalidArgumentException(sprintf('Attribute "%s" does not exist', $normalizedTarget['code']));
        }

        $sourceConfiguration = $this->sourceConfigurationHydrator->hydrate(
            $normalizedTarget['source_configuration'] ?? null,
            $attribute->type(),
        );

        return AttributeTarget::create(
            $normalizedTarget['code'],
            $attribute->type(),
            $normalizedTarget['channel'],
            $normalizedTarget['locale'],
            $normalizedTarget['action_if_not_empty'],
            $normalizedTarget['action_if_empty'],
            $sourceConfiguration,
        );
    }

    private function hydrateProperty(array $normalizedTarget): PropertyTarget
    {
        return PropertyTarget::create(
            $normalizedTarget['code'],
            $normalizedTarget['action_if_not_empty'],
            $normalizedTarget['action_if_empty'],
        );
    }
}
