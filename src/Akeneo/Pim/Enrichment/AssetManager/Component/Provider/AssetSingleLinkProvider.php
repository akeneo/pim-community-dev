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

namespace Akeneo\Pim\Enrichment\AssetManager\Component\Provider;

use Akeneo\Pim\Enrichment\AssetManager\Component\AttributeType\AssetSingleLinkType;
use Akeneo\Pim\Structure\Component\Model\AttributeInterface;
use Akeneo\Platform\Bundle\UIBundle\Provider\EmptyValue\EmptyValueProviderInterface;
use Akeneo\Platform\Bundle\UIBundle\Provider\Field\FieldProviderInterface;

/**
 * Field provider for asset family
 *
 * @author    Adrien Pétremann <adrien.petremann@akeneo.com>
 * @copyright 2018 Akeneo SAS (https://www.akeneo.com)
 */
class AssetSingleLinkProvider implements FieldProviderInterface, EmptyValueProviderInterface
{
    /**
     * {@inheritdoc}
     */
    public function getEmptyValue($attribute)
    {
        return null;
    }

    /**
     * {@inheritdoc}
     */
    public function getField($attribute): string
    {
        return 'akeneo-asset-single-link-field';
    }

    /**
     * {@inheritdoc}
     */
    public function supports($element): bool
    {
        return $element instanceof AttributeInterface && AssetSingleLinkType::ASSET_SINGLE_LINK === $element->getType();
    }
}
