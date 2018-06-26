<?php
declare(strict_types=1);

/*
 * This file is part of the Akeneo PIM Enterprise Edition.
 *
 * (c) 2015 Akeneo SAS (http://www.akeneo.com)
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Akeneo\Pim\EnrichedEntity\Component\AttributeType;

use Akeneo\Pim\Structure\Component\AttributeType\AbstractAttributeType;

/**
 * Enriched entity collection type
 *
 * @author Julien Sanchez <julien@akeneo.com>
 */
class EnrichedEntityCollectionType extends AbstractAttributeType
{
    const ENRICHED_ENTITY_COLLECTION = 'akeneo_enriched_entity_collection';

    /**
     * {@inheritdoc}
     */
    public function getName(): string
    {
        return static::ENRICHED_ENTITY_COLLECTION;
    }
}
