<?php

namespace Pim\Bundle\TransformBundle\Normalizer\Flat;

use Pim\Bundle\CatalogBundle\Model\AttributeGroupInterface;
use Pim\Bundle\TransformBundle\Normalizer\Structured;

/**
 * Flat attribute group normalizer
 *
 * @author    Romain Monceau <romain@akeneo.com>
 * @copyright 2013 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class AttributeGroupNormalizer extends Structured\AttributeGroupNormalizer
{
    /**
     * @var array
     */
    protected $supportedFormats = array('csv');

    /**
     * {@inheritdoc}
     */
    protected function normalizeAttributes(AttributeGroupInterface $group)
    {
        $attributes = parent::normalizeAttributes($group);

        return implode(',', $attributes);
    }
}
