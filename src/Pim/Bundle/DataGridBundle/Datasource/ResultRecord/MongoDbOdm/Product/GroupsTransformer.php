<?php

namespace Pim\Bundle\DataGridBundle\Datasource\ResultRecord\MongoDbOdm\Product;

use Pim\Bundle\CatalogBundle\ProductQueryUtility;

/**
 * Transform sub-part or product
 *
 * @author    Nicolas Dupont <nicolas@akeneo.com>
 * @copyright 2014 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class GroupsTransformer
{
    /**
     * @param array  $result
     * @param string $locale
     * @param string $currentGroupId
     *
     * @return array
     */
    public function transform(array $result, $locale, $currentGroupId)
    {
        $normalizedData = $result[ProductQueryUtility::NORMALIZED_FIELD];

        if ($currentGroupId) {
            if (isset($result['groupIds']) && in_array($currentGroupId, $result['groupIds'])) {
                $result['in_group'] = $result['is_checked'] = true;
            } else {
                $result['in_group'] = $result['is_checked'] = false;
            }
        }

        if (isset($normalizedData['groups'])) {
            $groups = [];
            foreach ($normalizedData['groups'] as $group) {
                $code = $group['code'];
                $label = isset($group['label'][$locale]) ? $group['label'][$locale] : null;
                $groups[$code] = ['code' => $code, 'label' => $label];
            }
            $result['groups'] = $groups;
        }

        return $result;
    }
}
