<?php
declare(strict_types=1);

/*
 * This file is part of the Akeneo PIM Enterprise Edition.
 *
 * (c) 2018 Akeneo SAS (http://www.akeneo.com)
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Akeneo\Pim\EnrichedEntity\Component\Provider;

use Akeneo\Pim\EnrichedEntity\Component\AttributeType\EnrichedEntityCollectionType;
use Akeneo\Pim\Structure\Component\Model\AttributeInterface;
use Akeneo\Platform\Bundle\UIBundle\Provider\EmptyValue\EmptyValueProviderInterface;
use Akeneo\Platform\Bundle\UIBundle\Provider\Field\FieldProviderInterface;

/**
 * Field provider for enriched entity
 *
 * @author Julien Sanchez <julien@akeneo.com>
 */
class EnrichedEntityProvider implements FieldProviderInterface, EmptyValueProviderInterface
{
    /**
     * {@inheritdoc}
     */
    public function getEmptyValue($attribute): array
    {
        return [];
    }

    /**
     * {@inheritdoc}
     */
    public function getField($attribute): string
    {
        return 'akeneo-enriched-entity-field';
    }

    /**
     * {@inheritdoc}
     */
    public function supports($element): bool
    {
        return $element instanceof AttributeInterface &&
            EnrichedEntityCollectionType::ENRICHED_ENTITY_COLLECTION === $element->getType();
    }
}
