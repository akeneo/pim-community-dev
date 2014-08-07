<?php

namespace PimEnterprise\Bundle\WebServiceBundle\Normalizer\Filter;

use Doctrine\Common\Collections\Collection;
use Pim\Bundle\TransformBundle\Normalizer\Filter\NormalizerFilterInterface;
use Pim\Bundle\CatalogBundle\Model\ProductValueInterface;

/**
 * Filter the granted product value objects.
 *
 * @author    Nicolas Dupont <nicolas@akeneo.com>
 * @copyright 2014 Akeneo SAS (http://www.akeneo.com)
 */
class GrantedProductValueNormalizerFilter implements NormalizerFilterInterface
{
    /**
     * {@inheritdoc}
     */
    public function filter(Collection $objects, array $context = [])
    {
        return $objects;
    }
}
