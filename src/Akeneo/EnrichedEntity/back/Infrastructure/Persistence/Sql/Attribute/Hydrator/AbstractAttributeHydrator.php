<?php

declare(strict_types=1);

namespace Akeneo\EnrichedEntity\Infrastructure\Persistence\Sql\Attribute\Hydrator;

use Akeneo\EnrichedEntity\Infrastructure\Persistence\Sql\HydratorInterface;

/**
 * @author    Samir Boulil <samir.boulil@akeneo.com>
 * @copyright 2018 Akeneo SAS (http://www.akeneo.com)
 */
abstract class AbstractAttributeHydrator implements HydratorInterface
{
    protected function hydrateCommonProperties(array $result): array
    {
        $result['labels'] = json_decode($result['labels'], true);
        $result['attribute_order'] = (int) $result['attribute_order'];
        $result['is_required'] = (bool) $result['is_required'];
        $result['value_per_channel'] = (bool) $result['value_per_channel'];
        $result['value_per_locale'] = (bool) $result['value_per_locale'];
        $result['additional_properties'] = json_decode($result['additional_properties'], true);

        return $result;
    }
}
