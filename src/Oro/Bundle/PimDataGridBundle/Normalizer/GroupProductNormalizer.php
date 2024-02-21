<?php

namespace Oro\Bundle\PimDataGridBundle\Normalizer;

use Akeneo\Pim\Enrichment\Component\Product\Model\GroupInterface;

/**
 * Normalize Products for variant group grid
 *
 * @author    Philippe Mossière <philippe.mossiere@akeneo.com>
 * @copyright 2017 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php Open Software License (OSL 3.0)
 */
class GroupProductNormalizer extends ProductNormalizer
{
    /**
     * {@inheritdoc}
     */
    public function normalize($product, $format = null, array $context = array())
    {
        $data = parent::normalize($product, $format, $context);

        $groupIds = array_map(function (GroupInterface $group) {
            return $group->getId();
        }, $product->getGroups()->toArray());

        $data['in_group'] = in_array($context['current_group_id'], $groupIds);
        $data['is_checked'] = in_array($context['current_group_id'], $groupIds);

        return $data;
    }
}
