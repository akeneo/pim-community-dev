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

namespace Akeneo\Pim\Enrichment\ReferenceEntity\Component\AttributeType;

use Akeneo\Pim\Structure\Component\AttributeType\AbstractAttributeType;

/**
 * Reference entity type
 *
 * @author    Adrien Pétremann <adrien.petremann@akeneo.com>
 * @copyright 2018 Akeneo SAS (https://www.akeneo.com)
 */
class ReferenceEntityType extends AbstractAttributeType
{
    const REFERENCE_ENTITY = 'akeneo_reference_entity';

    /**
     * {@inheritdoc}
     */
    public function getName(): string
    {
        return static::REFERENCE_ENTITY;
    }
}
