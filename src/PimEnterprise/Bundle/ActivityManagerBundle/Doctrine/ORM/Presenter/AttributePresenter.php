<?php

/*
 * This file is part of the Akeneo PIM Enterprise Edition.
 *
 * (c) 2016 Akeneo SAS (http://www.akeneo.com)
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace PimEnterprise\Bundle\ActivityManagerBundle\Doctrine\ORM\Presenter;

use PimEnterprise\Component\ActivityManager\Presenter\PresenterInterface;

/**
 * Presents the values coming from mysql into a comparable data structure.
 *
 * @author Olivier Soulet <olivier.soulet@akeneo.com>
 */
class AttributePresenter implements PresenterInterface
{
    /**
     * Converts the following values:
     *
     * $mandatoryAttributes = [
     *      [
     *          'attribute_code' => 'sku',
     *          'attribute_group_code' => 'marketing',
     *      ],
     *      [
     *          'attribute_code' => 'name',
     *          'attribute_group_code' => 'marketing',
     *      ],
     * ];
     *
     * Into:
     *
     * $mandatoryAttributes = [
     *      'marketing' => [
     *          'sku',
     *          'name',
     *      ],
     * ];
     *
     * {@inheritdoc}
     */
    public function present(array $values, array $options = [])
    {
        $attribute_group_codes = array_unique(array_filter(array_column($values, 'attribute_group_code')));
        $result = [];
        foreach ($values as $productValue) {
            foreach ($attribute_group_codes as $attribute_group_code) {
                if ($productValue['attribute_group_code'] === $attribute_group_code) {
                    $result[$attribute_group_code][] = $productValue['attribute_code'];
                }
            }
        }

        return $result;
    }
}
