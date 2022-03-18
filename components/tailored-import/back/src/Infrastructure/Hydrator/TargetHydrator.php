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
use Akeneo\Platform\TailoredImport\Domain\Model\TargetAttribute;
use Akeneo\Platform\TailoredImport\Domain\Model\TargetInterface;
use Akeneo\Platform\TailoredImport\Domain\Model\TargetProperty;

class TargetHydrator
{
    public function __construct(
        private SourceParameterHydrator $sourceParameterHydrator
    ) {
    }

    public function hydrate(array $normalizedTarget, array $indexedAttributes): TargetInterface
    {
        return match ($normalizedTarget['type']) {
            TargetAttribute::TYPE => $this->hydrateAttribute($normalizedTarget, $indexedAttributes),
            TargetProperty::TYPE => $this->hydrateProperty($normalizedTarget),
            default => throw new \InvalidArgumentException(sprintf('Unsupported "%s" target type', $normalizedTarget['type'])),
        };
    }

    private function hydrateAttribute(array $normalizedTarget, array $indexedAttributes): TargetAttribute
    {
        $attribute = $indexedAttributes[$normalizedTarget['code']] ?? null;
        if (!$attribute instanceof Attribute) {
            throw new \InvalidArgumentException(sprintf('Attribute "%s" does not exist', $normalizedTarget['code']));
        }

        $sourceParameter = $this->sourceParameterHydrator->hydrate($normalizedTarget['source_parameter'], $attribute->type());

        return TargetAttribute::create(
            $normalizedTarget['code'],
            $attribute->type(),
            $normalizedTarget['channel'],
            $normalizedTarget['locale'],
            $normalizedTarget['action_if_not_empty'],
            $normalizedTarget['action_if_empty'],
            $sourceParameter,
        );
    }

    private function hydrateProperty(array $normalizedTarget): TargetProperty
    {
        return TargetProperty::create(
            $normalizedTarget['code'],
            $normalizedTarget['action_if_not_empty'],
            $normalizedTarget['action_if_empty'],
        );
    }
}
