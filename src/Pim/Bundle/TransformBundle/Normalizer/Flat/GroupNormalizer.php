<?php

namespace Pim\Bundle\TransformBundle\Normalizer\Flat;

use Pim\Bundle\CatalogBundle\Model\GroupInterface;
use Pim\Bundle\TransformBundle\Normalizer\Structured;

/**
 * A normalizer to transform a group entity into a flat array
 *
 * @author    Nicolas Dupont <nicolas@akeneo.com>
 * @copyright 2013 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class GroupNormalizer extends Structured\GroupNormalizer
{
    /**
     * @var array $supportedFormats
     */
    protected $supportedFormats = array('csv');

    /**
     * {@inheritdoc}
     */
    protected function normalizeAxisAttributes(GroupInterface $group)
    {
        $attributes = parent::normalizeAxisAttributes($group);

        return implode(',', $attributes);
    }

    /**
     * {@inheritdoc}
     */
    protected function normalizeVariantGroupValues(GroupInterface $group)
    {
        $valuesData = [];
        if ($group->getType()->isVariant() && null !== $group->getProductTemplate()) {
            $template = $group->getProductTemplate();
            $valuesData = $template->getValuesData();
            $values = $this->serializer->denormalize($valuesData, 'ProductValue[]', 'json');
            $valuesData = $this->serializer->normalize($values, 'csv', ['entity' => 'product']);
        }

        return $valuesData;
    }
}
