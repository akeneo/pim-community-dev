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

namespace Akeneo\Pim\Enrichment\ReferenceEntity\Component\Provider;

use Akeneo\Pim\Enrichment\ReferenceEntity\Component\AttributeType\ReferenceEntityCollectionType;
use Akeneo\Pim\Structure\Component\Model\AttributeInterface;
use Akeneo\Platform\Bundle\UIBundle\Provider\EmptyValue\EmptyValueProviderInterface;
use Akeneo\Platform\Bundle\UIBundle\Provider\Field\FieldProviderInterface;

/**
 * Field provider for reference entity collection
 *
 * @author Julien Sanchez <julien@akeneo.com>
 */
class ReferenceEntityCollectionProvider implements FieldProviderInterface, EmptyValueProviderInterface
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
        return 'akeneo-reference-entity-collection-field';
    }

    /**
     * {@inheritdoc}
     */
    public function supports($element): bool
    {
        return $element instanceof AttributeInterface &&
            ReferenceEntityCollectionType::REFERENCE_ENTITY_COLLECTION === $element->getType();
    }
}
